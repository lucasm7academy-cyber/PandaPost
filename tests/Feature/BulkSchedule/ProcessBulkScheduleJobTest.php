<?php

declare(strict_types=1);

use App\Enums\BulkSchedule\Status as BulkStatus;
use App\Enums\Post\Status as PostStatus;
use App\Events\BulkScheduleProgress;
use App\Jobs\ProcessBulkScheduleJob;
use App\Models\Account;
use App\Models\BulkSchedule;
use App\Models\Media;
use App\Models\Plan;
use App\Models\Post;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    config(['trypost.self_hosted' => true]);

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

    $this->socialAccount = SocialAccount::factory()->create([
        'workspace_id' => $this->workspace->id,
    ]);
});

function makeBulkMedia(int $count, Workspace $workspace)
{
    return Media::factory()->count($count)->create([
        'mediable_id' => $workspace->id,
        'mediable_type' => Workspace::class,
        'collection' => 'assets',
    ]);
}

test('job creates min(media, slots) scheduled posts and marks completed', function () {
    Event::fake([BulkScheduleProgress::class]);

    $media = makeBulkMedia(3, $this->workspace);

    $bulkSchedule = BulkSchedule::create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'media_ids' => $media->pluck('id')->all(),
        'platforms' => [['social_account_id' => $this->socialAccount->id]],
        'days' => ['2026-07-01', '2026-07-02'],
        'times' => ['09:00', '18:00'],
        'timezone' => 'UTC',
        'prompt' => 'Sample brief',
        'status' => BulkStatus::Pending,
        'total_posts' => 3,
        'created_posts' => 0,
    ]);

    (new ProcessBulkScheduleJob($bulkSchedule->id))->handle();

    $bulkSchedule->refresh();

    expect($bulkSchedule->status)->toBe(BulkStatus::Completed);
    expect($bulkSchedule->created_posts)->toBe(3);

    $posts = Post::where('workspace_id', $this->workspace->id)
        ->orderBy('scheduled_at')
        ->get();

    expect($posts)->toHaveCount(3);
    expect($posts->pluck('status')->unique()->all())->toBe([PostStatus::Scheduled]);

    expect($posts[0]->scheduled_at->toDateTimeString())->toBe('2026-07-01 09:00:00');
    expect($posts[1]->scheduled_at->toDateTimeString())->toBe('2026-07-01 18:00:00');
    expect($posts[2]->scheduled_at->toDateTimeString())->toBe('2026-07-02 09:00:00');

    Event::assertDispatched(BulkScheduleProgress::class);
});

test('job discards trailing slots without media', function () {
    Event::fake([BulkScheduleProgress::class]);

    $media = makeBulkMedia(2, $this->workspace);

    $bulkSchedule = BulkSchedule::create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'media_ids' => $media->pluck('id')->all(),
        'platforms' => [['social_account_id' => $this->socialAccount->id]],
        'days' => ['2026-07-01'],
        'times' => ['09:00', '13:00', '18:00'],
        'timezone' => 'UTC',
        'prompt' => 'Brief',
        'status' => BulkStatus::Pending,
        'total_posts' => 2,
        'created_posts' => 0,
    ]);

    (new ProcessBulkScheduleJob($bulkSchedule->id))->handle();

    $posts = Post::where('workspace_id', $this->workspace->id)->get();
    expect($posts)->toHaveCount(2);

    $bulkSchedule->refresh();
    expect($bulkSchedule->created_posts)->toBe(2);
    expect($bulkSchedule->status)->toBe(BulkStatus::Completed);
});

test('job converts timezone to utc when scheduling', function () {
    Event::fake([BulkScheduleProgress::class]);

    $media = makeBulkMedia(1, $this->workspace);

    $bulkSchedule = BulkSchedule::create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'media_ids' => $media->pluck('id')->all(),
        'platforms' => [['social_account_id' => $this->socialAccount->id]],
        'days' => ['2026-07-01'],
        'times' => ['09:00'],
        'timezone' => 'America/Sao_Paulo',
        'prompt' => 'Brief',
        'status' => BulkStatus::Pending,
        'total_posts' => 1,
        'created_posts' => 0,
    ]);

    (new ProcessBulkScheduleJob($bulkSchedule->id))->handle();

    // São Paulo is UTC-3, so 09:00 local = 12:00 UTC.
    $post = Post::where('workspace_id', $this->workspace->id)->first();
    expect($post->scheduled_at->toDateTimeString())->toBe('2026-07-01 12:00:00');
});

test('job completes with zero posts when no media is provided', function () {
    Event::fake([BulkScheduleProgress::class]);

    $bulkSchedule = BulkSchedule::create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->user->id,
        'media_ids' => [],
        'platforms' => [['social_account_id' => $this->socialAccount->id]],
        'days' => ['2026-07-01'],
        'times' => ['09:00'],
        'timezone' => 'UTC',
        'prompt' => 'Brief',
        'status' => BulkStatus::Pending,
        'total_posts' => 0,
        'created_posts' => 0,
    ]);

    (new ProcessBulkScheduleJob($bulkSchedule->id))->handle();

    expect($bulkSchedule->fresh()->status)->toBe(BulkStatus::Completed);
    expect(Post::where('workspace_id', $this->workspace->id)->count())->toBe(0);
});
