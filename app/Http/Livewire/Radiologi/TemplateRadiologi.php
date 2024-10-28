<?php

namespace App\Http\Livewire\Radiologi;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class TemplateRadiologi extends Component
{
    public $search, $datas;
    public $readyToLoad = false;

    public function mount()
    {
        $this->search = '';
        $this->datas = DB::table('template_hasil_radiologi')
            ->limit(10)
            ->get();
    }

    public function loadDatas()
    {
        $this->readyToLoad = true;
    }

    public function render()
    {
        return view('livewire.radiologi.template-radiologi');
    }

    public function updatedSearch()
    {
        $this->datas = DB::table('template_hasil_radiologi')
            ->where('nama_pemeriksaan', 'like', '%' . $this->search . '%')
            ->limit(10)
            ->get();
    }
}
