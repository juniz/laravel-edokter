<?php

namespace App\Http\Livewire\Component;

use Livewire\Component;

class NotifikasiKonsul extends Component
{
    public $kd_dokter;
    public $pesan;
    public function render()
    {
        return view('livewire.component.notifikasi-konsul');
    }

    public function mount($kd_dokter)
    {
        $this->kd_dokter = $kd_dokter;
    }

    public function cekKonsul() {}
}
