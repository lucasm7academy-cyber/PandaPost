<?php

declare(strict_types=1);

use App\Enums\Media\Source;
use App\Enums\SocialAccount\Status;
use App\Models\GoogleDriveConnection;
use App\Models\GoogleDriveFolder;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    config(['trypost.self_hosted' => true]);

    $this->user = User::factory()->create();
    $this->workspace = Workspace::factory()->create(['account_id' => $this->user->account_id]);
    $this->workspace->members()->attach($this->user, ['role' => 'owner']);

    $this->folder = GoogleDriveFolder::factory()->create([
        'workspace_id' => $this->workspace->id,
        'is_active' => true,
    ]);

    GoogleDriveConnection::create([
        'workspace_id' => $this->workspace->id,
        'google_user_id' => 'user-google-id',
        'access_token' => 'valid-token',
        'refresh_token' => 'valid-refresh',
        'token_expires_at' => now()->addHour(),
        'status' => Status::Connected,
    ]);
});

test('download endpoint creates a Media for the workspace from a drive file', function () {
    Http::fake([
        'https://www.googleapis.com/drive/v3/files/drive-file-1*' => Http::response('image-bytes', 200),
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(
            route('app.assets.google-drive.from-folder.download', [$this->workspace, $this->folder]),
            [
                'file_id' => 'drive-file-1',
                'file_name' => 'photo.jpg',
                'mime_type' => 'image/jpeg',
            ],
        );

    $response->assertCreated();
    $response->assertJsonStructure(['id', 'url', 'mime_type', 'original_filename', 'meta']);

    $media = $this->workspace->media()->first();
    expect($media)->not->toBeNull();
    expect($media->original_filename)->toBe('photo.jpg');
    expect($media->mime_type)->toBe('image/jpeg');
    expect($media->meta['source'])->toBe(Source::GoogleDrive->value);
    expect($media->meta['google_drive_id'])->toBe('drive-file-1');
});

test('download endpoint returns 404 when folder belongs to a different workspace', function () {
    $otherWorkspace = Workspace::factory()->create(['account_id' => $this->user->account_id]);
    $otherFolder = GoogleDriveFolder::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this->actingAs($this->user)
        ->postJson(
            route('app.assets.google-drive.from-folder.download', [$this->workspace, $otherFolder]),
            [
                'file_id' => 'drive-file-1',
                'file_name' => 'photo.jpg',
                'mime_type' => 'image/jpeg',
            ],
        );

    $response->assertNotFound();
});

test('download endpoint returns 422 when folder is inactive', function () {
    $this->folder->update(['is_active' => false]);

    $response = $this->actingAs($this->user)
        ->postJson(
            route('app.assets.google-drive.from-folder.download', [$this->workspace, $this->folder]),
            [
                'file_id' => 'drive-file-1',
                'file_name' => 'photo.jpg',
                'mime_type' => 'image/jpeg',
            ],
        );

    $response->assertUnprocessable();
});

test('download endpoint returns 422 when google drive is not connected', function () {
    $this->workspace->googleDriveConnections()->delete();

    $response = $this->actingAs($this->user)
        ->postJson(
            route('app.assets.google-drive.from-folder.download', [$this->workspace, $this->folder]),
            [
                'file_id' => 'drive-file-1',
                'file_name' => 'photo.jpg',
                'mime_type' => 'image/jpeg',
            ],
        );

    $response->assertUnprocessable();
});

test('download endpoint validates required fields', function () {
    $response = $this->actingAs($this->user)
        ->postJson(
            route('app.assets.google-drive.from-folder.download', [$this->workspace, $this->folder]),
            [],
        );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['file_id', 'file_name', 'mime_type']);
});

test('download endpoint returns bad gateway when google drive returns nothing', function () {
    Http::fake([
        'https://www.googleapis.com/drive/v3/files/drive-file-1*' => Http::response(['error' => 'not found'], 404),
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(
            route('app.assets.google-drive.from-folder.download', [$this->workspace, $this->folder]),
            [
                'file_id' => 'drive-file-1',
                'file_name' => 'photo.jpg',
                'mime_type' => 'image/jpeg',
            ],
        );

    $response->assertStatus(502);
});
