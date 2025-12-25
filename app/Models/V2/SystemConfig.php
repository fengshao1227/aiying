<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    protected $table = 'system_configs';

    protected $fillable = [
        'config_key',
        'config_value',
        'config_type',
        'group',
        'description',
    ];

    public $timestamps = true;

    const TYPE_STRING = 'string';
    const TYPE_NUMBER = 'number';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_JSON = 'json';

    public function scopeByKey($query, string $key)
    {
        return $query->where('config_key', $key);
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function getValueAttribute()
    {
        return $this->castValue($this->config_value, $this->config_type);
    }

    protected function castValue(?string $value, string $type)
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            self::TYPE_NUMBER => (float) $value,
            self::TYPE_BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_JSON => json_decode($value, true),
            default => $value,
        };
    }

    public static function getValue(string $key, $default = null)
    {
        $config = self::byKey($key)->first();
        return $config ? $config->value : $default;
    }

    public static function setValue(string $key, $value, string $type = self::TYPE_STRING, ?string $group = 'general', ?string $description = null): self
    {
        $configValue = match ($type) {
            self::TYPE_JSON => json_encode($value),
            self::TYPE_BOOLEAN => $value ? 'true' : 'false',
            default => (string) $value,
        };

        return self::updateOrCreate(
            ['config_key' => $key],
            [
                'config_value' => $configValue,
                'config_type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );
    }

    public static function getPointsExchangeRate(): int
    {
        return (int) self::getValue('points_exchange_rate', 100);
    }

    public static function getPointsMaxDiscountRate(): int
    {
        return (int) self::getValue('points_max_discount_rate', 50);
    }

    public static function getOrderCancelMinutes(): int
    {
        return (int) self::getValue('order_cancel_minutes', 30);
    }

    public static function getOrderAutoCompleteDays(): int
    {
        return (int) self::getValue('order_auto_complete_days', 7);
    }

    public static function getDailyReportTime(): string
    {
        return (string) self::getValue('daily_report_time', '20:00');
    }

    public static function isNotifyNewMealOrderEnabled(): bool
    {
        return (bool) self::getValue('notify_new_meal_order', true);
    }

    public static function isNotifyNewGoodsOrderEnabled(): bool
    {
        return (bool) self::getValue('notify_new_goods_order', true);
    }

    public static function getCheckinPointsAmount(): int
    {
        return (int) self::getValue('checkin_points_amount', 0);
    }

    public static function getConsumptionPointsRate(): float
    {
        return (float) self::getValue('consumption_points_rate', 0);
    }
}
