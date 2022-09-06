<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Encryption\Encrypter;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('auth.login');
    }

    public function customLogin(Request $request)
    {
        $this->validateLogin($request);
        $cek = DB::table('user')
                    ->join("dokter", "dokter.kd_dokter", "=", DB::Raw("AES_DECRYPT(id_user, 'nur')"))
                    ->join("jadwal", "jadwal.kd_dokter", "=", "dokter.kd_dokter")
                    ->join("poliklinik", "poliklinik.kd_poli", "=", "jadwal.kd_poli")
                    ->whereRaw("id_user = AES_ENCRYPT('{$request->username}', 'nur')")
                    ->whereRaw("password = AES_ENCRYPT('{$request->password}', 'windi')")
                    ->where("poliklinik.nm_poli", "like", 'KLINIK%')
                    ->selectRaw("AES_DECRYPT(id_user, 'nur') as id_user, AES_DECRYPT(password, 'windi') as password, jadwal.kd_poli")
                    ->first();
        if ($cek) {
            session(['username' => $cek->id_user, 'password'=>$cek->password, 'kd_poli'=>$cek->kd_poli]);
            return redirect()->intended('home')
                        ->withSuccess('Signed in');
        }
  
        return redirect("/")->with('message','Login details are not valid');
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
