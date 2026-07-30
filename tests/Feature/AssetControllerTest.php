<?php

declare(strict_types=1);

use App\Enums\UserWorkspace\Role;
use App\Models\Account;
use App\Models\Media;
use App\Models\User;
use App\Models\Workspace;
use App\Services\UnsplashService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();

    $this->account = Account::factory()->create();
    $this->user = User::factory()->create([
        'account_id' => $this->account->id,
    ]);
    $this->account->update(['owner_id' => $this->user->id]);
    $this->workspace = Workspace::factory()->create([
        'account_id' => $this->account->id,
        'user_id' => $this->user->id,
    ]);
    $this->workspace->members()->attach($this->user->id, ['role' => Role::Member->value]);
    $this->user->update(['current_workspace_id' => $this->workspace->id]);

    $this->account->subscriptions()->create([
        'type' => Account::SUBSCRIPTION_NAME,
        'stripe_id' => 'sub_test_'.fake()->uuid(),
        'stripe_status' => 'active',
        'stripe_price' => 'price_123',
    ]);
});

test('assets index shows assets page', function () {
    $response = $this->actingAs($this->user)->get(route('app.assets.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('assets/Index', false));
});

test('assets index requires authentication', function () {
    $response = $this->get(route('app.assets.index'));

    $response->assertRedirect(route('login'));
});

test('assets search returns paginated json filtered by name', function () {
    $matching = $this->workspace->addMedia(UploadedFile::fake()->image('vacation-beach.jpg'), 'assets');
    $this->workspace->addMedia(UploadedFile::fake()->image('office-shot.jpg'), 'assets');

    $response = $this->actingAs($this->user)
        ->getJson(route('app.assets.search', ['search' => 'vacation']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.id', $matching->id);
});

test('assets search filters by type', function () {
    $this->workspace->addMedia(UploadedFile::fake()->image('photo.jpg'), 'assets');
    $this->workspace->addMedia(UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'), 'assets');

    $response = $this->actingAs($this->user)
        ->getJson(route('app.assets.search', ['type' => 'video']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.type', 'video');
});

test('assets search only returns the current workspace assets', function () {
    $this->workspace->addMedia(UploadedFile::fake()->image('mine.jpg'), 'assets');

    $otherAccount = Account::factory()->create();
    $otherUser = User::factory()->create(['account_id' => $otherAccount->id]);
    $otherWorkspace = Workspace::factory()->create([
        'account_id' => $otherAccount->id,
        'user_id' => $otherUser->id,
    ]);
    $otherWorkspace->addMedia(UploadedFile::fake()->image('theirs.jpg'), 'assets');

    $response = $this->actingAs($this->user)
        ->getJson(route('app.assets.search'));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

test('can upload an image asset', function () {
    $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

    $response = $this->actingAs($this->user)
        ->postJson(route('app.assets.store'), ['media' => $file]);

    $response->assertCreated();
    $response->assertJsonStructure(['id', 'url', 'type', 'original_filename', 'size']);

    expect($this->workspace->getMedia('assets')->count())->toBe(1);

    $media = $this->workspace->getMedia('assets')->first();
    expect($media->original_filename)->toBe('photo.jpg');
    expect($media->collection)->toBe('assets');
});

test('can delete an asset', function () {
    $file = UploadedFile::fake()->image('photo.jpg');
    $media = $this->workspace->addMedia($file, 'assets');

    $response = $this->actingAs($this->user)
        ->delete(route('app.assets.destroy', $media));

    $response->assertRedirect();
    expect(Media::find($media->id))->toBeNull();
});

test('cannot delete asset from another workspace', function () {
    $otherWorkspace = Workspace::factory()->create([
        'account_id' => $this->account->id,
        'user_id' => $this->user->id,
    ]);

    $file = UploadedFile::fake()->image('photo.jpg');
    $media = $otherWorkspace->addMedia($file, 'assets');

    $response = $this->actingAs($this->user)
        ->delete(route('app.assets.destroy', $media));

    $response->assertForbidden();
});

test('can bulk delete assets', function () {
    $first = $this->workspace->addMedia(UploadedFile::fake()->image('one.jpg'), 'assets');
    $second = $this->workspace->addMedia(UploadedFile::fake()->image('two.jpg'), 'assets');
    $kept = $this->workspace->addMedia(UploadedFile::fake()->image('three.jpg'), 'assets');

    $response = $this->actingAs($this->user)
        ->delete(route('app.assets.bulk-destroy'), [
            'ids' => [$first->id, $second->id],
        ]);

    $response->assertRedirect();

    expect(Media::find($first->id))->toBeNull();
    expect(Media::find($second->id))->toBeNull();
    expect(Media::find($kept->id))->not->toBeNull();

    Storage::assertMissing($first->path);
    Storage::assertMissing($second->path);
    Storage::assertExists($kept->path);
});

test('bulk delete ignores assets from another workspace', function () {
    $otherWorkspace = Workspace::factory()->create([
        'account_id' => $this->account->id,
        'user_id' => $this->user->id,
    ]);

    $mine = $this->workspace->addMedia(UploadedFile::fake()->image('mine.jpg'), 'assets');
    $theirs = $otherWorkspace->addMedia(UploadedFile::fake()->image('theirs.jpg'), 'assets');

    $response = $this->actingAs($this->user)
        ->delete(route('app.assets.bulk-destroy'), [
            'ids' => [$mine->id, $theirs->id],
        ]);

    $response->assertRedirect();

    expect(Media::find($mine->id))->toBeNull();
    expect(Media::find($theirs->id))->not->toBeNull();
    Storage::assertExists($theirs->path);
});

test('bulk delete requires at least one id', function () {
    $response = $this->actingAs($this->user)
        ->deleteJson(route('app.assets.bulk-destroy'), ['ids' => []]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('ids');
});

test('can store asset from url', function () {
    $fakeImage = UploadedFile::fake()->image('photo.jpg', 800, 600);
    $imageContent = file_get_contents($fakeImage->getPathname());

    Http::fake([
        'images.unsplash.com/*' => Http::response(
            $imageContent,
            200,
            ['Content-Type' => 'image/jpeg']
        ),
    ]);

    $unsplash = $this->mock(UnsplashService::class);
    $unsplash->shouldReceive('trackDownload')->once();

    $response = $this->actingAs($this->user)
        ->postJson(route('app.assets.store-from-url'), [
            'url' => 'https://images.unsplash.com/photo-test',
            'filename' => 'unsplash-test.jpg',
            'download_location' => 'https://api.unsplash.com/photos/test/download',
        ]);

    $response->assertCreated();
    $response->assertJsonStructure(['id', 'path', 'url', 'type', 'mime_type', 'original_filename', 'size']);

    expect($this->workspace->getMedia('assets')->count())->toBe(1);

    $media = $this->workspace->getMedia('assets')->first();
    $response->assertJsonPath('id', $media->id);
    $response->assertJsonPath('type', 'image');
});

test('chunked upload completes with single chunk', function () {
    // Use real PNG bytes so mime_content_type detects image/png. The MIME
    // is sniffed from content magic bytes, not the X-File-Name header.
    $content = file_get_contents(__DIR__.'/../fixtures/1x1.png');
    $size = strlen($content);

    $response = $this->actingAs($this->user)->call(
        'POST',
        route('app.assets.store-chunked'),
        [], [], [],
        [
            'HTTP_CONTENT_RANGE' => 'bytes 0-'.($size - 1).'/'.$size,
            'HTTP_X_FILE_NAME' => 'test.png',
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/octet-stream',
        ],
        $content,
    );

    $response->assertSuccessful();
    $response->assertJson(['done' => true]);
    $response->assertJsonStructure(['done', 'id', 'path', 'url', 'type', 'mime_type', 'original_filename', 'size']);
    expect($this->workspace->getMedia('assets')->count())->toBe(1);
});

test('chunked upload reports progress on intermediate chunks', function () {
    $response = $this->actingAs($this->user)->call(
        'POST',
        route('app.assets.store-chunked'),
        [], [], [],
        [
            'HTTP_CONTENT_RANGE' => 'bytes 0-499/1000',
            'HTTP_X_FILE_NAME' => 'test-video.mp4',
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/octet-stream',
        ],
        str_repeat('a', 500),
    );

    $response->assertSuccessful();
    $response->assertJson(['done' => false, 'progress' => 50]);
    expect(Media::count())->toBe(0);
});

test('chunked video upload finalizes without loading the whole file into memory', function () {
    // Synthetic MP4: the `ftyp` box makes mime_content_type report video/mp4,
    // so the upload takes the video branch instead of image normalization.
    $mp4Header = "\x00\x00\x00\x20ftypisom\x00\x00\x02\x00isomiso2avc1mp41";
    $chunkSize = 5 * 1024 * 1024;
    $totalSize = 12 * 1024 * 1024;

    $sendChunk = function (int $start, int $end) use ($mp4Header, $totalSize) {
        $length = $end - $start + 1;
        $body = $start === 0
            ? $mp4Header.str_repeat("\x00", $length - strlen($mp4Header))
            : str_repeat("\x00", $length);

        return $this->actingAs($this->user)->call(
            'POST',
            route('app.assets.store-chunked'),
            [], [], [],
            [
                'HTTP_CONTENT_RANGE' => "bytes {$start}-{$end}/{$totalSize}",
                'HTTP_X_FILE_NAME' => 'big-video.mp4',
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/octet-stream',
            ],
            $body,
        );
    };

    for ($start = 0; $start + $chunkSize < $totalSize; $start += $chunkSize) {
        $sendChunk($start, $start + $chunkSize - 1)->assertJson(['done' => false]);
    }

    // Measure only the finalizing request — that is where the assembled file is
    // read off disk and handed to the storage disk.
    gc_collect_cycles();
    memory_reset_peak_usage();
    $baseline = memory_get_usage();

    $response = $sendChunk($start, $totalSize - 1);

    $peakGrowth = memory_get_peak_usage() - $baseline;

    $response->assertSuccessful();
    $response->assertJson(['done' => true, 'type' => 'video']);
    expect($response->json('size'))->toBe($totalSize);

    // Streaming keeps this near the size of the final chunk. Reading the whole
    // file into a string pushes it past the full size, which is what exhausts
    // memory_limit for the ~1GB videos the app accepts in production.
    expect($peakGrowth)->toBeLessThan($totalSize);
});

test('chunked upload rejects unsupported file extension', function () {
    $response = $this->actingAs($this->user)->call(
        'POST',
        route('app.assets.store-chunked'),
        [], [], [],
        [
            'HTTP_CONTENT_RANGE' => 'bytes 0-99/100',
            'HTTP_X_FILE_NAME' => 'malware.exe',
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/octet-stream',
        ],
        str_repeat('x', 100),
    );

    $response->assertUnprocessable();
});

test('chunked upload rejects invalid Content-Range header', function () {
    $response = $this->actingAs($this->user)->call(
        'POST',
        route('app.assets.store-chunked'),
        [], [], [],
        [
            'HTTP_CONTENT_RANGE' => 'invalid',
            'HTTP_X_FILE_NAME' => 'test.jpg',
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/octet-stream',
        ],
        'data',
    );

    // FormRequest validation surfaces parse failures as 422
    // (range_start / range_end / total_size all required).
    $response->assertUnprocessable();
});

test('chunked upload rejects unauthenticated', function () {
    $response = $this->call(
        'POST',
        route('app.assets.store-chunked'),
        [], [], [],
        [
            'HTTP_CONTENT_RANGE' => 'bytes 0-99/100',
            'HTTP_X_FILE_NAME' => 'test.jpg',
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/octet-stream',
        ],
        str_repeat('x', 100),
    );

    $response->assertUnauthorized();
});

test('unsplash search returns results', function () {
    $this->mock(UnsplashService::class)
        ->shouldReceive('search')
        ->with('nature', 1)
        ->once()
        ->andReturn([
            'results' => [
                ['id' => 'abc', 'url_small' => 'https://example.com/small.jpg'],
            ],
            'total' => 1,
            'total_pages' => 1,
        ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('app.assets.unsplash.search', ['query' => 'nature']));

    $response->assertOk();
    $response->assertJsonPath('total', 1);
});
