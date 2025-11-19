<?php

namespace App\Jobs;

use App\Application\Rdash\User\BulkSyncUsersToRdashService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BulkSyncUsersToRdashJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 120; // seconds

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $userIds
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(BulkSyncUsersToRdashService $bulkSyncService): void
    {
        try {
            $result = $bulkSyncService->execute($this->userIds);

            Log::info('Bulk Sync Users to RDASH completed', [
                'total' => count($this->userIds),
                'success' => $result['success'],
                'failed' => $result['failed'],
            ]);

            // Log failed syncs
            foreach ($result['results'] as $item) {
                if (! $item['success']) {
                    Log::warning('Bulk sync failed for user', [
                        'user_id' => $item['user_id'],
                        'message' => $item['message'],
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Bulk Sync Users to RDASH job failed', [
                'user_ids' => $this->userIds,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

