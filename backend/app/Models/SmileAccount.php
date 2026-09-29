<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmileAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'smile_user_id',
        'account_id',
        'provider',
        'source',
        'status',
        'connected_at',
        'last_synced_at',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
