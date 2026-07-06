<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Enums\Media\Source;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\GoogleDrive\DownloadGoogleDriveFileRequest;
use App\Http\Resources\App\MediaResource;
use App\Models\GoogleDriveFolder;
use App\Models\Workspace;
use App\Services\GoogleDrive\GoogleDriveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class GoogleDrivePickerController extends Controller
{
    /**
     * Download a file from a vinculated Google Drive folder and persist it as
     * a Media on the workspace, returning the resource so the picker can push
     * it into the current post's selection.
     */
    public function download(
        Workspace $workspace,
        GoogleDriveFolder $folder,
        DownloadGoogleDriveFileRequest $request,
    ): MediaResource|JsonResponse {
        $this->authorize('createPost', $workspace);

        if ($folder->workspace_id !== $workspace->id) {
            return response()->json(
                ['error' => 'Folder does not belong to this workspace'],
                Response::HTTP_NOT_FOUND,
            );
        }

        if (! $folder->is_active) {
            return response()->json(
                ['error' => 'Folder is inactive'],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $connection = $workspace->googleDriveConnection;

        if (! $connection) {
            return response()->json(
                ['error' => 'Google Drive not connected'],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $fileId = $request->validated('file_id');
        $fileName = $request->validated('file_name');
        $mimeType = $request->validated('mime_type');

        try {
            $fileContent = GoogleDriveService::downloadFile($connection, $fileId, $fileName);

            if (! $fileContent) {
                return response()->json(
                    ['error' => 'Failed to download file from Google Drive'],
                    Response::HTTP_BAD_GATEWAY,
                );
            }

            $type = str_starts_with($mimeType, 'video/') ? 'video' : 'image';
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $storedFilename = Str::uuid()->toString().($extension ? ".{$extension}" : '');
            $path = "medias/{$storedFilename}";

            Storage::put($path, $fileContent);

            $media = $workspace->media()->create([
                'group_id' => Str::uuid()->toString(),
                'collection' => 'assets',
                'type' => $type,
                'path' => $path,
                'original_filename' => $fileName,
                'mime_type' => $mimeType,
                'size' => strlen($fileContent),
                'order' => 0,
                'meta' => [
                    'source' => Source::GoogleDrive->value,
                    'google_drive_id' => $fileId,
                    'google_drive_folder_id' => $folder->id,
                ],
            ]);

            return new MediaResource($media);
        } catch (\Exception $e) {
            Log::error('Google Drive picker download error', [
                'error' => $e->getMessage(),
                'workspace_id' => $workspace->id,
                'folder_id' => $folder->id,
                'file_id' => $fileId,
            ]);

            return response()->json(
                ['error' => 'Failed to process file'],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
