<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone_number',
        'id_type',
        'id_number',
        'profile_picture',
        'password',
        'user_type',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function consents()
    {
        return $this->hasMany(Consent::class);
    }

    public function latestConsent()
    {
        return $this->hasOne(Consent::class)->latestOfMany();
    }

    public function smileAccounts()
    {
        return $this->hasMany(SmileAccount::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function creditScores()
    {
        return $this->hasMany(CreditScore::class);
    }

    public function latestCreditScore()
    {
        return $this->hasOne(CreditScore::class)->latestOfMany();
    }

    public function loanApplications()
    {
        return $this->hasMany(LoanApplication::class);
    }

    public function loans()
    {
        return $this->hasMany(LoanApplication::class);
    }

    public function dataSources()
    {
        return $this->hasMany(DataSource::class);
    }

    public function features()
    {
        return $this->hasMany(Feature::class);
    }

    public function latestFeatures()
    {
        return $this->hasOne(Feature::class)->latestOfMany();
    }

    public function explanations()
    {
        return $this->hasMany(Explanation::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
