<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'score',
        'risk_tier',
        'model_version',
        'projected_disposable_cash_flow',
        'approved_ceiling',
        'shap_factors',
        'psite_metrics',
        'scored_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'projected_disposable_cash_flow' => 'decimal:2',
        'approved_ceiling' => 'decimal:2',
        'shap_factors' => 'array',
        'psite_metrics' => 'array',
        'scored_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function explanations()
    {
        return $this->hasMany(Explanation::class);
    }
}
