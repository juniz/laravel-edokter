<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanKonsultasiMedik extends Model
{
    use HasFactory;

    protected $table = 'jawaban_konsultasi_medik';
    protected $primaryKey = 'no_permintaan';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_permintaan',
        'tanggal',
        'diagnosa_kerja',
        'uraian_jawaban',
    ];
}
