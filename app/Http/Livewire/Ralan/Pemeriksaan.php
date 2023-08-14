<?php

namespace App\Http\Livewire\Ralan;

use App\Traits\SwalResponse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Pemeriksaan extends Component
{
    use SwalResponse;
    public $listPemeriksaan, $isCollapsed = false, $noRawat, $noRm, $isMaximized = true, $keluhan, $pemeriksaan, $penilaian, $instruksi, $rtl, $alergi, $suhu, $berat, $tinggi, $tensi, $nadi, $respirasi, $evaluasi, $gcs, $kesadaran = 'Compos Mentis', $lingkar, $spo2;

    public function mount($noRawat, $noRm)
    {
        $this->noRawat = $noRawat;
        $this->noRm = $noRm;
        if (!$this->isCollapsed) {
            $this->getPemeriksaan();
            $this->getListPemeriksaan();
        }
    }

    public function openModal()
    {
        $this->emit('openModalRehabMedik');
    }

    public function render()
    {
        return view('livewire.ralan.pemeriksaan');
    }

    public function hydrate()
    {
        $this->getPemeriksaan();
        $this->getListPemeriksaan();
    }

    public function getListPemeriksaan()
    {
        $this->listPemeriksaan = DB::table('pemeriksaan_ralan')
            ->where('no_rawat', $this->noRawat)
            ->get();
    }

    public function collapsed()
    {
        $this->isCollapsed = !$this->isCollapsed;
    }

    public function expanded()
    {
        $this->isMaximized = !$this->isMaximized;
    }

    public function getPemeriksaan()
    {
        $data = DB::table('pasien')
            ->join('pemeriksaan_ralan', 'pasien.no_rkm_medis', '=', 'pemeriksaan_ralan.no_rawat')
            ->where('pasien.no_rkm_medis', $this->noRm)
            ->where('pemeriksaan_ralan.alergi', '<>', 'Tidak Ada')
            ->select('pemeriksaan_ralan.alergi')
            ->first();

        $pemeriksaan = DB::table('pemeriksaan_ralan')
            ->where('no_rawat', $this->noRawat)
            ->orderBy('jam_rawat', 'desc')
            ->first();
        if ($pemeriksaan) {
            $this->keluhan = $pemeriksaan->keluhan;
            $this->pemeriksaan = $pemeriksaan->pemeriksaan;
            $this->penilaian = $pemeriksaan->penilaian;
            $this->instruksi = $pemeriksaan->instruksi;
            $this->rtl = $pemeriksaan->rtl;
            $this->alergi = $pemeriksaan->alergi ?? $data->alergi ?? 'Tidak Ada';
            $this->suhu = $pemeriksaan->suhu_tubuh;
            $this->berat = $pemeriksaan->berat;
            $this->tinggi = $pemeriksaan->tinggi;
            $this->tensi = $pemeriksaan->tensi;
            $this->nadi = $pemeriksaan->nadi;
            $this->respirasi = $pemeriksaan->respirasi;
            $this->evaluasi = $pemeriksaan->evaluasi;
            $this->gcs = $pemeriksaan->gcs;
            $this->kesadaran = $pemeriksaan->kesadaran;
            $this->lingkar = $pemeriksaan->lingkar_perut;
            $this->spo2 = $pemeriksaan->spo2;
        }
    }

    public function simpanPemeriksaan()
    {
        try {
            DB::beginTransaction();
            DB::table('pemeriksaan_ralan')
                ->insert([
                    'no_rawat' => $this->noRawat,
                    'keluhan' => $this->keluhan,
                    'pemeriksaan' => $this->pemeriksaan,
                    'penilaian' => $this->penilaian ?? '-',
                    'instruksi' => $this->instruksi ?? '-',
                    'rtl' => $this->rtl ?? '-',
                    'alergi' => $this->alergi,
                    'suhu_tubuh' => $this->suhu,
                    'berat' => $this->berat,
                    'tinggi' => $this->tinggi,
                    'tensi' => $this->tensi ?? '-',
                    'nadi' => $this->nadi,
                    'respirasi' => $this->respirasi,
                    'gcs' => $this->gcs,
                    'kesadaran' => $this->kesadaran ?? 'Compos Mentis',
                    'lingkar_perut' => $this->lingkar,
                    'spo2' => $this->spo2 ?? '-',
                    'evaluasi' => $this->evaluasi ?? '-',
                    'tgl_perawatan' => date('Y-m-d'),
                    'jam_rawat' => date('H:i:s'),
                    'nip' => session()->get('username'),
                ]);

            DB::commit();
            $this->getPemeriksaan();
            $this->dispatchBrowserEvent('swal:pemeriksaan', $this->toastResponse('Pemeriksaan berhasil ditambahkan'));
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            $this->dispatchBrowserEvent('swal:pemeriksaan', $this->toastResponse($ex->getMessage() ?? 'Pemeriksaan gagal ditambahkan', 'error'));
        }
    }
}
