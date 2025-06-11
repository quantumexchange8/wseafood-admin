<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('modifier_items', function (Blueprint $table) {
            $table->id();
            $table->string('modifier_name');
            $table->unsignedBigInteger('modifier_group_id');
            $table->decimal('price', 13, 2);
            $table->string('status');
            $table->string('default');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifier_items');
    }
};
