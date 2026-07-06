<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\GoogleDrive\StoreGoogleDriveFolderRequest;
use App\Models\GoogleDriveFolder;
use App\Models\Workspace;
use App\Services\GoogleDrive\GoogleDriveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GoogleDriveFolderController extends Controller
{
    public function index(Workspace $workspace, \Illuminate\Http\Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $workspace);

        $folders = $workspace->googleDriveFolders()
            ->latest()
            ->get()
            ->map(fn ($folder) => [
                'id' => $folder->id,
                'folder_id' => $folder->folder_id,
                'folder_name' => $folder->folder_name,
                'folder_link' => $folder->folder_link,
                'is_active' => $folder->is_active,
                'added_by' => $folder->added_by,
                'created_at' => $folder->created_at->format('Y-m-d'),
            ]);

        if ($request->wantsJson()) {
            return response()->json([
                'folders' => $folders,
            ]);
        }

        return Inertia::render('workspace/GoogleDriveFolders', [
            'workspace' => $workspace,
            'folders' => $folders,
        ]);
    }

    public function store(Workspace $workspace, StoreGoogleDriveFolderRequest $request): RedirectResponse
    {
        $this->authorize('manageAccounts', $workspace);

        try {
            $folderLink = $request->validated('folder_link');
            $folderName = $request->validated('folder_name');

            // Extrair folder_id do link: https://drive.google.com/drive/folders/FOLDER_ID
            preg_match('/folders\/([a-zA-Z0-9-_]+)/', $folderLink, $matches);
            $folderId = $matches[1] ?? null;

            if (! $folderId) {
                return redirect()->back()->withErrors([
                    'folder_link' => 'Link inválido. Use o link de compartilhamento da pasta do Google Drive.',
                ]);
            }

            $workspace->googleDriveFolders()->create([
                'folder_id' => $folderId,
                'folder_name' => $folderName,
                'folder_link' => $folderLink,
                'is_active' => true,
                'added_by' => auth()->user()->email,
            ]);

            return redirect()->route('app.workspace.google-drive-folders.index', $workspace)
                ->with('flash.success', __('Google Drive folder added successfully!'));
        } catch (\Exception $e) {
            \Log::error('Google Drive Folder Creation Error', [
                'error' => $e->getMessage(),
                'workspace' => $workspace->id,
            ]);

            return redirect()->back()->withErrors([
                'folder_link' => 'Erro ao adicionar pasta: ' . $e->getMessage(),
            ]);
        }
    }

    public function toggle(Workspace $workspace, GoogleDriveFolder $folder): RedirectResponse
    {
        $this->authorize('manageAccounts', $workspace);

        $folder->update([
            'is_active' => ! $folder->is_active,
        ]);

        return redirect()->back()
            ->with('flash.success', $folder->is_active ? 'Pasta ativada!' : 'Pasta desativada!');
    }

    public function destroy(Workspace $workspace, GoogleDriveFolder $folder): RedirectResponse
    {
        $this->authorize('manageAccounts', $workspace);

        $folder->delete();

        return redirect()->back()
            ->with('flash.success', 'Pasta removida!');
    }

    public function files(Workspace $workspace, GoogleDriveFolder $folder, Request $request): JsonResponse
    {
        $this->authorize('view', $workspace);

        $connection = $workspace->googleDriveConnection;

        if (! $connection) {
            return response()->json([
                'files' => [],
                'nextPageToken' => null,
                'error' => 'Google Drive not connected',
            ], 400);
        }

        $result = GoogleDriveService::listFilesInFolder(
            $connection,
            $folder->folder_id,
            $request->input('pageToken')
        );

        return response()->json([
            'files' => $result['files'],
            'nextPageToken' => $result['nextPageToken'],
        ]);
    }
}
