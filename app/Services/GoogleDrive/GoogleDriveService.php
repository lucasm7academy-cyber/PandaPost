<?php

declare(strict_types=1);

namespace App\Services\GoogleDrive;

use App\Models\GoogleDriveConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    private const DRIVE_API_BASE = 'https://www.googleapis.com/drive/v3';

    private const DRIVE_API_UPLOAD = 'https://www.googleapis.com/upload/drive/v3';

    /**
     * List files from Google Drive.
     *
     * @return array{files: array, nextPageToken: ?string}
     */
    public static function listFiles(GoogleDriveConnection $connection, ?string $pageToken = null): array
    {
        try {
            if ($connection->is_token_expired) {
                self::refreshToken($connection);
            }

            $params = [
                'q' => "mimeType contains 'image/' or mimeType contains 'video/'",
                'fields' => 'files(id,name,mimeType,modifiedTime,thumbnailLink,webContentLink,size),nextPageToken',
                'pageSize' => 20,
                'orderBy' => 'modifiedTime desc',
            ];

            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }

            $response = Http::withToken($connection->access_token)
                ->get(self::DRIVE_API_BASE.'/files', $params);

            if ($response->failed()) {
                Log::error('Google Drive list files failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return ['files' => [], 'nextPageToken' => null];
            }

            $data = $response->json();

            return [
                'files' => data_get($data, 'files', []),
                'nextPageToken' => data_get($data, 'nextPageToken'),
            ];
        } catch (\Exception $e) {
            Log::error('Google Drive list files error', [
                'error' => $e->getMessage(),
            ]);

            return ['files' => [], 'nextPageToken' => null];
        }
    }

    /**
     * List files from a specific Google Drive folder.
     *
     * @return array{files: array, nextPageToken: ?string}
     */
    public static function listFilesInFolder(GoogleDriveConnection $connection, string $folderId, ?string $pageToken = null): array
    {
        try {
            if ($connection->is_token_expired) {
                self::refreshToken($connection);
            }

            $params = [
                'q' => "'{$folderId}' in parents and trashed = false",
                'fields' => 'files(id,name,mimeType,modifiedTime,thumbnailLink,webContentLink,size),nextPageToken',
                'pageSize' => 1000,
                'orderBy' => 'modifiedTime desc',
            ];

            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }

            $response = Http::withToken($connection->access_token)
                ->get(self::DRIVE_API_BASE.'/files', $params);

            if ($response->failed()) {
                Log::error('Google Drive list folder files failed', [
                    'folderId' => $folderId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return ['files' => [], 'nextPageToken' => null];
            }

            $data = $response->json();

            return [
                'files' => data_get($data, 'files', []),
                'nextPageToken' => data_get($data, 'nextPageToken'),
            ];
        } catch (\Exception $e) {
            Log::error('Google Drive list folder files error', [
                'folderId' => $folderId,
                'error' => $e->getMessage(),
            ]);

            return ['files' => [], 'nextPageToken' => null];
        }
    }

    /**
     * Search files in Google Drive.
     *
     * @return array{files: array, nextPageToken: ?string}
     */
    public static function searchFiles(GoogleDriveConnection $connection, string $query, ?string $pageToken = null): array
    {
        try {
            if ($connection->is_token_expired) {
                self::refreshToken($connection);
            }

            $searchQuery = "({$query} in name or {$query} in fullText) and (mimeType contains 'image/' or mimeType contains 'video/')";

            $params = [
                'q' => $searchQuery,
                'fields' => 'files(id,name,mimeType,modifiedTime,thumbnailLink,webContentLink,size),nextPageToken',
                'pageSize' => 20,
            ];

            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }

            $response = Http::withToken($connection->access_token)
                ->get(self::DRIVE_API_BASE.'/files', $params);

            if ($response->failed()) {
                Log::error('Google Drive search files failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return ['files' => [], 'nextPageToken' => null];
            }

            $data = $response->json();

            return [
                'files' => data_get($data, 'files', []),
                'nextPageToken' => data_get($data, 'nextPageToken'),
            ];
        } catch (\Exception $e) {
            Log::error('Google Drive search files error', [
                'error' => $e->getMessage(),
            ]);

            return ['files' => [], 'nextPageToken' => null];
        }
    }

    /**
     * Download file content from Google Drive.
     */
    public static function downloadFile(GoogleDriveConnection $connection, string $fileId, string $fileName): ?string
    {
        try {
            if ($connection->is_token_expired) {
                self::refreshToken($connection);
            }

            $response = Http::withToken($connection->access_token)
                ->get(self::DRIVE_API_BASE."/files/{$fileId}", [
                    'alt' => 'media',
                ]);

            if ($response->failed()) {
                Log::error('Google Drive download file failed', [
                    'fileId' => $fileId,
                    'status' => $response->status(),
                ]);

                return null;
            }

            return $response->body();
        } catch (\Exception $e) {
            Log::error('Google Drive download file error', [
                'error' => $e->getMessage(),
                'fileId' => $fileId,
            ]);

            return null;
        }
    }

    /**
     * Refresh the access token using the refresh token.
     */
    private static function refreshToken(GoogleDriveConnection $connection): void
    {
        try {
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'refresh_token' => $connection->refresh_token,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->failed()) {
                Log::error('Google Drive token refresh failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return;
            }

            $data = $response->json();

            $connection->update([
                'access_token' => $data['access_token'],
                'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
            ]);
        } catch (\Exception $e) {
            Log::error('Google Drive token refresh error', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
