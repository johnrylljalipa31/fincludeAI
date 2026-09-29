<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{
    use HasFactory;

    protected $table = 'data_sources';

    protected $fillable = [
        'user_id',
        'provider',
        'source_type',
        'account_id',
        'account_name',
        'status',
        'connected_at',
        'last_synced_at',
        'metadata',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'metadata' => 'array',
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
