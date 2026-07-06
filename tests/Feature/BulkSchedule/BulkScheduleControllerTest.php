<?php

declare(strict_types=1);

use App\Enums\BulkSchedule\Status as BulkStatus;
use App\Enums\UserWorkspace\Role;
use App\Models\Account;
use App\Models\BulkSchedule;
use App\Models\Plan;
use App\Models\User;
use App\Models\Workspace;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->plan = Plan::firstOrCreate(['slug' => 'plus'], [
        'name' => 'Plus',
        'social_account_limit' => 10,
        'member_limit' => 5,
        'workspace_limit' => 5,
        'monthly_credits_limit' => 5000,
        'sort' => 2,
        'is_archived' => false,
    ]);

    $this->account = Account::factory()->create(['plan_id' => $this->plan->id]);
    $this->user = User::factory()->create(['account_id' => $this->account->id]);
    $this->account->update(['owner_id' => $this->user->id]);

    $this->workspace = Workspace::factory()->create([
        'account_id' => $this->account->id,
        'user_id' => $this->user->id,
    ]);
    $this->workspace->members()->attach($this->user->id, ['role' => Role::Member->value]);
    $this->user->update(['current_workspace_id' => $this->workspace->id]);
});

test('loading page requires authentication', function () {
    $bulkSchedule = BulkSchedule::create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'media_ids' => [],
        'platforms' => [],
        'days' => ['2026-07-01'],
        'times' => ['09:00'],
        'timezone' => 'UTC',
        'prompt' => 'Brief',
        'status' => BulkStatus::Pending,
        'total_posts' => 0,
        'created_posts' => 0,
    ]);

    $this->get(route('app.posts.bulk.loading', $bulkSchedule->id))
        ->assertStatus(Response::HTTP_FOUND);
});

test('loading page renders Inertia component with progress props', function () {
    $bulkSchedule = BulkSchedule::create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'media_ids' => [],
        'platforms' => [],
        'days' => ['2026-07-01'],
        'times' => ['09:00'],
        'timezone' => 'UTC',
        'prompt' => 'Brief',
        'status' => BulkStatus::Processing,
        'total_posts' => 5,
        'created_posts' => 2,
    ]);

    $this->actingAs($this->user)
        ->get(route('app.posts.bulk.loading', $bulkSchedule->id))
        ->assertInertia(fn ($page) => $page
            ->component('posts/bulk/Loading')
            ->where('bulkScheduleId', $bulkSchedule->id)
            ->where('channel', "user.{$this->user->id}.bulk-schedule.{$bulkSchedule->id}")
            ->where('totalPosts', 5)
            ->where('createdPosts', 2)
            ->where('status', 'processing')
        );
});

test('loading page rejects non-uuid identifiers', function () {
    $this->actingAs($this->user)
        ->get(route('app.posts.bulk.loading', 'not-a-uuid'))
        ->assertStatus(Response::HTTP_NOT_FOUND);
});
