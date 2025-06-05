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
        Schema::create('stamp_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('attendance_id');
            $table->date('request_date');
            $table->time('original_clock_in')->nullable();
            $table->time('original_clock_out')->nullable();
            $table->json('original_breaks_json')->nullable();
            $table->time('requested_clock_in')->nullable();
            $table->time('requested_clock_out')->nullable();
            $table->json('requested_breaks_json')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->time('reviewed_at')->nullable();
            $table->timestamps();
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stamp_correction_requests');
    }
};