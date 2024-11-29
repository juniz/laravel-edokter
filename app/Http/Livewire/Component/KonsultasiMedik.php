<?php

namespace App\Http\Livewire\Component;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class KonsultasiMedik extends Component
{
    use LivewireAlert;
    public $noRawat;
    public $noRm;
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
    public $riwayatRujukan;
    public $modeEdit = false;
    public $jawaban_diagnosa_kerja;
    public $jawaban_uraian_konsultasi;

    public function mount($noRawat, $noRm)
    {
        $this->noRawat = $noRawat;
        $this->noRm = $noRm;
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
        $this->getRujukan();
        // dd($this->generateNoPermintaan());
    }

    public function hydrate()
    {
        $this->getDataListKonsultasi();
        $this->getListDokter();
        $this->getRujukan();
    }

    public function getRujukan()
    {
        $rm = $this->noRawat;
        $data = DB::table('rujukan_internal_poli')
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'rujukan_internal_poli.kd_poli')
            ->join('dokter', 'dokter.kd_dokter', '=', 'rujukan_internal_poli.kd_dokter')
            ->join('rujukan_internal_poli_detail', 'rujukan_internal_poli_detail.no_rawat', '=', 'rujukan_internal_poli.no_rawat')
            ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'rujukan_internal_poli.no_rawat')
            ->where('reg_periksa.no_rkm_medis', $this->noRm)
            // ->where('rujukan_internal_poli.kd_dokter', $kdDokter)
            // ->where('rujukan_internal_poli.kd_poli', $kdPoli)
            ->select('rujukan_internal_poli.no_rawat', 'poliklinik.nm_poli', 'dokter.nm_dokter', 'rujukan_internal_poli_detail.konsul', 'rujukan_internal_poli_detail.pemeriksaan', 'rujukan_internal_poli_detail.diagnosa', 'rujukan_internal_poli_detail.saran', 'reg_periksa.tgl_registrasi')
            ->orderByDesc('reg_periksa.tgl_registrasi')
            ->get();
        // dd($data);
        $this->riwayatRujukan = $data;
    }

    public function getPerujuk($noRawat)
    {
        $data = DB::table('reg_periksa')
            ->join('dokter', 'dokter.kd_dokter', '=', 'reg_periksa.kd_dokter')
            ->where('reg_periksa.no_rawat', $noRawat)
            ->select('dokter.nm_dokter')
            ->first();
        return $data->nm_dokter;
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
            ->where('pasien.no_rkm_medis', $this->noRm)
            ->where('konsultasi_medik.kd_dokter', session()->get('username'))
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

    public function getJawaban($no_permintaan)
    {
        $this->jawaban_diagnosa_kerja = '';
        $this->jawaban_uraian_konsultasi = '';

        $data = DB::table('jawaban_konsultasi_medik')
            ->where('no_permintaan', $no_permintaan)
            ->first();

        $this->jawaban_diagnosa_kerja = $data->diagnosa_kerja;
        $this->jawaban_uraian_konsultasi = $data->uraian_jawaban;
        $this->emit('openJawabanKonsultasi', $no_permintaan);
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
