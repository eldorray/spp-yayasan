<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'institution_id', 'nis', 'nisn', 'name', 'jenis_kelamin',
        'tempat_lahir', 'tanggal_lahir', 'alamat', 'telpon',
        'nama_ayah', 'nama_ibu', 'nama_wali', 'domicile', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tanggal_lahir' => 'date',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function placements(): HasMany
    {
        return $this->hasMany(StudentPlacement::class);
    }

    public function monthlyBills(): HasMany
    {
        return $this->hasMany(MonthlyBill::class);
    }

    public function activityBills(): HasMany
    {
        return $this->hasMany(ActivityBill::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isKotaTangerang(): bool
    {
        return $this->domicile === 'kota_tangerang';
    }

    public function isExemptFromMonthlyFee(): bool
    {
        return $this->institution->code === 'smp' && $this->isKotaTangerang();
    }
}
