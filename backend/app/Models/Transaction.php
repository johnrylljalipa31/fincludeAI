<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'data_source_id',
        'smile_account_id',
        'transaction_id',
        'amount',
        'type',
        'category',
        'description',
        'transaction_date',
        'raw_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dataSource()
    {
        return $this->belongsTo(DataSource::class);
    }

    public function smileAccount()
    {
        return $this->belongsTo(SmileAccount::class);
    }
}
