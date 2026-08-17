<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value', 'label', 'type', 'group', 'sort_order'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $s = static::where('key', $key)->first();
        return $s ? $s->value : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
