<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'tenure_days',
        'interest_rate_apr',
        'monthly_amortization',
        'total_repayment',
        'status',
        'purpose',
        'notes',
        'due_date',
        'approved_at',
        'disbursed_at',
        'repaid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tenure_days' => 'integer',
        'interest_rate_apr' => 'decimal:2',
        'monthly_amortization' => 'decimal:2',
        'total_repayment' => 'decimal:2',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
        'repaid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
