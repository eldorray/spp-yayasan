<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('name');
            $table->string('tempat_lahir')->nullable()->after('jenis_kelamin');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->text('alamat')->nullable()->after('tanggal_lahir');
            $table->string('telpon')->nullable()->after('alamat');
            $table->string('nama_ayah')->nullable()->after('telpon');
            $table->string('nama_ibu')->nullable()->after('nama_ayah');
            $table->string('nama_wali')->nullable()->after('nama_ibu');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
                'alamat', 'telpon', 'nama_ayah', 'nama_ibu', 'nama_wali',
            ]);
        });
    }
};
