<?php

declare(strict_types=1);

use App\Models\GoogleDriveFolder;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('index shows google drive folders', function () {
    config(['trypost.self_hosted' => true]);

    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['account_id' => $user->account_id]);
    $workspace->members()->attach($user, ['role' => 'owner']);

    GoogleDriveFolder::factory()->create(['workspace_id' => $workspace->id]);
    GoogleDriveFolder::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user)
        ->get(route('app.workspace.google-drive-folders.index', $workspace));

    $response->assertStatus(200);
    $response->assertInertia(function ($page) {
        $page->component('workspace/GoogleDriveFolders')
            ->has('folders', 2);
    });
});

test('store creates a new google drive folder', function () {
    config(['trypost.self_hosted' => true]);

    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['account_id' => $user->account_id]);
    $workspace->members()->attach($user, ['role' => 'owner']);

    $response = $this->actingAs($user)
        ->post(route('app.workspace.google-drive-folders.store', $workspace), [
            'folder_name' => 'My Folder',
            'folder_link' => 'https://drive.google.com/drive/folders/1uuQAbpAnPMK5Ui58JmocRTSqiK2WP57b?usp=drive_link',
        ]);

    $response->assertRedirect(route('app.workspace.google-drive-folders.index', $workspace));
    $this->assertDatabaseHas('google_drive_folders', [
        'workspace_id' => $workspace->id,
        'folder_name' => 'My Folder',
        'folder_id' => '1uuQAbpAnPMK5Ui58JmocRTSqiK2WP57b',
        'is_active' => true,
    ]);
});

test('store validates required fields', function () {
    config(['trypost.self_hosted' => true]);

    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['account_id' => $user->account_id]);
    $workspace->members()->attach($user, ['role' => 'owner']);

    $response = $this->actingAs($user)
        ->post(route('app.workspace.google-drive-folders.store', $workspace), [
            'folder_name' => '',
            'folder_link' => '',
        ]);

    $response->assertSessionHasErrors(['folder_name', 'folder_link']);
});

test('store validates url format for folder_link', function () {
    config(['trypost.self_hosted' => true]);

    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['account_id' => $user->account_id]);
    $workspace->members()->attach($user, ['role' => 'owner']);

    $response = $this->actingAs($user)
        ->post(route('app.workspace.google-drive-folders.store', $workspace), [
            'folder_name' => 'My Folder',
            'folder_link' => 'not a valid url',
        ]);

    $response->assertSessionHasErrors('folder_link');
});

test('store extracts folder id correctly from drive link', function () {
    config(['trypost.self_hosted' => true]);

    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['account_id' => $user->account_id]);
    $workspace->members()->attach($user, ['role' => 'owner']);

    $response = $this->actingAs($user)
        ->post(route('app.workspace.google-drive-folders.store', $workspace), [
            'folder_name' => 'My Folder',
            'folder_link' => 'https://drive.google.com/drive/folders/ABC123xyz_-456?usp=drive_link',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('google_drive_folders', [
        'folder_id' => 'ABC123xyz_-456',
    ]);
});

test('store requires manageAccounts permission', function () {
    config(['trypost.self_hosted' => true]);

    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $workspace = Workspace::factory()->create(['account_id' => $user->account_id]);

    // Add anotherUser as a Member (not Admin) in the workspace
    $workspace->members()->attach($anotherUser, ['role' => 'member']);

    $response = $this->actingAs($anotherUser)
        ->post(route('app.workspace.google-drive-folders.store', $workspace), [
            'folder_name' => 'My Folder',
            'folder_link' => 'https://drive.google.com/drive/folders/1uuQAbpAnPMK5Ui58JmocRTSqiK2WP57b?usp=drive_link',
        ]);

    $response->assertForbidden();
});

test('toggle folder active status', function () {
    config(['trypost.self_hosted' => true]);

    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['account_id' => $user->account_id]);
    $workspace->members()->attach($user, ['role' => 'owner']);
    $folder = GoogleDriveFolder::factory()->create([
        'workspace_id' => $workspace->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->patch(route('app.workspace.google-drive-folders.toggle', [$workspace, $folder]));

    $response->assertRedirect();
    $this->assertFalse($folder->fresh()->is_active);
});

test('destroy folder', function () {
    config(['trypost.self_hosted' => true]);

    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['account_id' => $user->account_id]);
    $workspace->members()->attach($user, ['role' => 'owner']);
    $folder = GoogleDriveFolder::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user)
        ->delete(route('app.workspace.google-drive-folders.destroy', [$workspace, $folder]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('google_drive_folders', ['id' => $folder->id]);
});
