<?php

declare(strict_types=1);

use App\Enums\Plan\Slug;
use App\Enums\UserWorkspace\Role;
use App\Jobs\ProcessBulkScheduleJob;
use App\Models\Account;
use App\Models\BulkSchedule;
use App\Models\Media;
use App\Models\Plan;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    config(['trypost.self_hosted' => false]);

    $this->plusPlan = Plan::firstOrCreate(['slug' => Slug::Plus->value], [
        'name' => 'Plus',
        'social_account_limit' => 10,
        'member_limit' => 5,
        'workspace_limit' => 5,
        'monthly_credits_limit' => 5000,
        'sort' => 2,
        'is_archived' => false,
    ]);

    $this->starterPlan = Plan::firstOrCreate(['slug' => Slug::Starter->value], [
        'name' => 'Starter',
        'social_account_limit' => 3,
        'member_limit' => 1,
        'workspace_limit' => 1,
        'monthly_credits_limit' => 1000,
        'sort' => 1,
        'is_archived' => false,
    ]);

    $this->account = Account::factory()->create(['plan_id' => $this->plusPlan->id]);
    $this->user = User::factory()->create(['account_id' => $this->account->id]);
    $this->account->update(['owner_id' => $this->user->id]);

    $this->workspace = Workspace::factory()->create([
        'account_id' => $this->account->id,
        'user_id' => $this->user->id,
    ]);
    $this->workspace->members()->attach($this->user->id, ['role' => Role::Member->value]);
    $this->user->update(['current_workspace_id' => $this->workspace->id]);

    $this->socialAccount = SocialAccount::factory()->create([
        'workspace_id' => $this->workspace->id,
    ]);

    $this->media = Media::factory()->count(3)->create([
        'mediable_id' => $this->workspace->id,
        'mediable_type' => Workspace::class,
        'collection' => 'assets',
    ]);
});

function validPayload(array $overrides = []): array
{
    return array_merge([
        'media_ids' => test()->media->pluck('id')->all(),
        'platforms' => [['social_account_id' => test()->socialAccount->id]],
        'days' => ['2026-07-01', '2026-07-02'],
        'times' => ['09:00', '18:00'],
        'timezone' => 'UTC',
        'prompt' => 'Promote our summer launch with a confident tone.',
    ], $overrides);
}

test('start requires authentication', function () {
    $this->postJson(route('app.posts.bulk.start'), [])
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test('starter plan is blocked with 402', function () {
    Bus::fake();

    $this->account->update(['plan_id' => $this->starterPlan->id]);

    $this->actingAs($this->user)
        ->postJson(route('app.posts.bulk.start'), validPayload())
        ->assertStatus(Response::HTTP_PAYMENT_REQUIRED);

    Bus::assertNotDispatched(ProcessBulkScheduleJob::class);
});

test('plus plan can start bulk schedule', function () {
    Bus::fake();

    $response = $this->actingAs($this->user)
        ->postJson(route('app.posts.bulk.start'), validPayload())
        ->assertStatus(Response::HTTP_ACCEPTED);

    $bulkScheduleId = $response->json('bulk_schedule_id');

    expect($bulkScheduleId)->toBeString()->not->toBeEmpty();
    expect($response->json('channel'))->toBe("user.{$this->user->id}.bulk-schedule.{$bulkScheduleId}");

    $bulkSchedule = BulkSchedule::find($bulkScheduleId);
    expect($bulkSchedule)->not->toBeNull();
    expect($bulkSchedule->workspace_id)->toBe($this->workspace->id);
    expect($bulkSchedule->user_id)->toBe($this->user->id);
    // 3 media × min(3, 2 days × 2 times = 4 slots) = 3 posts.
    expect($bulkSchedule->total_posts)->toBe(3);

    Bus::assertDispatched(ProcessBulkScheduleJob::class, fn ($job) => $job->bulkScheduleId === $bulkScheduleId);
});

test('start validates media_ids required', function () {
    Bus::fake();

    $this->actingAs($this->user)
        ->postJson(route('app.posts.bulk.start'), validPayload(['media_ids' => []]))
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['media_ids']);
});

test('start validates time format', function () {
    Bus::fake();

    $this->actingAs($this->user)
        ->postJson(route('app.posts.bulk.start'), validPayload(['times' => ['25:00']]))
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['times.0']);
});

test('start validates day format', function () {
    Bus::fake();

    $this->actingAs($this->user)
        ->postJson(route('app.posts.bulk.start'), validPayload(['days' => ['07-01-2026']]))
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['days.0']);
});

test('start rejects social_account from another workspace', function () {
    Bus::fake();

    $otherWorkspace = Workspace::factory()->create();
    $foreignAccount = SocialAccount::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $this->actingAs($this->user)
        ->postJson(route('app.posts.bulk.start'), validPayload([
            'platforms' => [['social_account_id' => $foreignAccount->id]],
        ]))
        ->assertStatus(Response::HTTP_FORBIDDEN);
});

test('self_hosted bypasses starter gate', function () {
    Bus::fake();
    config(['trypost.self_hosted' => true]);

    $this->account->update(['plan_id' => $this->starterPlan->id]);

    $this->actingAs($this->user)
        ->postJson(route('app.posts.bulk.start'), validPayload())
        ->assertStatus(Response::HTTP_ACCEPTED);
});
