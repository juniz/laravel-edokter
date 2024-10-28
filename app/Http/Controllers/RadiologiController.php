<?php

namespace App\Http\Controllers;

use App\Traits\EnkripsiData;
use Illuminate\Http\Request;

class RadiologiController extends Controller
{
    use EnkripsiData;
    public function ralan()
    {
        return view('radiologi.ralan');
    }

    public function ranap()
    {
        return view('radiologi.ranap');
    }

    public function pemeriksaan($no_rawat)
    {
        $no_rawat = $this->decryptData($no_rawat);
        return view('radiologi.pemeriksaan', compact('no_rawat'));
    }
}
