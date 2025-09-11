<?php

namespace App\Console\Commands;

use App\Models\Voucher;
use App\Models\VoucherValidity;
use Illuminate\Console\Command;

class UpdateVoucherStatusCommand extends Command
{
    protected $signature = 'update:voucher-status';

    protected $description = 'Update voucher statuses daily';

    public function handle(): void
    {
        $now = now();

        // schedule → active
        $voucherIds = VoucherValidity::where('start_date', '<=', $now)
            ->pluck('voucher_id');
        Voucher::whereIn('id', $voucherIds)
            ->where('campaign_period', true)
            ->where('status', 'schedule')
            ->update(['status' => 'active']);

        // active → ended
        $voucherIds = VoucherValidity::whereDate('end_date', '<', $now)
            ->pluck('voucher_id');
        Voucher::whereIn('id', $voucherIds)
            ->where('campaign_period', true)
            ->where('status', 'active')
            ->update(['status' => 'ended']);

        $this->info('Voucher statuses updated.');
    }
}
