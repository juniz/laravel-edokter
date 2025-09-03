<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Jobs\ProcessAuditLog;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        DB::listen(function (QueryExecuted $q) {
            $username = session()?->get('username');

            // 1) Hanya DML
            $sql = $q->sql;
            if (!preg_match('/^\s*(insert|update|delete)\s/i', $sql)) return;

            // 2) Hindari rekursi
            if ($q->connectionName === 'audit') return;
            if (stripos($sql, 'audit_sql_logs') !== false) return;

            // 3) Hindari query table finger_bpjs
            if (stripos($sql, 'finger_bpjs') !== false) return;

            // 3) Buat unique hash untuk query (mencegah duplikasi)
            $queryHash = md5($sql . json_encode($q->bindings) . $username . time());

            // 4) Check cache untuk mencegah duplikasi dalam satu request
            $cacheKey = "audit_query_{$queryHash}";
            if (Cache::has($cacheKey)) {
                return; // Query sudah diproses
            }

            // 5) Set cache lock dengan TTL pendek
            Cache::put($cacheKey, true, 10); // 10 detik

            // 6) Normalkan & batasi ukuran bindings
            $bindings = array_map(function ($b) {
                if (is_resource($b)) return '[resource]';
                if ($b instanceof \DateTimeInterface) return $b->format(DATE_ATOM);
                $s = is_scalar($b)
                    ? (string) $b
                    : json_encode($b, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
                return Str::limit($s ?? '[non-scalar]', 1000);
            }, $q->bindings);

            // 7) Batasi panjang SQL
            $sqlLimited = Str::limit($sql, 10000);

            // 8) Dispatch job ke queue untuk diproses di background
            try {
                ProcessAuditLog::dispatch([
                    'sql' => $sqlLimited,
                    'bindings' => json_encode($bindings, JSON_UNESCAPED_UNICODE),
                    'time_ms' => (int) $q->time,
                    'user_id' => $username,
                    'ip' => request()?->ip(),
                    'url' => request()?->fullUrl(),
                    'query_hash' => $queryHash,
                ]);

                // Log success untuk monitoring
                Log::info('Audit log job dispatched', [
                    'query_hash' => $queryHash,
                    'user_id' => $username,
                    'queue' => 'audit-logs'
                ]);
            } catch (\Exception $e) {
                // Log error jika dispatch gagal
                Log::error('Failed to dispatch audit log job: ' . $e->getMessage(), [
                    'query_hash' => $queryHash,
                    'error' => $e->getMessage()
                ]);

                // Remove cache lock jika gagal
                Cache::forget($cacheKey);
            }
        });
    }
}
