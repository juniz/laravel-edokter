<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use App\Jobs\ProcessAuditLog;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Buffer audit data per connection untuk transaksi (hanya dispatch saat commit).
     * Key: connection name, Value: stack of arrays (untuk nested transactions)
     */
    protected static array $pendingAuditsByConnection = [];

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
        $this->registerTransactionListeners();
        $this->registerQueryListener();
    }

    /**
     * Listener untuk event transaksi: buffer query, hanya log saat commit.
     */
    protected function registerTransactionListeners(): void
    {
        Event::listen(TransactionBeginning::class, function (TransactionBeginning $event) {
            $conn = $event->connection->getName();
            if (!isset(self::$pendingAuditsByConnection[$conn])) {
                self::$pendingAuditsByConnection[$conn] = [];
            }
            self::$pendingAuditsByConnection[$conn][] = [];
        });

        Event::listen(TransactionCommitted::class, function (TransactionCommitted $event) {
            $conn = $event->connection->getName();
            if (empty(self::$pendingAuditsByConnection[$conn])) {
                return;
            }
            $popped = array_pop(self::$pendingAuditsByConnection[$conn]);
            if (!empty(self::$pendingAuditsByConnection[$conn])) {
                $parentIdx = count(self::$pendingAuditsByConnection[$conn]) - 1;
                self::$pendingAuditsByConnection[$conn][$parentIdx] = array_merge(
                    self::$pendingAuditsByConnection[$conn][$parentIdx],
                    $popped
                );
            } else {
                foreach ($popped as $auditData) {
                    $this->dispatchAudit($auditData);
                }
                unset(self::$pendingAuditsByConnection[$conn]);
            }
        });

        Event::listen(TransactionRolledBack::class, function (TransactionRolledBack $event) {
            $conn = $event->connection->getName();
            if (!empty(self::$pendingAuditsByConnection[$conn])) {
                array_pop(self::$pendingAuditsByConnection[$conn]);
                if (empty(self::$pendingAuditsByConnection[$conn])) {
                    unset(self::$pendingAuditsByConnection[$conn]);
                }
            }
        });
    }

    /**
     * Listener untuk QueryExecuted: buffer jika dalam transaksi, else dispatch.
     */
    protected function registerQueryListener(): void
    {
        DB::listen(function (QueryExecuted $q) {
            $username = session()?->get('username');

            if (!preg_match('/^\s*(insert|update|delete)\s/i', $q->sql)) return;
            if ($q->connectionName === 'audit') return;
            if (stripos($q->sql, 'audit_sql_logs') !== false) return;
            if (stripos($q->sql, 'finger_bpjs') !== false) return;

            $auditData = $this->buildAuditData($q, $username);

            $conn = $q->connectionName ?? $q->connection->getName();
            if (!empty(self::$pendingAuditsByConnection[$conn])) {
                $topIdx = count(self::$pendingAuditsByConnection[$conn]) - 1;
                self::$pendingAuditsByConnection[$conn][$topIdx][] = $auditData;
            } else {
                if (!$this->isDuplicate($auditData)) {
                    $this->dispatchAudit($auditData);
                }
            }
        });
    }

    protected function buildAuditData(QueryExecuted $q, ?string $username): array
    {
        $bindings = array_map(function ($b) {
            if (is_resource($b)) return '[resource]';
            if ($b instanceof \DateTimeInterface) return $b->format(DATE_ATOM);
            $s = is_scalar($b)
                ? (string) $b
                : json_encode($b, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            return Str::limit($s ?? '[non-scalar]', 1000);
        }, $q->bindings);

        $requestId = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        $queryHash = md5($q->sql . json_encode($q->bindings) . $username . $requestId);

        return [
            'sql' => Str::limit($q->sql, 10000),
            'bindings' => json_encode($bindings, JSON_UNESCAPED_UNICODE),
            'time_ms' => (int) $q->time,
            'user_id' => $username,
            'ip' => request()?->ip(),
            'url' => request()?->fullUrl(),
            'query_hash' => $queryHash,
        ];
    }

    protected function isDuplicate(array $auditData): bool
    {
        // Tip: Gunakan CACHE_DRIVER=redis di .env untuk performa lebih baik
        $cacheKey = "audit_query_{$auditData['query_hash']}";
        if (Cache::has($cacheKey)) {
            return true;
        }
        Cache::put($cacheKey, true, 10);
        return false;
    }

    protected function dispatchAudit(array $auditData): void
    {
        try {
            ProcessAuditLog::dispatch($auditData);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch audit log job: ' . $e->getMessage(), [
                'query_hash' => $auditData['query_hash'] ?? null,
                'error' => $e->getMessage()
            ]);
            Cache::forget("audit_query_{$auditData['query_hash']}");
        }
    }
}
