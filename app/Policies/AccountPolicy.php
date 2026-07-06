<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Plan\Slug;
use App\Features\MonthlyCreditsLimit;
use App\Models\Account;
use App\Models\AiUsageLog;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Laravel\Pennant\Feature;

class AccountPolicy
{
    public function update(User $user, Account $account): bool
    {
        return $user->id === $account->owner_id;
    }

    public function manageBilling(User $user, Account $account): bool
    {
        return $user->id === $account->owner_id;
    }

    /**
     * Authorize using AI features. Allows when the account still has monthly
     * credits remaining. Manual post creation is unaffected — only AI calls
     * are gated by this check.
     */
    public function useAi(User $user, Account $account): Response
    {
        if (config('trypost.self_hosted')) {
            return Response::allow();
        }

        $limit = (int) Feature::for($account)->value(MonthlyCreditsLimit::class);
        $used = AiUsageLog::monthlyCredits($account->id);

        if ($used >= $limit) {
            return Response::deny(__('billing.flash.credits_exhausted', [
                'limit' => (string) $limit,
            ]));
        }

        return Response::allow();
    }

    /**
     * Authorize using Bulk Schedule. Self-hosted always allowed; on cloud,
     * Starter plan is blocked and must upgrade to Plus or higher.
     */
    public function useBulkSchedule(User $user, Account $account): Response
    {
        if (config('trypost.self_hosted')) {
            return Response::allow();
        }

        if ($account->plan?->slug === Slug::Starter) {
            return Response::deny(__('posts.bulk.flash.starter_blocked'));
        }

        return Response::allow();
    }

    /**
     * Authorize swapping the account's subscription to the given plan.
     *
     * Denies if the account's current usage exceeds any of the target plan's
     * limits (workspaces, social accounts, members + pending invites).
     */
    public function swapPlan(User $user, Account $account, Plan $plan): Response
    {
        if ($user->id !== $account->owner_id) {
            return Response::deny(__('billing.flash.cannot_manage'));
        }

        $usage = $account->usage();

        $checks = [
            'workspaces' => [
                'count' => $usage['workspaceCount'],
                'limit' => (int) $plan->workspace_limit,
            ],
            'social_accounts' => [
                'count' => $usage['socialAccountCount'],
                'limit' => (int) $plan->social_account_limit,
            ],
            'members' => [
                'count' => $usage['memberCount'] + $usage['pendingInviteCount'],
                'limit' => (int) $plan->member_limit,
            ],
        ];

        foreach ($checks as $type => $data) {
            if ($data['count'] > $data['limit']) {
                return Response::deny(__('billing.flash.cannot_downgrade.'.$type, [
                    'plan' => $plan->name,
                    'count' => (string) $data['count'],
                    'limit' => (string) $data['limit'],
                ]));
            }
        }

        return Response::allow();
    }
}
