<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Explanation extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_score_id',
        'user_id',
        'feature_key',
        'raw_value',
        'shap_value',
        'impact_points',
        'direction',
        'display_text',
    ];

    protected $casts = [
        'raw_value' => 'float',
        'shap_value' => 'float',
        'impact_points' => 'integer',
    ];

    public function creditScore()
    {
        return $this->belongsTo(CreditScore::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
