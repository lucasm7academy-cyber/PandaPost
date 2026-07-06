<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\BulkSchedule;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BulkScheduleProgress implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $bulkScheduleId;

    public string $userId;

    public int $createdPosts;

    public int $totalPosts;

    public string $status;

    public ?string $errorMessage;

    public function __construct(BulkSchedule $bulkSchedule)
    {
        $this->bulkScheduleId = $bulkSchedule->id;
        $this->userId = (string) $bulkSchedule->user_id;
        $this->createdPosts = (int) $bulkSchedule->created_posts;
        $this->totalPosts = (int) $bulkSchedule->total_posts;
        $this->status = $bulkSchedule->status->value;
        $this->errorMessage = $bulkSchedule->error_message;
    }

    public function broadcastAs(): string
    {
        return 'bulk-schedule.progress';
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.{$this->userId}.bulk-schedule.{$this->bulkScheduleId}");
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'bulk_schedule_id' => $this->bulkScheduleId,
            'created_posts' => $this->createdPosts,
            'total_posts' => $this->totalPosts,
            'status' => $this->status,
            'error_message' => $this->errorMessage,
        ];
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }
}
