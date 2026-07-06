<?php

declare(strict_types=1);

use App\Enums\SocialAccount\Status;
use App\Models\GoogleDriveConnection;
use App\Models\User;
use App\Models\Workspace;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->workspace = Workspace::factory()->create(['user_id' => $this->user->id]);
    $this->user->update(['current_workspace_id' => $this->workspace->id]);
    $this->workspace->members()->attach($this->user->id, ['role' => 'owner']);
});

test('google drive connect redirects to oauth provider and stores workspace in session', function () {
    $driverMock = Mockery::mock();
    $driverMock->shouldReceive('scopes')->andReturnSelf();
    $driverMock->shouldReceive('with')->andReturnSelf();
    $driverMock->shouldReceive('redirect')->andReturn(Mockery::mock([
        'getTargetUrl' => 'https://accounts.google.com/o/oauth2/v2/auth?test=1',
    ]));

    Socialite::shouldReceive('driver')
        ->with('google-drive')
        ->andReturn($driverMock);

    $response = $this->actingAs($this->user)
        ->withHeader('X-Inertia', 'true')
        ->get(route('app.social.google-drive.connect'));

    // Inertia::location returns 409 with X-Inertia header
    $response->assertStatus(409);

    expect(session('google_drive_workspace'))->toBe($this->workspace->id);
});

test('google drive oauth callback creates connection on success', function () {
    session(['google_drive_workspace' => $this->workspace->id]);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google_user_drive_123');
    $socialiteUser->token = 'drive-access-token';
    $socialiteUser->refreshToken = 'drive-refresh-token';
    $socialiteUser->expiresIn = 3600;
    $socialiteUser->user = ['email' => 'connected@example.com'];

    Socialite::shouldReceive('driver')
        ->with('google-drive')
        ->andReturn(Mockery::mock([
            'user' => $socialiteUser,
        ]));

    $response = $this->actingAs($this->user)
        ->get(route('app.social.google-drive.callback'));

    $response->assertOk();
    $response->assertViewIs('auth.social-callback');
    $response->assertViewHas('success', true);

    expect(GoogleDriveConnection::query()->count())->toBe(1);

    $connection = GoogleDriveConnection::first();
    expect($connection->workspace_id)->toBe($this->workspace->id);
    expect($connection->google_user_id)->toBe('google_user_drive_123');
    expect($connection->access_token)->toBe('drive-access-token');
    expect($connection->refresh_token)->toBe('drive-refresh-token');
    expect($connection->status)->toBe(Status::Connected);
    expect($connection->meta['email'])->toBe('connected@example.com');
});

test('google drive oauth callback fails gracefully when session is missing', function () {
    // No session('google_drive_workspace') set.
    $response = $this->actingAs($this->user)
        ->get(route('app.social.google-drive.callback'));

    $response->assertOk();
    $response->assertViewIs('auth.social-callback');
    $response->assertViewHas('success', false);

    expect(GoogleDriveConnection::query()->count())->toBe(0);
});

test('google drive oauth callback updates existing connection (updateOrCreate)', function () {
    session(['google_drive_workspace' => $this->workspace->id]);

    GoogleDriveConnection::create([
        'workspace_id' => $this->workspace->id,
        'google_user_id' => 'google_user_drive_123',
        'access_token' => 'old-token',
        'refresh_token' => 'old-refresh',
        'status' => Status::Disconnected,
    ]);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('google_user_drive_123');
    $socialiteUser->token = 'new-access-token';
    $socialiteUser->refreshToken = 'new-refresh-token';
    $socialiteUser->expiresIn = 3600;
    $socialiteUser->user = ['email' => 'connected@example.com'];

    Socialite::shouldReceive('driver')
        ->with('google-drive')
        ->andReturn(Mockery::mock([
            'user' => $socialiteUser,
        ]));

    $this->actingAs($this->user)
        ->get(route('app.social.google-drive.callback'));

    expect(GoogleDriveConnection::query()->count())->toBe(1);
    $connection = GoogleDriveConnection::first();
    expect($connection->access_token)->toBe('new-access-token');
    expect($connection->refresh_token)->toBe('new-refresh-token');
    expect($connection->status)->toBe(Status::Connected);
});
