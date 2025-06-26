<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('view_analytics', function (Blueprint $table) {
            $table->id();
            $table->morphs('subject'); // subject_type, subject_id
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type');
            $table->ipAddress()->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('view_analytics');
    }
};
