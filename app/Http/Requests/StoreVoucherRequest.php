<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoucherRequest extends FormRequest
{
    public function rules(): array
    {
        $step = $this->route('step') ?? 'all';

        $rules = [];

        if ($step === 'general' || $step === 'all') {
            $rules = array_merge($rules, [
                'voucher_type'     => ['required', 'string'],
                'voucher_name'     => ['required', 'string'],
                'description'      => ['required', 'string'],
                'campaign_period'  => ['required'],
                'eligible_service' => ['required', 'string'],
                'discount_rate'    => ['required', 'numeric'],
                'discount_type'    => ['required'],
                'discount_limit'   => ['required'],
            ]);

            if ($this->boolean('campaign_period')) {
                $rules['campaign_period_range'] = ['required'];
            }

            if ($this->boolean('discount_limit')) {
                $rules['capped_amount'] = ['required', 'numeric'];
            }
        }

        if ($step === 'redemption' || $step === 'all') {
            $rules = array_merge($rules, [
                'claim_method'            => ['required', 'string'],
                'claim_limit'             => ['required'],
                'validity_count'          => ['required'],
                'validity_count_type'     => ['required'],
                'requirement_type'        => ['required'],
                'valid_type'              => ['required'],
                'can_stack'               => ['required'],
            ]);

            switch ($this->input('claim_method')) {
                case 'point_to_claim':
                    $rules['redeem_point'] = ['required', 'numeric', 'min:1'];
                    break;

                case 'code_to_claim':
                    $rules['voucher_code'] = ['required', 'string'];
                    break;

                case 'add_for_member':
                    $rules['add_for_member.activation_rule'] = ['required'];

                    if ($this->input('add_for_member.event_type') === 'special_holiday') {
                        $rules['add_for_member.special_holiday_date'] = ['required', 'date'];
                    }

                    if ($this->input('add_for_member.activation_rule') === 'amount_paid') {
                        $rules['add_for_member.amount_paid'] = ['required', 'numeric', 'min:1'];
                    }
                    break;
            }

            if ($this->claim_limit == 'limited') {
                $rules['voucher_limit'] = ['required'];
                $rules['renew_voucher_limit'] = ['required'];
                $rules['claim_amount_per_member'] = ['required'];
                $rules['renew_claim_limit'] = ['required'];
            }

            if ($this->input('requirement_type') === 'min_spend') {
                $rules['min_spend_amount'] = ['required', 'numeric', 'min:1'];
            }

            switch ($this->input('valid_type')) {
                case 'specific_day':
                    $rules['valid_days'] = ['required'];
                    break;

                case 'specific_time':
                    $rules['valid_time.0'] = ['required'];
                    $rules['valid_time.1'] = ['required', 'after:valid_time.0'];
                    break;

                case 'specific_day_time':
                    $rules['valid_days']   = ['required'];
                    $rules['valid_time.0'] = ['required'];
                    $rules['valid_time.1'] = ['required', 'after:valid_time.0'];
                    break;
            }
        }

        if ($step === 'setting' || $step === 'all') {
            $rules = array_merge($rules, [
                'voucher_thumbnail' => ['required', 'image', 'max:8000'],
            ]);
        }

        return $rules;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return [
            'voucher_type'   => trans('public.voucher_type'),
            'voucher_name'  => trans('public.voucher_name'),
            'description'     => trans('public.description'),
            'campaign_period' => trans('public.campaign_period'),
            'campaign_period_range' => trans('public.campaign_period'),
            'eligible_service' => trans('public.eligible_service'),
            'discount_rate'   => trans('public.discount_rate'),
            'discount_type'   => trans('public.discount_type'),
            'discount_limit'   => trans('public.discount_limit'),
            'capped_amount'   => trans('public.capped_amount'),
            'claim_method'   => trans('public.method'),
            'redeem_point'   => trans('public.point_to_redeem'),
            'voucher_code'   => trans('public.voucher_code'),
            'activation_rule'   => trans('public.activation_rule'),
            'event_type'   => trans('public.type_of_event'),
            'special_holiday_date'   => trans('public.date'),
            'amount_paid' => trans('public.amount_paid'),
            'claim_limit'   => trans('public.claim_limit'),
            'voucher_limit'   => trans('public.limit_per_voucher'),
            'renew_voucher_limit'   => trans('public.renew_limit'),
            'claim_amount_per_member'   => trans('public.limit_per_member'),
            'renew_claim_limit'   => trans('public.renew_limit'),
            'validity_count'   => trans('public.validity'),
            'requirement_type'   => trans('public.requirement_type'),
            'min_spend_amount'   => trans('public.min_spend'),
            'valid_type' => trans('public.valid_day_time'),
            'can_stack' => trans('public.stacking_rule'),
            'valid_days' => trans('public.valid_day'),
            'valid_time.*' => trans('public.valid_time'),
            'voucher_thumbnail' => trans('public.image'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'discount_limit' => filter_var($this->discount_limit, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'campaign_period' => filter_var($this->campaign_period, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'can_stack' => filter_var($this->can_stack, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        ]);
    }
}
