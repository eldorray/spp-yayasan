<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $setting = self::find($key);

            return $setting ? $setting->value : $default;
        } catch (\Throwable $e) {
            Log::error('Setting::get failed for key ['.$key.']: '.$e->getMessage());

            return $default;
        }
    }

    /**
     * Set a setting by key.
     */
    public static function set(string $key, mixed $value): void
    {
        try {
            self::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        } catch (\Throwable $e) {
            Log::error('Setting::set failed for key ['.$key.']: '.$e->getMessage());
        }
    }
}
