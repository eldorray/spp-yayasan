<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = ['academic_year_id', 'institution_id', 'name', 'amount', 'activity_date', 'description'];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(ActivityBill::class);
    }
}
