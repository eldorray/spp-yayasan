<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_rate_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month'); // 1-12
            $table->unsignedBigInteger('amount'); // nominal tagihan
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id', 'fee_rate_id', 'month'], 'monthly_bills_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_bills');
    }
};
