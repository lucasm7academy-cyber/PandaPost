<?php

declare(strict_types=1);

namespace App\Broadcasting;

use App\Models\User;

class UserBulkScheduleChannel
{
    public function join(User $user, User $owner, string $bulkScheduleId): bool
    {
        return $user->is($owner);
    }
}
