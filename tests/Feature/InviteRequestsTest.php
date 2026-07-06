<?php

declare(strict_types=1);

use App\Actions\Invite\CreateInvite;
use App\Enums\Notification\Channel;
use App\Enums\Notification\Type as NotificationType;
use App\Enums\UserWorkspace\Role as WorkspaceRole;
use App\Jobs\SendNotification;
use App\Mail\WorkspaceInvite as WorkspaceInviteMail;
use App\Models\Account;
use App\Models\Invite;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    config(['trypost.self_hosted' => true]);

    $this->account = Account::factory()->create();
    $this->user = User::factory()->create([
        'account_id' => $this->account->id,
    ]);
    $this->account->update(['owner_id' => $this->user->id]);
    $this->workspace = Workspace::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
    ]);
    $this->workspace->members()->attach($this->user->id, ['role' => WorkspaceRole::Admin->value]);
    $this->user->update(['current_workspace_id' => $this->workspace->id]);
});

test('invite requests page requires authentication', function () {
    $response = $this->get(route('app.invites.requests'));

    $response->assertRedirect(route('login'));
});

test('invite requests page shows pending invites for the logged user email', function () {
    // Two different accounts inviting the same email — unique constraint on (email, account_id).
    Invite::factory()->create([
        'account_id' => $this->account->id,
        'invited_by' => $this->user->id,
        'email' => $this->user->email,
        'workspaces' => [$this->workspace->id],
        'role' => WorkspaceRole::Member,
    ]);

    $otherAccount = Account::factory()->create();
    $otherWorkspace = Workspace::factory()->create([
        'account_id' => $otherAccount->id,
    ]);

    Invite::factory()->create([
        'account_id' => $otherAccount->id,
        'email' => $this->user->email,
        'workspaces' => [$otherWorkspace->id],
        'role' => WorkspaceRole::Admin,
    ]);

    $response = $this->actingAs($this->user)->get(route('app.invites.requests'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('app/invites/Requests', false)
        ->has('invites', 2)
    );
});

test('invite requests page hides accepted invites', function () {
    Invite::factory()->create([
        'account_id' => $this->account->id,
        'invited_by' => $this->user->id,
        'email' => $this->user->email,
        'workspaces' => [$this->workspace->id],
        'accepted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->get(route('app.invites.requests'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('app/invites/Requests', false)
        ->has('invites', 0)
    );
});

test('invite requests page hides invites for other emails', function () {
    Invite::factory()->create([
        'account_id' => $this->account->id,
        'invited_by' => $this->user->id,
        'email' => 'someone-else@example.com',
        'workspaces' => [$this->workspace->id],
    ]);

    $response = $this->actingAs($this->user)->get(route('app.invites.requests'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('app/invites/Requests', false)
        ->has('invites', 0)
    );
});

test('creating an invite for an existing user dispatches an in-app notification', function () {
    Bus::fake([SendNotification::class]);

    $invitedAccount = Account::factory()->create();
    $invitedUser = User::factory()->create([
        'account_id' => $invitedAccount->id,
        'email' => 'invited@example.com',
    ]);
    $invitedWorkspace = Workspace::factory()->create([
        'user_id' => $invitedUser->id,
        'account_id' => $invitedAccount->id,
    ]);
    $invitedUser->update(['current_workspace_id' => $invitedWorkspace->id]);

    $this->actingAs($this->user);

    CreateInvite::execute($this->workspace, [
        'email' => $invitedUser->email,
        'role' => WorkspaceRole::Member->value,
    ]);

    Bus::assertDispatched(SendNotification::class, function (SendNotification $job) use ($invitedUser, $invitedWorkspace) {
        return $job->user->id === $invitedUser->id
            && $job->workspaceId === $invitedWorkspace->id
            && $job->type === NotificationType::InviteReceived
            && $job->channel === Channel::InApp;
    });

    Mail::assertQueued(WorkspaceInviteMail::class);
});

test('creating an invite for a non-existing email does not dispatch in-app notification but still sends email', function () {
    Bus::fake([SendNotification::class]);

    $this->actingAs($this->user);

    CreateInvite::execute($this->workspace, [
        'email' => 'never-seen-before@example.com',
        'role' => WorkspaceRole::Member->value,
    ]);

    Bus::assertNotDispatched(SendNotification::class);
    Mail::assertQueued(WorkspaceInviteMail::class);
});

test('creating an invite for an existing user without current workspace does not dispatch notification', function () {
    Bus::fake([SendNotification::class]);

    $invitedAccount = Account::factory()->create();
    User::factory()->create([
        'account_id' => $invitedAccount->id,
        'email' => 'no-workspace@example.com',
        'current_workspace_id' => null,
    ]);

    $this->actingAs($this->user);

    CreateInvite::execute($this->workspace, [
        'email' => 'no-workspace@example.com',
        'role' => WorkspaceRole::Member->value,
    ]);

    Bus::assertNotDispatched(SendNotification::class);
    Mail::assertQueued(WorkspaceInviteMail::class);
});
