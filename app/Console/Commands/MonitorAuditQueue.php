<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MonitorAuditQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:monitor {--stats : Show queue statistics} {--failed : Show failed jobs} {--clear : Clear failed jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor audit log queue status and performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('stats')) {
            $this->showQueueStats();
        } elseif ($this->option('failed')) {
            $this->showFailedJobs();
        } elseif ($this->option('clear')) {
            $this->clearFailedJobs();
        } else {
            $this->showQueueStatus();
        }
    }

    /**
     * Show general queue status
     */
    protected function showQueueStatus()
    {
        $this->info('🔍 Audit Log Queue Status');
        $this->line('');

        // Queue size
        $pendingJobs = DB::table('jobs')->where('queue', 'audit-logs')->count();
        $failedJobs = DB::table('failed_jobs')->where('queue', 'audit-logs')->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Pending Jobs', $pendingJobs],
                ['Failed Jobs', $failedJobs],
            ]
        );

        // Recent audit logs
        $recentLogs = DB::table('audit_sql_logs')
            ->select('user_id', 'ip', 'time_ms', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($recentLogs->count() > 0) {
            $this->line('');
            $this->info('📊 Recent Audit Logs (Last 5)');

            $logsData = $recentLogs->map(function ($log) {
                return [
                    $log->user_id,
                    $log->ip,
                    $log->time_ms . 'ms',
                    $log->created_at
                ];
            })->toArray();

            $this->table(
                ['User', 'IP', 'Time', 'Created At'],
                $logsData
            );
        }
    }

    /**
     * Show queue statistics
     */
    protected function showQueueStats()
    {
        $this->info('📈 Audit Log Queue Statistics');
        $this->line('');

        // Today's stats
        $today = now()->startOfDay();
        $todayLogs = DB::table('audit_sql_logs')
            ->where('created_at', '>=', $today)
            ->count();

        $todayAvgTime = DB::table('audit_sql_logs')
            ->where('created_at', '>=', $today)
            ->avg('time_ms');

        // This week's stats
        $weekStart = now()->startOfWeek();
        $weekLogs = DB::table('audit_sql_logs')
            ->where('created_at', '>=', $weekStart)
            ->count();

        $weekAvgTime = DB::table('audit_sql_logs')
            ->where('created_at', '>=', $weekStart)
            ->avg('time_ms');

        $this->table(
            ['Period', 'Total Logs', 'Avg Response Time'],
            [
                ['Today', $todayLogs, round($todayAvgTime, 2) . 'ms'],
                ['This Week', $weekLogs, round($weekAvgTime, 2) . 'ms'],
            ]
        );
    }

    /**
     * Show failed jobs
     */
    protected function showFailedJobs()
    {
        $failedJobs = DB::table('failed_jobs')
            ->where('queue', 'audit-logs')
            ->orderBy('failed_at', 'desc')
            ->get();

        if ($failedJobs->count() == 0) {
            $this->info('✅ No failed jobs found in audit-logs queue');
            return;
        }

        $this->error('❌ Failed Jobs in Audit Logs Queue');
        $this->line('');

        $failedData = $failedJobs->map(function ($job) {
            return [
                $job->id,
                $job->queue,
                $job->failed_at,
                Str::limit($job->exception, 100)
            ];
        })->toArray();

        $this->table(
            ['ID', 'Queue', 'Failed At', 'Exception'],
            $failedData
        );
    }

    /**
     * Clear failed jobs
     */
    protected function clearFailedJobs()
    {
        $deleted = DB::table('failed_jobs')
            ->where('queue', 'audit-logs')
            ->delete();

        $this->info("🗑️  Cleared {$deleted} failed jobs from audit-logs queue");
    }
}
