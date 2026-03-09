<?php

namespace App\Http\Livewire\Ranap;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Carbon;

class LapTindakan extends Component
{
    use LivewireAlert;

    public $no_rawat;
    public $kd_dokter;
    public $diagnosa_pra_tindakan;
    public $diagnosa_pasca_tindakan;
    public $tindakan_medik;
    public $uraian;
    public $hasil;
    public $kesimpulan;
    public $data = [];
    public $modeEdit = false;
    public $tanggalEdit;

    protected $listeners = ['hapusLapTindakan' => 'hapus'];

    public function render()
    {
        return view('livewire.ranap.lap-tindakan');
    }

    public function mount($noRawat)
    {
        $this->no_rawat = $noRawat;
        $this->kd_dokter = session()->get('username');
    }

    public function hydrate()
    {
        $this->getData();
    }

    public function getData()
    {
        $this->data = DB::table('laporan_tindakan')
            ->where('no_rawat', $this->no_rawat)
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function confirmHapus($tanggal)
    {
        $this->tanggalEdit = $tanggal;
        $data = DB::table('laporan_tindakan')
            ->where('no_rawat', $this->no_rawat)
            ->where('tanggal', $tanggal)
            ->first();

        if (!$data) {
            $this->alert('warning', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => 'Data tidak ditemukan',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
            return;
        }

        if ($data->kd_dokter != session()->get('username')) {
            $this->alert('warning', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => 'Anda tidak memiliki akses untuk menghapus data ini',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
            return;
        }

        $this->confirm('Apakah anda yakin ingin menghapus data ini?', [
            'onConfirmed' => 'hapusLapTindakan',
            'cancelButtonText' => 'Batal',
        ]);
    }

    public function hapus()
    {
        $noRawat = $this->no_rawat;
        $tanggal = $this->tanggalEdit;

        try {
            $cek = DB::table('laporan_tindakan')
                ->where('no_rawat', $noRawat)
                ->where('tanggal', $tanggal)
                ->first();

            if (!$cek) {
                $this->alert('warning', 'Gagal', [
                    'position' => 'center',
                    'timer' => '',
                    'toast' => false,
                    'text' => 'Data tidak ditemukan',
                    'confirmButtonText' => 'Ok',
                    'showConfirmButton' => true,
                ]);
                return;
            }

            if ($cek->kd_dokter != session()->get('username')) {
                $this->alert('warning', 'Gagal', [
                    'position' => 'center',
                    'timer' => '',
                    'toast' => false,
                    'text' => 'Anda tidak memiliki akses untuk menghapus data ini',
                    'confirmButtonText' => 'Ok',
                    'showConfirmButton' => true,
                ]);
                return;
            }

            DB::table('laporan_tindakan')
                ->where('no_rawat', $noRawat)
                ->where('tanggal', $tanggal)
                ->delete();

            $this->getData();
            $this->alert('success', 'Berhasil hapus laporan tindakan');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => $e->getMessage(),
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
        }
    }

    public function edit($tanggal)
    {
        $data = DB::table('laporan_tindakan')
            ->where('no_rawat', $this->no_rawat)
            ->where('tanggal', $tanggal)
            ->first();

        if (!$data) {
            $this->alert('warning', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => 'Data tidak ditemukan',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
            return;
        }

        if ($data->kd_dokter != session()->get('username')) {
            $this->alert('warning', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => 'Anda tidak memiliki akses untuk mengubah data ini',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
            return;
        }

        $this->tanggalEdit = $data->tanggal;
        $this->diagnosa_pra_tindakan = $data->diagnosa_pra_tindakan;
        $this->diagnosa_pasca_tindakan = $data->diagnosa_pasca_tindakan;
        $this->tindakan_medik = $data->tindakan_medik;
        $this->uraian = $data->uraian;
        $this->hasil = $data->hasil;
        $this->kesimpulan = $data->kesimpulan;

        $this->modeEdit = true;
    }

    public function resetInput()
    {
        $this->reset([
            'diagnosa_pra_tindakan',
            'diagnosa_pasca_tindakan',
            'tindakan_medik',
            'uraian',
            'hasil',
            'kesimpulan',
        ]);
        $this->modeEdit = false;
        $this->tanggalEdit = null;
    }

    public function simpan()
    {
        $this->validate([
            'diagnosa_pra_tindakan' => 'required|max:50',
            'diagnosa_pasca_tindakan' => 'required|max:50',
            'tindakan_medik' => 'required|max:300',
            'uraian' => 'required|max:3000',
            'hasil' => 'required|max:1000',
            'kesimpulan' => 'required|max:500',
        ], [
            'diagnosa_pra_tindakan.required' => 'Diagnosa Pra Tindakan tidak boleh kosong!',
            'diagnosa_pra_tindakan.max' => 'Diagnosa Pra Tindakan maksimal 50 karakter!',
            'diagnosa_pasca_tindakan.required' => 'Diagnosa Pasca Tindakan tidak boleh kosong!',
            'diagnosa_pasca_tindakan.max' => 'Diagnosa Pasca Tindakan maksimal 50 karakter!',
            'tindakan_medik.required' => 'Tindakan Medik tidak boleh kosong!',
            'tindakan_medik.max' => 'Tindakan Medik maksimal 300 karakter!',
            'uraian.required' => 'Uraian tidak boleh kosong!',
            'uraian.max' => 'Uraian maksimal 3000 karakter!',
            'hasil.required' => 'Hasil tidak boleh kosong!',
            'hasil.max' => 'Hasil maksimal 1000 karakter!',
            'kesimpulan.required' => 'Kesimpulan tidak boleh kosong!',
            'kesimpulan.max' => 'Kesimpulan maksimal 500 karakter!',
        ]);

        $data = [
            'no_rawat' => $this->no_rawat,
            'tanggal' => Carbon::now()->format('Y-m-d H:i:s'),
            'kd_dokter' => $this->kd_dokter,
            'nip' => null,
            'diagnosa_pra_tindakan' => $this->diagnosa_pra_tindakan,
            'diagnosa_pasca_tindakan' => $this->diagnosa_pasca_tindakan,
            'tindakan_medik' => $this->tindakan_medik,
            'uraian' => $this->uraian,
            'hasil' => $this->hasil,
            'kesimpulan' => $this->kesimpulan,
        ];

        try {
            if ($this->modeEdit) {
                DB::table('laporan_tindakan')
                    ->where('no_rawat', $this->no_rawat)
                    ->where('tanggal', $this->tanggalEdit)
                    ->where('kd_dokter', $this->kd_dokter)
                    ->update([
                        'diagnosa_pra_tindakan' => $data['diagnosa_pra_tindakan'],
                        'diagnosa_pasca_tindakan' => $data['diagnosa_pasca_tindakan'],
                        'tindakan_medik' => $data['tindakan_medik'],
                        'uraian' => $data['uraian'],
                        'hasil' => $data['hasil'],
                        'kesimpulan' => $data['kesimpulan'],
                    ]);

                $this->resetInput();
                $this->getData();
                $this->alert('success', 'Berhasil ubah laporan tindakan');
            } else {
                DB::table('laporan_tindakan')->insert($data);

                $this->resetInput();
                $this->getData();
                $this->alert('success', 'Berhasil input laporan tindakan');
            }
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => $e->getMessage(),
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
        }
    }
}
