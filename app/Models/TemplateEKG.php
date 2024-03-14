<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateEKG extends Model
{
    use HasFactory;

    protected $table = 'template_hasil_ekg';

    protected $fillable = ['nama_template', 'template'];
}
