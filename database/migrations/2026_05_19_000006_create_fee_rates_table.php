<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. SPP Bulanan
            $table->unsignedBigInteger('amount'); // nominal in rupiah
            $table->timestamps();

            $table->unique(['academic_year_id', 'institution_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_rates');
    }
};
