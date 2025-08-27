<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


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
            DB::listen(function (QueryExecuted $q) {
                // 1) Hanya DML
                $sql = $q->sql;
                if (!preg_match('/^\s*(insert|update|delete)\s/i', $sql)) return;

                // 2) Hindari rekursi:
                // - lewati event dari koneksi 'audit'
                // - lewati query yang menyentuh tabel log
                if ($q->connectionName === 'audit') return;
                if (stripos($sql, 'audit_sql_logs') !== false) return;

                // 3) Normalkan & batasi ukuran bindings (hindari BLOB besar)
                $bindings = array_map(function ($b) {
                    if (is_resource($b)) return '[resource]';
                    if ($b instanceof \DateTimeInterface) return $b->format(DATE_ATOM);
                    $s = is_scalar($b)
                        ? (string) $b
                        : json_encode($b, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
                    return Str::limit($s ?? '[non-scalar]', 1000); // limit 1KB per binding
                }, $q->bindings);

                // 4) Batasi panjang SQL yang disimpan
                $sqlLimited = Str::limit($sql, 10000); // 10KB

                // 5) Reentrancy guard tambahan (satu request)
                static $busy = false;
                if ($busy) return;

                $busy = true;
                try {
                    DB::table('audit_sql_logs')->insert([
                        'sql'        => $sqlLimited,
                        'bindings'   => json_encode($bindings, JSON_UNESCAPED_UNICODE),
                        'time_ms'    => (int) $q->time,
                        'user_id'    => session()?->get('username'),
                        'ip'         => request()?->ip(),
                        'url'        => request()?->fullUrl(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } finally {
                    $busy = false;
                }
            });
        });
    }
}
