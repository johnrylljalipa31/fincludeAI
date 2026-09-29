<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'has_agreed',
        'policy_version',
        'legal_basis',
        'ip_address',
        'scope',
        'agreed_at',
        'revoked_at',
    ];

    protected $casts = [
        'has_agreed' => 'boolean',
        'agreed_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
