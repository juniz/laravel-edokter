<?php

namespace App\Http\Livewire\Radiologi;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PhotoRadiologi extends Component
{
    public $no_rawat;
    public function mount($no_rawat)
    {
        $this->no_rawat = $no_rawat;
    }

    public function render()
    {
        return view('livewire.radiologi.photo-radiologi', [
            'photoRadiologi' => $this->getPhotoRadiologi()
        ]);
    }

    public function getPhotoRadiologi()
    {
        return DB::table('gambar_radiologi')
            ->where('no_rawat', $this->no_rawat)
            ->get();
    }
}
