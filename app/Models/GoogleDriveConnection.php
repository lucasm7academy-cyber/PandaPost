<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SocialAccount\Status;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleDriveConnection extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workspace_id',
        'google_user_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'status',
        'error_message',
        'disconnected_at',
        'meta',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'token_expires_at' => 'datetime',
            'disconnected_at' => 'datetime',
            'scopes' => 'array',
            'meta' => 'array',
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    protected function isTokenExpired(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->token_expires_at && $this->token_expires_at->isPast(),
        );
    }

    protected function isTokenExpiringSoon(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->token_expires_at && $this->token_expires_at->isBefore(now()->addMinutes(15)),
        );
    }

    public function markAsConnected(): void
    {
        $this->update([
            'status' => Status::Connected,
            'error_message' => null,
            'disconnected_at' => null,
        ]);
    }

    public function markAsDisconnected(string $reason = null): void
    {
        $this->update([
            'status' => Status::Disconnected,
            'error_message' => $reason,
            'disconnected_at' => now(),
        ]);
    }
}
