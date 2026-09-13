<?php

namespace App\Models;

use Database\Factories\CourierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    /** @use HasFactory<CourierFactory> */
    use HasFactory;

    protected $table = 'm_courier';

    protected $primaryKey = 'courier_id';

    protected $fillable = [
        'courier_code',
        'courier_name',
        'courier_phone',
        'courier_email',
        'courier_level',
        'courier_address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'courier_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Every keyword must match courier_name, so "budi agung" finds "Budiono Hadi Agung".
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        $keywords = preg_split('/\s+/', trim($search), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return $query->where(function (Builder $query) use ($keywords) {
            foreach ($keywords as $keyword) {
                $query->where('courier_name', 'like', '%'.$keyword.'%');
            }
        });
    }

    public function scopeLevelIn(Builder $query, string $levels): Builder
    {
        $values = collect(explode(',', $levels))
            ->map(fn (string $level) => trim($level))
            ->filter(fn (string $level) => $level !== '' && ctype_digit($level))
            ->map(fn (string $level) => (int) $level)
            ->unique()
            ->all();

        return $values === [] ? $query : $query->whereIn('courier_level', $values);
    }
}
