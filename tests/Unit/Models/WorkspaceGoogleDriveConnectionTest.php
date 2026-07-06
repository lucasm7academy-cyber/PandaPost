<?php

declare(strict_types=1);

use App\Enums\SocialAccount\Status;
use App\Models\GoogleDriveConnection;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('googleDriveConnection returns the most recent connection without breaking on uuid primary keys', function () {
    // Reproduces the "function max(uuid) does not exist" PostgreSQL error
    // that happened when latestOfMany() defaulted to MAX(id) on a uuid pk.
    $workspace = Workspace::factory()->create();

    $older = GoogleDriveConnection::create([
        'workspace_id' => $workspace->id,
        'google_user_id' => 'older',
        'access_token' => 'older-token',
        'refresh_token' => 'older-refresh',
        'token_expires_at' => now()->addHour(),
        'status' => Status::Connected,
    ]);
    $older->forceFill(['created_at' => now()->subDay()])->save();

    $newer = GoogleDriveConnection::create([
        'workspace_id' => $workspace->id,
        'google_user_id' => 'newer',
        'access_token' => 'newer-token',
        'refresh_token' => 'newer-refresh',
        'token_expires_at' => now()->addHour(),
        'status' => Status::Connected,
    ]);
    $newer->forceFill(['created_at' => now()])->save();

    $resolved = $workspace->fresh()->googleDriveConnection;

    expect($resolved)->not->toBeNull();
    expect($resolved->id)->toBe($newer->id);
    expect($resolved->id)->not->toBe($older->id);
});
