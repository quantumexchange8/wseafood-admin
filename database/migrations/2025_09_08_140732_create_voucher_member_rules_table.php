<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('voucher_member_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_id');
            $table->string('activation_rule');
            $table->string('event_type')->nullable();
            $table->date('special_holiday_date')->nullable();
            $table->decimal('amount_paid', 12)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('voucher_id')
                ->references('id')
                ->on('vouchers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_member_rules');
    }
};
