<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsHistory extends Model
{
    protected $table = 'points_history';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'customer_id',
        'type',
        'points',
        'balance_before',
        'balance_after',
        'source',
        'source_id',
        'description',
        'operator_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'customer_id' => 'integer',
            'points' => 'integer',
            'balance_before' => 'integer',
            'balance_after' => 'integer',
            'source_id' => 'integer',
            'operator_id' => 'integer',
        ];
    }

    const TYPE_EARN = 'earn';
    const TYPE_SPEND = 'spend';
    const TYPE_REFUND = 'refund';
    const TYPE_ADMIN_ADD = 'admin_add';
    const TYPE_ADMIN_DEDUCT = 'admin_deduct';

    const SOURCE_ORDER = 'order';
    const SOURCE_MEAL = 'meal';
    const SOURCE_ADMIN = 'admin';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
