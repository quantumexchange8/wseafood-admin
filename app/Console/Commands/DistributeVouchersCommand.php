<?php

namespace App\Console\Commands;

use App\Enums\VoucherType;
use App\Models\User;
use App\Models\UserVoucherRedemption;
use App\Models\Voucher;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class DistributeVouchersCommand extends Command
{
    protected $signature = 'distribute:vouchers';

    protected $description = 'Distribute vouchers to users daily';

    public function handle(): void
    {
        $this->info('Distributing vouchers...');
        $today = now()->startOfDay();

        $vouchers = Voucher::with('member_rule') // eager load rules
        ->where('claim_method', VoucherType::ADD_FOR_MEMBER)
            ->whereHas('member_rule', fn($q) =>
            $q->where('activation_rule', VoucherType::EVENT_BASED)
            )
            ->get();

        foreach ($vouchers as $voucher) {
            $rule = $voucher->member_rule;

            // Build user query based on event type
            if ($rule->event_type === 'member_birthday') {
                $usersQuery = User::whereMonth('dob', $today->month)
                    ->whereDay('dob', $today->day);
            } elseif ($rule->event_type === 'special_holiday') {
                if (!$rule->special_holiday_date ||
                    !Carbon::parse($rule->special_holiday_date)->isSameDay($today)) {
                    continue; // skip voucher
                }
                $usersQuery = User::query(); // all users
            } else {
                continue;
            }

            // Chunk users to avoid memory spikes
            $usersQuery->chunkById(500, function ($users) use ($voucher) {
                foreach ($users as $user) {
                    DB::transaction(function () use ($voucher, $user) {
                        // Lock voucher row
                        $lockedVoucher = Voucher::where('id', $voucher->id)
                            ->lockForUpdate()
                            ->first();

                        // Double check still exists
                        if (!$lockedVoucher) {
                            return;
                        }

                        // === Voucher limits ===
                        if ($lockedVoucher->claim_limit === 'limited' && $lockedVoucher->voucher_limit > 0) {
                            $totalClaimed = $lockedVoucher->redemptions()->count();
                            if ($totalClaimed >= $lockedVoucher->voucher_limit) {
                                // Mark fully claimed and skip
                                $lockedVoucher->status = VoucherType::FULLY_CLAIMED;
                                $lockedVoucher->save();
                                return;
                            }
                        }

                        // === Member limit ===
                        if ($lockedVoucher->claim_amount_per_member > 0) {
                            $userClaimed = $lockedVoucher->redemptions()
                                ->where('user_id', $user->id)
                                ->count();
                            if ($userClaimed >= $lockedVoucher->claim_amount_per_member) {
                                return; // skip this user
                            }
                        }

                        // === Create redemption ===
                        $redemption = UserVoucherRedemption::create([
                            'user_id'        => $user->id,
                            'voucher_id'     => $lockedVoucher->id,
                            'claimed_method' => $lockedVoucher->claim_method,
                            'redeemed_at'    => now(),
                            'status'         => VoucherType::REDEEMED,
                        ]);

                        // === Calculate expiry ===
                        if ($lockedVoucher->validity_count > 0) {
                            $expiredAt = match ($lockedVoucher->validity_count_type) {
                                'day', 'days'     => now()->addDays($lockedVoucher->validity_count),
                                'month', 'months' => now()->addMonths($lockedVoucher->validity_count),
                                'year', 'years'   => now()->addYears($lockedVoucher->validity_count),
                                default           => now(),
                            };

                            $redemption->update([
                                'expired_at' => $expiredAt->endOfDay(),
                            ]);
                        }

                        // === Check total again after redemption ===
                        if ($lockedVoucher->claim_limit === 'limited' && $lockedVoucher->voucher_limit > 0) {
                            $totalClaimed = $lockedVoucher->redemptions()->count();
                            if ($totalClaimed >= $lockedVoucher->voucher_limit) {
                                $lockedVoucher->status = VoucherType::FULLY_CLAIMED;
                                $lockedVoucher->save();
                            }
                        }
                    }); // end transaction
                }
            });
        }

        $this->info('Voucher distribution complete.');
    }
}
