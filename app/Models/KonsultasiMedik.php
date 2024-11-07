<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KonsultasiMedik extends Model
{
    use HasFactory;

    protected $table = 'konsultasi_medik';
    protected $primaryKey = 'no_permintaan';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_permintaan',
        'no_rawat',
        'tanggal',
        'jenis_permintaan',
        'kd_dokter',
        'kd_dokter_dikonsuli',
        'diagnosa_kerja',
        'uraian_konsultasi',
    ];

    public function jawaban()
    {
        return $this->hasOne(JawabanKonsultasiMedik::class, 'no_permintaan', 'no_permintaan');
    }

    public function dokter()
    {
        return $this->hasOne(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    public function dokterDikonsuli()
    {
        return $this->hasOne(Dokter::class, 'kd_dokter', 'kd_dokter_dikonsuli');
    }

    public function regPeriksa()
    {
        return $this->hasOne(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }
}
