<?php

namespace App\Http\Controllers;

use App\Enums\VoucherType;
use App\Http\Requests\StoreVoucherRequest;
use App\Models\User;
use App\Models\UserVoucherRedemption;
use App\Models\Voucher;
use App\Models\VoucherMemberRules;
use App\Models\VoucherValidity;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
                        $q->whereNotNull('redeemed_at');
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
                        $start_date = Carbon::parse($range[0])->addDay()->startOfDay();
                        $end_date = Carbon::parse($range[1])->addDay()->endOfDay();

                        $inner->whereBetween('start_date', [$start_date, $end_date])
                            ->orWhereBetween('end_date', [$start_date, $end_date]);
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
                'voucher_highlight' => $request->voucher_highlight,
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

                // Determine status
                if ($start_date->isFuture()) {
                    $voucher->status = VoucherType::SCHEDULE;
                    $voucher->save();
                }
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

    public function claim_activity()
    {
        $stats = UserVoucherRedemption::selectRaw('COUNT(*) as total, MAX(updated_at) as last_updated_at')->first();

        return Inertia::render('Voucher/ClaimActivity/ClaimActivity', [
            'lastUpdatedDate' => $stats->last_updated_at,
            'redemptionsCount'   => $stats->total,
        ]);
    }

    public function getClaimActivityData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = UserVoucherRedemption::with([
                'voucher',
                'voucher.media',
                'voucher.validities',
                'voucher.member_rule',
                'user:id,full_name,phone_number'
            ]);

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('user', function ($q) use ($keyword) {
                        $q->where('full_name', 'like', '%' . $keyword . '%')
                            ->orWhere('phone_number', 'like', '%' . $keyword . '%')
                            ->orWhere('email', 'like', '%' . $keyword . '%');
                    })->orWhereHas('voucher', function ($q) use ($keyword) {
                        $q->where('voucher_name', 'like', '%' . $keyword . '%')
                            ->orWhere('voucher_code', 'like', '%' . $keyword . '%');
                    });
                });
            }

            if ($data['filters']['date']['value']) {
                $range = $data['filters']['date']['value'];
                $start_date = Carbon::parse($range[0])->addDay()->startOfDay();
                $end_date = Carbon::parse($range[1])->addDay()->endOfDay();

                $query->whereBetween('redeemed_at', [$start_date, $end_date]);
            }

            if ($data['filters']['claim_methods']['value']) {
                $query->whereIn('claimed_method', $data['filters']['claim_methods']['value']);
            }

            if ($data['filters']['status']['value']) {
                $query->whereIn('status', $data['filters']['status']['value']);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('redeemed_at');
            }

            $redemptions = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $redemptions,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function usage_activity()
    {
        $stats = UserVoucherRedemption::where('status', VoucherType::USED)
            ->selectRaw('COUNT(*) as total, MAX(updated_at) as last_updated_at')
            ->first();

        return Inertia::render('Voucher/UsageActivity/UsageActivity', [
            'lastUpdatedDate' => $stats->last_updated_at,
            'usageCount'   => $stats->total,
        ]);
    }

    public function voucher_usage()
    {
        return Inertia::render('Voucher/UsageActivity/UseVoucherForm');
    }

    public function getUsageActivityData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = UserVoucherRedemption::with([
                'voucher',
                'user:id,full_name,phone_number'
            ])
                ->where('status', VoucherType::USED);

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('user', function ($q) use ($keyword) {
                        $q->where('full_name', 'like', '%' . $keyword . '%')
                            ->orWhere('phone_number', 'like', '%' . $keyword . '%')
                            ->orWhere('email', 'like', '%' . $keyword . '%');
                    })->orWhereHas('voucher', function ($q) use ($keyword) {
                        $q->where('voucher_name', 'like', '%' . $keyword . '%')
                            ->orWhere('voucher_code', 'like', '%' . $keyword . '%');
                    });
                });
            }

            if ($data['filters']['date']['value']) {
                $range = $data['filters']['date']['value'];
                $start_date = Carbon::parse($range[0])->addDay()->startOfDay();
                $end_date = Carbon::parse($range[1])->addDay()->endOfDay();

                $query->whereBetween('used_at', [$start_date, $end_date]);
            }

            if ($data['filters']['claim_methods']['value']) {
                $query->whereIn('claimed_method', $data['filters']['claim_methods']['value']);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('used_at');
            }

            $redemptions = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $redemptions,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function useVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required'],
            'voucher_id' => ['required'],
            'remarks' => ['required'],
        ])->setAttributeNames([
            'user_id' => trans('public.member'),
            'voucher_id' => trans('public.voucher'),
            'remarks' => trans('public.remark'),
        ]);
        $validator->validate();

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $user_voucher = UserVoucherRedemption::with([
            'voucher',
            'voucher.media',
            'voucher.validities',
            'user:id,full_name,phone_number'
        ])->find($request->voucher_id);

        if (!$user_voucher) {
            return response()->json([
                'success' => false,
                'title' => trans('public.no_voucher_found'),
                'type' => 'error'
            ], 400);
        }

        if ($user_voucher->status == VoucherType::USED) {
            return response()->json([
                'success' => false,
                'title' => trans('public.voucher_used'),
                'type' => 'error'
            ], 400);
        }

        if ($user_voucher->status == VoucherType::EXPIRED) {
            return response()->json([
                'success' => false,
                'title' => trans('public.voucher_expired'),
                'type' => 'error'
            ], 400);
        }

        if ($user_voucher->expired_at && $user_voucher->expired_at->isPast()) {
            return response()->json([
                'success' => false,
                'title' => trans('public.voucher_expired'),
                'type' => 'warning'
            ], 400);
        }

        $user_voucher->update([
            'used_at' => now(),
            'status' => VoucherType::USED,
            'remarks' => $request->remarks,
        ]);

        return response()->json([
            'title' => trans('public.voucher_created'),
            'message' => trans('public.voucher_created_message'),
            'type' => 'success',
            'data' => $user_voucher,
        ]);
    }
}
