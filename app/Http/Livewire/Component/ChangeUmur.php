<?php

namespace App\Http\Livewire\Component;

use Illuminate\Support\Facades\App;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Carbon\Carbon;

class ChangeUmur extends Component
{
    use LivewireAlert;
    public $tahun, $bulan, $hari, $noRm, $tgl_lahir;
    protected $listeners = ['setRmUmur' => 'setUmur'];

    public function render()
    {
        return view('livewire.component.change-umur');
    }

    public function setUmur($noRm, $umur, $tglLahir)
    {
        $this->noRm = $noRm;
        $this->tgl_lahir = $tglLahir;
        
        // Selalu hitung umur yang benar dari tanggal lahir untuk memastikan akurasi
        if ($tglLahir) {
            try {
                $birthDate = Carbon::parse($tglLahir);
                $now = Carbon::now();
                
                // Hitung umur dengan akurat menggunakan diff
                $diff = $now->diff($birthDate);
                
                $this->tahun = $diff->y;
                $this->bulan = $diff->m;
                $this->hari = $diff->d;
                
            } catch (\Exception $e) {
                // Jika parsing gagal, coba parse dari umur yang ada
                if ($umur) {
                    preg_match('/(\d+)\s*Th.*?(\d+)?\s*Bl.*?(\d+)?\s*Hr/i', $umur, $matches);
                    $this->tahun = isset($matches[1]) ? (int)$matches[1] : 0;
                    $this->bulan = isset($matches[2]) ? (int)$matches[2] : 0;
                    $this->hari = isset($matches[3]) ? (int)$matches[3] : 0;
                } else {
                    $this->tahun = 0;
                    $this->bulan = 0;
                    $this->hari = 0;
                }
            }
        } else {
            // Jika tidak ada tanggal lahir, parse dari umur yang ada
            if ($umur) {
                preg_match('/(\d+)\s*Th.*?(\d+)?\s*Bl.*?(\d+)?\s*Hr/i', $umur, $matches);
                $this->tahun = isset($matches[1]) ? (int)$matches[1] : 0;
                $this->bulan = isset($matches[2]) ? (int)$matches[2] : 0;
                $this->hari = isset($matches[3]) ? (int)$matches[3] : 0;
            } else {
                $this->tahun = 0;
                $this->bulan = 0;
                $this->hari = 0;
            }
        }
    }

    public function simpan()
    {
        $this->validate([
            'tahun' => 'required|integer|min:0',
            'bulan' => 'required|integer|min:0|max:11',
            'hari' => 'required|integer|min:0|max:30'
        ],[
            'tahun.required' => 'Tahun tidak boleh kosong',
            'tahun.integer' => 'Tahun harus berupa angka',
            'tahun.min' => 'Tahun tidak boleh negatif',
            'bulan.required' => 'Bulan tidak boleh kosong',
            'bulan.integer' => 'Bulan harus berupa angka',
            'bulan.min' => 'Bulan tidak boleh negatif',
            'bulan.max' => 'Bulan maksimal 11',
            'hari.required' => 'Hari tidak boleh kosong',
            'hari.integer' => 'Hari harus berupa angka',
            'hari.min' => 'Hari tidak boleh negatif',
            'hari.max' => 'Hari maksimal 30'
        ]);

        try{
            // Hitung tanggal lahir baru berdasarkan umur yang diinput
            $tanggalLahirBaru = Carbon::now()
                ->subYears($this->tahun)
                ->subMonths($this->bulan)
                ->subDays($this->hari)
                ->format('Y-m-d');

            DB::table('pasien')->where('no_rkm_medis', $this->noRm)->update([
                'tgl_lahir' => $tanggalLahirBaru
            ]);

            $umurFormat = $this->tahun . ' Th ' . $this->bulan . ' Bl ' . $this->hari . ' Hr';
            
            $this->alert('success', 'Umur berhasil diubah');
            $this->dispatchBrowserEvent('refreshUmur', ['umur' => $umurFormat]);
            $this->reset();

        }catch(\Exception $e){

            $this->alert('error', 'Gagal', [
                'position' =>  'center',
                'timer' =>  '',
                'toast' =>  false,
                'text' =>  App::environment('local') ? $e->getMessage() : 'Terjadi Kesalahan saat input data',
                'confirmButtonText' =>  'Tutup',
                'cancelButtonText' =>  'Batalkan',
                'showCancelButton' =>  false,
                'showConfirmButton' =>  true,
            ]);
        }
    }
}

