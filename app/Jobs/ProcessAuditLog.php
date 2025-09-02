<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessAuditLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 30; // 30 detik timeout
    public $tries = 3; // Retry 3 kali jika gagal
    public $maxExceptions = 3; // Maksimal 3 exception sebelum failed

    protected $queryData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $queryData)
    {
        $this->queryData = $queryData;
        $this->onQueue('audit-logs'); // Set queue name
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Proses insert audit log
            DB::table('audit_sql_logs')->insert([
                'sql'        => $this->queryData['sql'],
                'bindings'   => $this->queryData['bindings'],
                'time_ms'    => $this->queryData['time_ms'],
                'user_id'    => $this->queryData['user_id'],
                'ip'         => $this->queryData['ip'],
                'url'        => $this->queryData['url'],
                'query_hash' => $this->queryData['query_hash'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Audit log processed successfully', [
                'query_hash' => $this->queryData['query_hash'],
                'user_id' => $this->queryData['user_id']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process audit log: ' . $e->getMessage(), [
                'query_data' => $this->queryData,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            // Re-throw exception untuk retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Audit log job failed permanently', [
            'query_data' => $this->queryData,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [5, 10, 30]; // Wait 5s, 10s, 30s between retries
    }
}
