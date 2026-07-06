<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\BulkSchedule\CreateBulkSchedule;
use App\Http\Requests\App\BulkSchedule\StartBulkScheduleRequest;
use App\Jobs\ProcessBulkScheduleJob;
use App\Models\BulkSchedule;
use App\Models\SocialAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class BulkScheduleController extends Controller
{
    public function start(StartBulkScheduleRequest $request): JsonResponse
    {
        $workspace = $request->user()->currentWorkspace;

        $this->authorize('createPost', $workspace);

        $gate = Gate::inspect('useBulkSchedule', $workspace->account);
        if ($gate->denied()) {
            return response()->json(['message' => $gate->message()], Response::HTTP_PAYMENT_REQUIRED);
        }

        $validated = $request->validated();

        // Defense-in-depth: ensure every social account belongs to this workspace.
        $accountIds = array_values(array_filter(array_map(
            fn ($p) => data_get($p, 'social_account_id'),
            data_get($validated, 'platforms', []),
        )));

        $ownedCount = SocialAccount::whereIn('id', $accountIds)
            ->where('workspace_id', $workspace->id)
            ->count();

        if ($ownedCount !== count($accountIds)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $bulkSchedule = CreateBulkSchedule::execute($workspace, $request->user(), $validated);

        ProcessBulkScheduleJob::dispatch($bulkSchedule->id);

        return response()->json([
            'bulk_schedule_id' => $bulkSchedule->id,
            'channel' => "user.{$request->user()->id}.bulk-schedule.{$bulkSchedule->id}",
        ], Response::HTTP_ACCEPTED);
    }

    public function loading(Request $request, BulkSchedule $bulkSchedule): InertiaResponse
    {
        $this->authorize('view', $bulkSchedule->workspace);

        return Inertia::render('posts/bulk/Loading', [
            'bulkScheduleId' => $bulkSchedule->id,
            'channel' => "user.{$request->user()->id}.bulk-schedule.{$bulkSchedule->id}",
            'totalPosts' => (int) $bulkSchedule->total_posts,
            'createdPosts' => (int) $bulkSchedule->created_posts,
            'status' => $bulkSchedule->status->value,
        ]);
    }
}
