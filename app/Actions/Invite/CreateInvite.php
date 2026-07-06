<?php

declare(strict_types=1);

namespace App\Actions\Invite;

use App\Enums\Notification\Channel;
use App\Enums\Notification\Type as NotificationType;
use App\Enums\UserWorkspace\Role as WorkspaceRole;
use App\Jobs\SendNotification;
use App\Mail\WorkspaceInvite as WorkspaceInviteMail;
use App\Models\Invite;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Mail;

class CreateInvite
{
    public static function execute(Workspace $workspace, array $data): Invite
    {
        $role = WorkspaceRole::tryFrom((string) data_get($data, 'role', WorkspaceRole::Member->value))
            ?? WorkspaceRole::Member;

        $invite = Invite::create([
            'account_id' => $workspace->account_id,
            'invited_by' => auth()->id(),
            'email' => data_get($data, 'email'),
            'role' => $role,
            'workspaces' => [$workspace->id],
        ]);

        Mail::to($invite->email)->send(new WorkspaceInviteMail($invite));

        self::dispatchInAppNotification($invite, $workspace);

        return $invite;
    }

    private static function dispatchInAppNotification(Invite $invite, Workspace $workspace): void
    {
        $invitedUser = User::where('email', $invite->email)->first();

        if (! $invitedUser || ! $invitedUser->current_workspace_id) {
            return;
        }

        $accountName = $workspace->account?->name ?? '';

        SendNotification::dispatch(
            user: $invitedUser,
            workspaceId: $invitedUser->current_workspace_id,
            type: NotificationType::InviteReceived,
            channel: Channel::InApp,
            title: __('notifications.invite_received.title', ['account' => $accountName]),
            body: __('notifications.invite_received.body'),
            data: [
                'invite_id' => $invite->id,
                'account_name' => $accountName,
            ],
        );
    }
}
