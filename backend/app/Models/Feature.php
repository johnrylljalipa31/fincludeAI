<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_count',
        'avg_monthly_income',
        'income_consistency',
        'income_growth_rate',
        'inflow_outflow_ratio',
        'cash_flow_stability',
        'active_financial_days',
        'avg_monthly_balance',
        'min_account_balance',
        'transaction_frequency',
        'payment_consistency',
        'gig_earnings_activity',
        'burn_rate_trajectory',
        'raw_features',
        'computed_at',
    ];

    protected $casts = [
        'raw_features' => 'array',
        'computed_at' => 'datetime',
        'avg_monthly_income' => 'float',
        'income_consistency' => 'float',
        'income_growth_rate' => 'float',
        'inflow_outflow_ratio' => 'float',
        'cash_flow_stability' => 'float',
        'avg_monthly_balance' => 'float',
        'min_account_balance' => 'float',
        'transaction_frequency' => 'float',
        'payment_consistency' => 'float',
        'gig_earnings_activity' => 'float',
        'burn_rate_trajectory' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
