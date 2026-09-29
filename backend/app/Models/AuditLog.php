<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'details',
        'status',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $action, ?int $userId = null, ?array $details = null, string $status = 'success'): self
    {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'System/API',
            'details' => $details,
            'status' => $status,
        ]);
    }
}
