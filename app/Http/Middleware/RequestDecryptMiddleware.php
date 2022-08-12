<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Crypt;
use Closure;
use Illuminate\Http\Request;

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
        if($request->has('no_rawat')) {
            $request->merge(['no_rawat' => $this->decryptData($request->get('no_rawat'))]);
          
        }
        if($request->has('no_rm')){
            $request->merge(['no_rm' => $this->decryptData($request->get('no_rm'))]);
        }
        return $next($request);    
    }

    public function decryptData($data)
    {
        $data = Crypt::decrypt($data);
        return $data;
    }
}
