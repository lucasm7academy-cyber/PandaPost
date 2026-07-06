<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\SocialAccount\Status;
use App\Models\GoogleDriveConnection;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class GoogleDriveController extends SocialController
{
    protected string $driver = 'google-drive';

    protected array $scopes = [
        'https://www.googleapis.com/auth/drive.readonly',
    ];

    public function connect(Request $request): Response|RedirectResponse
    {
        $workspace = $request->user()->currentWorkspace;

        if (! $workspace) {
            return redirect()->route('app.workspaces.create');
        }

        $this->authorize('manageAccounts', $workspace);

        session([
            'google_drive_workspace' => $workspace->id,
        ]);

        return $this->redirectToGoogle();
    }

    public function callback(Request $request): View
    {
        $workspaceId = session('google_drive_workspace');

        if (! $workspaceId) {
            return $this->popupCallback(false, __('accounts.popup_callback.session_expired'), 'google-drive');
        }

        $workspace = Workspace::find($workspaceId);

        if (! $workspace || ! $request->user()->can('manageAccounts', $workspace)) {
            return $this->popupCallback(false, __('accounts.popup_callback.workspace_not_found'), 'google-drive');
        }

        try {
            $socialUser = Socialite::driver($this->driver)->user();

            $workspace->googleDriveConnections()->updateOrCreate(
                [
                    'google_user_id' => $socialUser->getId(),
                ],
                [
                    'access_token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'token_expires_at' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn) : null,
                    'scopes' => $this->scopes,
                    'status' => Status::Connected,
                    'error_message' => null,
                    'disconnected_at' => null,
                    'meta' => [
                        'email' => data_get($socialUser->user, 'email'),
                    ],
                ],
            );

            $this->forgetGoogleDriveSession();

            return $this->popupCallback(true, __('accounts.popup_callback.google_drive_connected'), 'google-drive');
        } catch (\Exception $e) {
            Log::error('Google Drive OAuth Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->forgetGoogleDriveSession();

            return $this->popupCallback(false, __('accounts.popup_callback.google_drive_error'), 'google-drive');
        }
    }

    private function redirectToGoogle(): Response
    {
        return Inertia::location(
            Socialite::driver($this->driver)
                ->scopes($this->scopes)
                ->with([
                    'access_type' => 'offline',
                    'prompt' => 'consent',
                    'include_granted_scopes' => 'true',
                ])
                ->redirect()
                ->getTargetUrl()
        );
    }

    private function forgetGoogleDriveSession(): void
    {
        session()->forget('google_drive_workspace');
    }
}
