<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class MealConfig extends Model
{
    protected $table = 'meal_configs';

    protected $fillable = [
        'meal_type',
        'name',
        'price',
        'order_start_time',
        'order_end_time',
        'advance_days',
        'description',
        'cover_image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'advance_days' => 'integer',
            'status' => 'integer',
        ];
    }

    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    const TYPE_BREAKFAST = 'breakfast';
    const TYPE_LUNCH = 'lunch';
    const TYPE_DINNER = 'dinner';

    public function scopeEnabled($query)
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('meal_type', $type);
    }

    public function isAvailableForDate(\DateTime $date): bool
    {
        if ($this->status !== self::STATUS_ENABLED) {
            return false;
        }

        $now = now();
        $targetDate = \Carbon\Carbon::instance($date)->startOfDay();
        $today = $now->copy()->startOfDay();

        $daysUntilTarget = $today->diffInDays($targetDate, false);

        if ($daysUntilTarget < $this->advance_days) {
            return false;
        }

        // 仅在最小提前天数当天检查截止时间
        if ($this->order_end_time && $daysUntilTarget === $this->advance_days) {
            $endTime = \Carbon\Carbon::parse($this->order_end_time);
            if ($now->format('H:i:s') > $endTime->format('H:i:s')) {
                return false;
            }
        }

        return true;
    }
}
