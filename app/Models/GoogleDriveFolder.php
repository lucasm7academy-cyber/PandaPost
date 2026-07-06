<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleDriveFolder extends Model
{
    /** @use HasFactory<\Database\Factories\GoogleDriveFolderFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'workspace_id',
        'folder_id',
        'folder_name',
        'folder_link',
        'is_active',
        'added_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
