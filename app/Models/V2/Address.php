<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use SoftDeletes;

    protected $table = 'addresses';

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'province',
        'city',
        'district',
        'address',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    protected $hidden = ['deleted_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function getFullAddressAttribute(): string
    {
        return $this->province . $this->city . $this->district . $this->address;
    }

    public function setAsDefault(): bool
    {
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->is_default = true;
        return $this->save();
    }
}
