<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $config = [
            "title" => "Pilih Poliklinik",
            "liveSearch" => true,
            "liveSearchPlaceholder" => "Cari...",
            "showTick" => true,
            "actionsBox" => true,
        ];
        $poli = DB::table('poliklinik')->where('status', '1')->get();
        return view('auth.login', ['poli' => $poli, 'config' => $config]);
    }

    public function customLogin(Request $request)
    {
        $this->validateLogin($request);

        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'message' => "Gagal login: Terlalu banyak percobaan. Akun terkunci sementara. Coba lagi dalam {$seconds} detik."
            ])->withInput($request->only('username', 'poli'));
        }

        $cek = DB::table('user')
            ->join("dokter", "dokter.kd_dokter", "=", DB::raw("AES_DECRYPT(id_user, 'nur')"))
            ->whereRaw("id_user = AES_ENCRYPT(?, 'nur')", [$request->username])
            ->selectRaw("AES_DECRYPT(id_user, 'nur') as id_user, AES_DECRYPT(password, 'windi') as password, dokter.kd_sps")
            ->first();

        if ($cek && $cek->password == $request->password) {
            RateLimiter::clear($throttleKey);

            session(['username' => $cek->id_user, 'password' => $cek->password, 'kd_poli' => $request->poli, 'kd_sps' => $cek->kd_sps]);
            return redirect()->intended('home')
                ->withSuccess('Signed in');
        }

        RateLimiter::hit($throttleKey, 300);

        $attemptsLeft = RateLimiter::remaining($throttleKey, 3);
        $message = $attemptsLeft > 0
            ? "Gagal login: Username atau password salah. Sisa percobaan: {$attemptsLeft} kali."
            : "Gagal login: Terlalu banyak percobaan. Akun terkunci sementara selama 5 menit.";

        return back()->withErrors(['message' => $message])->withInput($request->only('username', 'poli'));
    }

    public function throttleKey(Request $request)
    {
        return Str::transliterate(Str::lower($request->input($this->username())) . '|' . $request->ip());
    }


    public function username()
    {
        return 'username';
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string',
            'password' => 'required|string',
            'poli' => 'required',
        ], [
            'username.required' => 'NIP Dokter tidak boleh kosong',
            'password.required' => 'Password tidak boleh kosong',
            'poli.required' => 'Poli tidak boleh kosong',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
