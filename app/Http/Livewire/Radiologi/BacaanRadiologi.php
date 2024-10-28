<?php

namespace App\Http\Livewire\Radiologi;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class BacaanRadiologi extends Component
{
    use LivewireAlert;
    public $no_rawat;
    public $bacaanRadiologi;
    protected $listeners = ['pilihTemplateRadiologi' => 'pilihTemplateRadiologi'];

    public function mount($no_rawat)
    {
        $this->no_rawat = $no_rawat;
        $this->bacaanRadiologi = $this->getBacaanRadiologi()->hasil ?? '';
    }

    public function render()
    {
        return view('livewire.radiologi.bacaan-radiologi');
    }

    public function getBacaanRadiologi()
    {
        return DB::table('hasil_radiologi')
            ->where('no_rawat', $this->no_rawat)
            ->first();
    }

    public function pilihTemplateRadiologi($id)
    {
        $data = DB::table('template_hasil_radiologi')
            ->where('no_template', $id)
            ->first();

        $this->bacaanRadiologi = $data->template_hasil_radiologi;

        $this->dispatchBrowserEvent('closeModalTemplateRadiologi');
    }

    public function simpan()
    {
        $this->validate([
            'bacaanRadiologi' => 'required'
        ], [
            'bacaanRadiologi.required' => 'Bacaan radiologi tidak boleh kosong'
        ]);

        try {
            $cek = DB::table('hasil_radiologi')
                ->where('no_rawat', $this->no_rawat)
                ->first();
            if ($cek) {
                DB::table('hasil_radiologi')
                    ->where('no_rawat', $this->no_rawat)
                    ->update([
                        'hasil' => $this->bacaanRadiologi,
                        'tgl_periksa' => date('Y-m-d'),
                        'jam' => date('H:i:s')
                    ]);
            } else {
                DB::table('hasil_radiologi')
                    ->insert([
                        'no_rawat' => $this->no_rawat,
                        'hasil' => $this->bacaanRadiologi,
                        'tgl_periksa' => date('Y-m-d'),
                        'jam' => date('H:i:s')
                    ]);
            }
            $this->alert('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal simpan ; ' . $e->getMessage());
        }
    }
}
