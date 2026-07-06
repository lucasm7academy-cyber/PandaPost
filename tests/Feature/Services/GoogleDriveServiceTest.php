<?php

declare(strict_types=1);

use App\Enums\SocialAccount\Status;
use App\Models\GoogleDriveConnection;
use App\Models\Workspace;
use App\Services\GoogleDrive\GoogleDriveService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->connection = GoogleDriveConnection::create([
        'workspace_id' => $this->workspace->id,
        'google_user_id' => 'google_user_test',
        'access_token' => 'valid-access-token',
        'refresh_token' => 'valid-refresh-token',
        'token_expires_at' => now()->addHour(),
        'status' => Status::Connected,
    ]);
});

test('listFilesInFolder returns files and pagination token on success', function () {
    Http::fake([
        'https://www.googleapis.com/drive/v3/files*' => Http::response([
            'files' => [
                ['id' => 'file-1', 'name' => 'video.mp4', 'mimeType' => 'video/mp4'],
                ['id' => 'file-2', 'name' => 'image.png', 'mimeType' => 'image/png'],
            ],
            'nextPageToken' => 'next-token-123',
        ], 200),
    ]);

    $result = GoogleDriveService::listFilesInFolder($this->connection, 'folder-abc-123');

    expect($result)
        ->toHaveKey('files')
        ->toHaveKey('nextPageToken');
    expect($result['files'])->toHaveCount(2);
    expect($result['files'][0]['id'])->toBe('file-1');
    expect($result['nextPageToken'])->toBe('next-token-123');
});

test('listFilesInFolder returns empty array when API fails', function () {
    Http::fake([
        'https://www.googleapis.com/drive/v3/files*' => Http::response(['error' => 'unauthorized'], 401),
    ]);

    $result = GoogleDriveService::listFilesInFolder($this->connection, 'folder-abc-123');

    expect($result['files'])->toBe([]);
    expect($result['nextPageToken'])->toBeNull();
});

test('downloadFile returns content as string on success', function () {
    Http::fake([
        'https://www.googleapis.com/drive/v3/files/file-xyz*' => Http::response('binary-content-here', 200),
    ]);

    $content = GoogleDriveService::downloadFile($this->connection, 'file-xyz', 'media.jpg');

    expect($content)->toBe('binary-content-here');
});

test('downloadFile returns null when API fails', function () {
    Http::fake([
        'https://www.googleapis.com/drive/v3/files/file-xyz*' => Http::response(['error' => 'not found'], 404),
    ]);

    $content = GoogleDriveService::downloadFile($this->connection, 'file-xyz', 'media.jpg');

    expect($content)->toBeNull();
});

test('listFilesInFolder triggers token refresh when expired', function () {
    $this->connection->update([
        'token_expires_at' => now()->subHour(),
    ]);

    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response([
            'access_token' => 'refreshed-token',
            'expires_in' => 3600,
        ], 200),
        'https://www.googleapis.com/drive/v3/files*' => Http::response([
            'files' => [],
            'nextPageToken' => null,
        ], 200),
    ]);

    GoogleDriveService::listFilesInFolder($this->connection->fresh(), 'folder-abc-123');

    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://oauth2.googleapis.com/token'));

    expect($this->connection->fresh()->access_token)->toBe('refreshed-token');
});
