<?php

namespace App\Http\Livewire\Component;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class KonsultasiMedik extends Component
{
    use LivewireAlert;
    public $noRawat;
    public $tanggal;
    public $jenis_permintaan;
    public $list_jenis_permintaan;
    public $kd_dokter;
    public $kd_dokter_dikonsuli;
    public $diagnosa_kerja;
    public $uraian_konsultasi;
    public $list_data_konsultasi = [];
    public $list_dokter;
    public $no_permintaan;
    public $modeEdit = false;

    public function mount($noRawat)
    {
        $this->noRawat = $noRawat;
        $this->tanggal = date('Y-m-d H:i:s');
        $this->list_jenis_permintaan = [
            'Konsultasi',
            'Evaluasi',
            'Rawat Bersama',
            'Alih Rawat',
            'Pre/Post Operasi',
        ];
        // $this->jenis_permintaan = 'Konsultasi';
        $this->kd_dokter = session()->get('username');
        // $this->getDataListKonsultasi();
        $this->getListDokter();
        // dd($this->generateNoPermintaan());
    }

    public function hydrate()
    {
        $this->getDataListKonsultasi();
        $this->getListDokter();
    }

    public function render()
    {
        return view('livewire.component.konsultasi-medik');
    }

    public function generateNoPermintaan()
    {
        $data = DB::table('konsultasi_medik')
            ->max('no_permintaan');
        $noUrut = (int) substr($data, 10, 4);
        return 'KM' . date('Ymd') . sprintf('%04s', $noUrut + 1);
    }

    public function getDataListKonsultasi()
    {
        $data = DB::table('konsultasi_medik')
            ->join('reg_periksa', 'konsultasi_medik.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('dokter', 'konsultasi_medik.kd_dokter_dikonsuli', '=', 'dokter.kd_dokter')
            ->where('konsultasi_medik.no_rawat', $this->noRawat)
            ->select('konsultasi_medik.*', 'pasien.nm_pasien', 'dokter.nm_dokter')
            ->get();
        $this->list_data_konsultasi = $data;
    }

    public function getListDokter()
    {
        $data = DB::table('dokter')
            ->where('status', '1')
            ->get();
        $this->list_dokter = $data;
    }

    public function confirmHapus($no_permintaan)
    {
        try {

            DB::table('konsultasi_medik')
                ->where('no_permintaan', $no_permintaan)
                ->delete();
            $this->getDataListKonsultasi();
            $this->alert('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal hapus : ' . $e->getMessage());
        }
    }

    public function edit($no_permintaan)
    {
        $data = DB::table('konsultasi_medik')
            ->where('no_permintaan', $no_permintaan)
            ->first();
        $this->no_permintaan = $data->no_permintaan;
        $this->tanggal = $data->tanggal;
        $this->jenis_permintaan = $data->jenis_permintaan;
        $this->kd_dokter = $data->kd_dokter;
        $this->kd_dokter_dikonsuli = $data->kd_dokter_dikonsuli;
        $this->diagnosa_kerja = $data->diagnosa_kerja;
        $this->uraian_konsultasi = $data->uraian_konsultasi;
        $this->modeEdit = true;
    }

    public function simpan()
    {
        $this->validate([
            'jenis_permintaan' => 'required',
            'kd_dokter' => 'required',
            'kd_dokter_dikonsuli' => 'required',
            'diagnosa_kerja' => 'required',
            'uraian_konsultasi' => 'required'
        ], [
            'jenis_permintaan.required' => 'Jenis permintaan harus diisi',
            'kd_dokter.required' => 'Dokter yang meminta harus diisi',
            'kd_dokter_dikonsuli.required' => 'Dokter yang dikonsuli harus diisi',
            'diagnosa_kerja.required' => 'Diagnosa kerja harus diisi',
            'uraian_konsultasi.required' => 'Uraian konsultasi harus diisi'
        ]);

        try {

            if (!$this->modeEdit) {
                DB::table('konsultasi_medik')->insert([
                    'no_permintaan' => $this->generateNoPermintaan(),
                    'no_rawat' => $this->noRawat,
                    'tanggal' => $this->tanggal,
                    'jenis_permintaan' => $this->jenis_permintaan,
                    'kd_dokter' => $this->kd_dokter,
                    'kd_dokter_dikonsuli' => $this->kd_dokter_dikonsuli,
                    'diagnosa_kerja' => $this->diagnosa_kerja,
                    'uraian_konsultasi' => $this->uraian_konsultasi
                ]);
            } else {
                DB::table('konsultasi_medik')
                    ->where('no_permintaan', $this->no_permintaan)
                    ->update([
                        'tanggal' => $this->tanggal,
                        'jenis_permintaan' => $this->jenis_permintaan,
                        'kd_dokter' => $this->kd_dokter,
                        'kd_dokter_dikonsuli' => $this->kd_dokter_dikonsuli,
                        'diagnosa_kerja' => $this->diagnosa_kerja,
                        'uraian_konsultasi' => $this->uraian_konsultasi
                    ]);
            }

            $this->reset(['jenis_permintaan', 'kd_dokter_dikonsuli', 'diagnosa_kerja', 'uraian_konsultasi']);
            $this->getDataListKonsultasi();
            $this->alert('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal simpan : ' . $e->getMessage());
        }
    }
}
