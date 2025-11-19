<?php

namespace App\Jobs;

use App\Application\Rdash\User\SyncUserToRdashService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncUserToRdashJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // seconds

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public bool $createCustomerIfNotExists = true
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(SyncUserToRdashService $syncService): void
    {
        try {
            $result = $syncService->execute($this->user, $this->createCustomerIfNotExists);

            if (! $result['success']) {
                Log::warning('Sync User to RDASH failed in job', [
                    'user_id' => $this->user->id,
                    'message' => $result['message'],
                ]);

                throw new \Exception($result['message']);
            }

            Log::info('Sync User to RDASH successful', [
                'user_id' => $this->user->id,
                'rdash_customer_id' => $result['rdash_customer_id'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Sync User to RDASH job failed', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Sync User to RDASH job permanently failed', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

