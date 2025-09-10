<?php

namespace App\Http\Controllers;

use App\Enums\VoucherType;
use App\Http\Requests\StoreVoucherRequest;
use App\Models\Voucher;
use App\Models\VoucherMemberRules;
use App\Models\VoucherValidity;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Str;
use Throwable;
use Carbon\Carbon;

class VoucherController extends Controller
{
    public function index()
    {
        $stats = Voucher::selectRaw('COUNT(*) as total, MAX(updated_at) as last_updated_at')->first();

        return Inertia::render('Voucher/Listing/VoucherListing', [
            'lastUpdatedDate' => $stats->last_updated_at,
            'vouchersCount'   => $stats->total,
        ]);
    }

    public function getVoucherListingData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = Voucher::with('validities')
                ->withCount([
                    'redemptions',
                    'redemptions as redeemed_count' => function ($q) {
                        $q->where('status', VoucherType::REDEEMED);
                    },
                    'redemptions as used_count' => function ($q) {
                        $q->where('status', VoucherType::USED);
                    },
                ]);

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->where('voucher_name', 'like', '%' . $keyword . '%')
                        ->orWhere('voucher_code', 'like', '%' . $keyword . '%');
                });
            }

            if ($data['filters']['campaign_period']['value']) {
                $range = $data['filters']['campaign_period']['value'];

                $query->whereHas('validities', function ($q) use ($range) {
                    $q->where(function ($inner) use ($range) {
                        $inner->whereBetween('start_date', [$range[0], $range[1]])
                            ->orWhereBetween('end_date', [$range[0], $range[1]]);
                    });
                });
            }

            if ($data['filters']['status']['value']) {
                $query->whereIn('status', $data['filters']['status']['value']);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('created_at');
            }

            $fetchedNotifications = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $fetchedNotifications,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function create()
    {
        return Inertia::render('Voucher/Create/CreateVoucher');
    }

    public function validate_step(StoreVoucherRequest $request)
    {
        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(StoreVoucherRequest $request)
    {
        DB::beginTransaction();

        try {
            // Create main voucher
            $voucher = Voucher::create([
                'voucher_type' => $request->voucher_type,
                'voucher_name' => $request->voucher_name,
                'description' => $request->description,
                'campaign_period' => $request->campaign_period,
                'eligible_service' => $request->eligible_service,
                'discount_rate' => $request->discount_rate,
                'discount_type' => $request->discount_type,
                'discount_limit' => $request->discount_limit,
                'claim_method' => $request->claim_method,
                'claim_limit' => $request->claim_limit,
                'validity_count' => $request->validity_count,
                'validity_count_type' => $request->validity_count_type,
                'requirement_type' => $request->requirement_type,
                'valid_type' => $request->valid_type,
                'can_stack' => $request->can_stack,
            ]);

            // Discount limit
            if ($request->discount_limit) {
                $voucher->update([
                    'capped_amount' => $request->capped_amount
                ]);
            }

            if ($request->claim_method != VoucherType::CODE_TO_CLAIM) {
                $voucher->update([
                    'voucher_code' => Str::upper(Str::random(6)),
                ]);
            }

            // Claim method
            switch ($request->input('claim_method')) {
                case VoucherType::POINT_TO_CLAIM:
                    $voucher->update([
                        'redeem_point' => $request->redeem_point
                    ]);
                    break;

                case VoucherType::CODE_TO_CLAIM:
                    $voucher->update([
                        'voucher_code' => $request->code_to_claim['voucher_code']
                    ]);
                    break;

                case VoucherType::ADD_FOR_MEMBER:
                    $member_rule = VoucherMemberRules::create([
                        'voucher_id' => $voucher->id,
                        'activation_rule' => $request->add_for_member['activation_rule'],
                    ]);

                    if ($member_rule->activation_rule == 'event_based') {
                        $member_rule->update([
                            'event_type' => $request->add_for_member['event_type'],
                        ]);
                    }

                    if ($member_rule->event_type == 'special_holiday') {
                        $member_rule->update([
                            'special_holiday_date' => Carbon::parse($request->add_for_member['special_holiday_date'])->addDay()->startOfDay(),
                        ]);
                    }

                    if ($member_rule->activation_rule == 'amount_paid') {
                        $member_rule->update([
                            'amount_paid' => $request->add_for_member['amount_paid'],
                        ]);
                    }

                    break;
            }

            // Claim limit
            if ($voucher->claim_limit == 'limited') {
                $voucher->update([
                    'voucher_limit' => $request->voucher_limit,
                    'renew_voucher_limit' => $request->renew_voucher_limit,
                    'claim_amount_per_member' => $request->claim_amount_per_member,
                    'renew_claim_limit' => $request->renew_claim_limit,
                ]);
            }

            // Precompute shared values
            if ($voucher->campaign_period) {
                $start_date = Carbon::parse($request->campaign_period_range[0])->addDay()->startOfDay();
                $end_date = Carbon::parse($request->campaign_period_range[1])->addDay()->endOfDay();
            } else {
                $start_date = null;
                $end_date = null;
            }


            $dateRange = [
                'start_date' => $voucher->campaign_period ? $start_date : null,
                'end_date'   => $voucher->campaign_period ? $end_date : null,
                'type'       => $voucher->valid_type,
            ];

            // Handle Validity rules
            switch ($voucher->valid_type) {
                case 'specific_day':
                    // User-selected days, full-day time
                    foreach ($request->valid_days as $day) {
                        VoucherValidity::create(array_merge($dateRange, [
                            'voucher_id' => $voucher->id,
                            'start_time' => '00:00',
                            'end_time'   => '23:59:59',
                            'weekday'    => $day,
                        ]));
                    }
                    break;

                case 'specific_time':
                    // Every day, user-selected time
                    foreach (range(0, 6) as $day) {
                        VoucherValidity::create(array_merge($dateRange, [
                            'voucher_id' => $voucher->id,
                            'start_time' => $request->valid_time[0] ?? null,
                            'end_time'   => $request->valid_time[1] ?? null,
                            'weekday'    => $day,
                        ]));
                    }
                    break;

                case 'specific_day_time':
                    // User-selected days and time range
                    foreach ($request->valid_days as $day) {
                        VoucherValidity::create(array_merge($dateRange, [
                            'voucher_id' => $voucher->id,
                            'start_time' => $request->valid_time[0] ?? null,
                            'end_time'   => $request->valid_time[1] ?? null,
                            'weekday'    => $day,
                        ]));
                    }
                    break;

                default:
                    // Default: all days, full-day time
                    foreach (range(0, 6) as $day) {
                        VoucherValidity::create(array_merge($dateRange, [
                            'voucher_id' => $voucher->id,
                            'start_time' => '00:00',
                            'end_time'   => '23:59:59',
                            'weekday'    => $day,
                        ]));
                    }
                    break;
            }

            if ($request->hasFile('voucher_thumbnail')) {
                $voucher->addMedia($request->voucher_thumbnail)->toMediaCollection('voucher_thumbnail');
            }

            DB::commit();

            return response()->json([
                'title' => trans('public.voucher_created'),
                'message' => trans('public.voucher_created_message'),
                'type' => 'success',
                'voucher' => $voucher,
            ]);

        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'title' => trans('public.error'),
                'message' => $e->getMessage(),
                'type' => 'error',
            ], 400);
        }
    }
}
