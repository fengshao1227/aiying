<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointsHistory extends Model
{
    use HasFactory;

    protected $table = 'points_history';

    protected $fillable = [
        'user_id',
        'type',
        'points',
        'balance_after',
        'source',
        'source_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'points' => 'integer',
            'balance_after' => 'integer',
            'source_id' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
