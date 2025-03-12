<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FingerBpjs extends Model
{
    use HasFactory;
    protected $table = 'finger_bpjs';
    protected $primaryKey = 'no_rawat';
    protected $fillable = ['no_rawat', 'no_kartu', 'tanggal', 'kode', 'status'];
    public $timestamps = true;
}
