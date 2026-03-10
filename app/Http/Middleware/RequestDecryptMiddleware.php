<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class RequestDecryptMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('no_rawat')) {
            $request->merge(['no_rawat' => $this->safeDecrypt($request->get('no_rawat'))]);
        }
        if ($request->has('no_rm')) {
            $request->merge(['no_rm' => $this->safeDecrypt($request->get('no_rm'))]);
        }
        return $next($request);
    }

    /**
     * Decrypt data. Jika gagal (payload invalid/corrupt), kembalikan nilai asli.
     * Menangani kasus: refresh halaman, URL termodifikasi Livewire, atau data plain text.
     */
    protected function safeDecrypt(?string $data): ?string
    {
        if ($data === null || $data === '') {
            return $data;
        }
        try {
            return Crypt::decrypt($data);
        } catch (DecryptException $e) {
            return $data;
        }
    }
}
