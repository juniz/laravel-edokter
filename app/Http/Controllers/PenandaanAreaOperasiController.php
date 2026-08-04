<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PenandaanAreaOperasiController extends Controller
{
    public function cetak(Request $request)
    {
        $validated = $request->validate([
            'no_rawat' => ['required', 'string'],
        ]);

        $marking = (new class extends Model {
            protected $table = 'penandaan_area_operasi';
            public $timestamps = false;
        })->newQuery()
            ->where('no_rawat', $validated['no_rawat'])
            ->firstOrFail();

        $images = json_decode(
            $marking->gambar_penandaan_base64,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return view('prints.penandaan-area-operasi', compact('marking', 'images'));
    }
}
