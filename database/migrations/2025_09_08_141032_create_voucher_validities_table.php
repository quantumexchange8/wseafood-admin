<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('voucher_validities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_id');
            $table->string('type', 100);
            // for campaign or date-based
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // for time-based
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // for weekday-based (0=Sunday, 6=Saturday)
            $table->unsignedTinyInteger('weekday')->nullable();
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
        Schema::dropIfExists('voucher_validities');
    }
};
