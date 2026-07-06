<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BulkSchedule\Status;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkSchedule extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'media_ids',
        'platforms',
        'days',
        'times',
        'timezone',
        'prompt',
        'signature_id',
        'status',
        'total_posts',
        'created_posts',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'media_ids' => 'array',
            'platforms' => 'array',
            'days' => 'array',
            'times' => 'array',
            'status' => Status::class,
            'total_posts' => 'integer',
            'created_posts' => 'integer',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signature(): BelongsTo
    {
        return $this->belongsTo(WorkspaceSignature::class, 'signature_id');
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => Status::Processing]);
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => Status::Completed]);
    }

    public function markAsFailed(?string $errorMessage = null): void
    {
        $this->update([
            'status' => Status::Failed,
            'error_message' => $errorMessage,
        ]);
    }
}
