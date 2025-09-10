<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            // General
            $table->string('voucher_type', 100)->default('value_discount');
            $table->string('voucher_name');
            $table->text('description');
            $table->boolean('campaign_period')->default(false);
            $table->string('eligible_service')->default('all_services');
            $table->decimal('discount_rate', 10)->default(0);
            $table->string('discount_type', 50)->default('percentage');
            $table->boolean('discount_limit')->default(false);
            $table->decimal('capped_amount', 12)->default(0);

            // Redemption
            $table->string('claim_method')->default('point_to_claim');
            $table->string('claim_limit')->default('limited');
            $table->integer('voucher_limit')->default(0);
            $table->string('renew_voucher_limit', 100)->default('never_renew');
            $table->integer('claim_amount_per_member')->default(0);
            $table->string('renew_claim_limit', 100)->default('never_renew');
            $table->integer('validity_count')->nullable();
            $table->string('validity_count_type')->default('day');
            $table->string('requirement_type')->default('no_requirement');
            $table->string('valid_type', 100)->default('all_day_time');
            $table->boolean('can_stack')->default(false);

            // Conditional redemption fields
            $table->integer('redeem_point')->nullable();
            $table->string('voucher_code')->nullable();
            $table->decimal('min_spend_amount', 12)->default(0);

            // Settings
            $table->longText('voucher_highlight')->nullable();
            $table->longText('voucher_terms')->nullable();

            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
