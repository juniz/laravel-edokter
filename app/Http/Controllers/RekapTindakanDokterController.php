<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RekapTindakanDokterController extends Controller
{
    public function __construct()
    {
        $this->middleware('loginauth');
    }

    public function index(Request $request)
    {
        return view('rekap.tindakan-dokter');
    }
}

