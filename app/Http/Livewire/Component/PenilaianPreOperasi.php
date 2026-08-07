<?php

namespace App\Http\Livewire\Component;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class PenilaianPreOperasi extends Component
{
    use LivewireAlert;

    public $no_rawat;
    public $no_rm;
    public $kd_dokter;

    public $ringkasan_klinik;
    public $pemeriksaan_fisik;
    public $pemeriksaan_diagnostik;
    public $diagnosa_pre_operasi;
    public $rencana_tindakan_bedah;
    public $hal_hal_yang_perludi_persiapkan;
    public $terapi_pre_operasi;

    public $data = [];
    public $modeEdit = false;
    public $tanggalEdit = null;

    protected $listeners = [
        'hapusPenilaianPreOperasi' => 'hapus',
    ];

    public function mount($noRawat, $noRm = null): void
    {
        $this->no_rawat = $noRawat;
        $this->no_rm = $noRm;
        $this->kd_dokter = session()->get('username');
        $this->getData();
    }

    public function hydrate(): void
    {
        $this->getData();
    }

    public function getData(): void
    {
        if (empty($this->no_rawat)) {
            $this->data = [];
            return;
        }

        $this->data = DB::table('penilaian_pre_operasi')
            ->leftJoin('dokter', 'penilaian_pre_operasi.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('penilaian_pre_operasi.no_rawat', $this->no_rawat)
            ->select('penilaian_pre_operasi.*', 'dokter.nm_dokter')
            ->orderBy('penilaian_pre_operasi.tanggal', 'desc')
            ->get();
    }

    public function edit($tanggal): void
    {
        $data = DB::table('penilaian_pre_operasi')
            ->where('no_rawat', $this->no_rawat)
            ->where('tanggal', $tanggal)
            ->first();

        if (! $data) {
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

        $userDokter = session()->get('username') ?? $this->kd_dokter;
        if ($data->kd_dokter != $userDokter) {
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
        $this->ringkasan_klinik = $data->ringkasan_klinik;
        $this->pemeriksaan_fisik = $data->pemeriksaan_fisik;
        $this->pemeriksaan_diagnostik = $data->pemeriksaan_diagnostik;
        $this->diagnosa_pre_operasi = $data->diagnosa_pre_operasi;
        $this->rencana_tindakan_bedah = $data->rencana_tindakan_bedah;
        $this->hal_hal_yang_perludi_persiapkan = $data->hal_hal_yang_perludi_persiapkan;
        $this->terapi_pre_operasi = $data->terapi_pre_operasi;

        $this->modeEdit = true;
    }

    public function resetInput(): void
    {
        $this->reset([
            'ringkasan_klinik',
            'pemeriksaan_fisik',
            'pemeriksaan_diagnostik',
            'diagnosa_pre_operasi',
            'rencana_tindakan_bedah',
            'hal_hal_yang_perludi_persiapkan',
            'terapi_pre_operasi',
        ]);
        $this->modeEdit = false;
        $this->tanggalEdit = null;
        $this->resetErrorBag();
    }

    public function simpan(): void
    {
        $this->validate([
            'ringkasan_klinik' => 'nullable|string|max:500',
            'pemeriksaan_fisik' => 'nullable|string|max:500',
            'pemeriksaan_diagnostik' => 'nullable|string|max:500',
            'diagnosa_pre_operasi' => 'nullable|string|max:500',
            'rencana_tindakan_bedah' => 'nullable|string|max:500',
            'hal_hal_yang_perludi_persiapkan' => 'nullable|string|max:500',
            'terapi_pre_operasi' => 'nullable|string|max:500',
        ], [
            'max' => 'Karakter maksimal 500 karakter.',
        ]);

        $userDokter = session()->get('username') ?? $this->kd_dokter;

        // Guard against rapid double-submission (< 3s) creating duplicate records
        $recentDuplicate = DB::table('penilaian_pre_operasi')
            ->where('no_rawat', $this->no_rawat)
            ->where('kd_dokter', $userDokter)
            ->where('tanggal', '>=', Carbon::now()->subSeconds(3)->format('Y-m-d H:i:s'))
            ->first();

        if ($recentDuplicate) {
            $this->resetInput();
            $this->getData();
            return;
        }

        try {
            DB::table('penilaian_pre_operasi')->insert([
                'no_rawat' => $this->no_rawat,
                'tanggal' => Carbon::now()->format('Y-m-d H:i:s'),
                'kd_dokter' => $userDokter,
                'ringkasan_klinik' => $this->ringkasan_klinik,
                'pemeriksaan_fisik' => $this->pemeriksaan_fisik,
                'pemeriksaan_diagnostik' => $this->pemeriksaan_diagnostik,
                'diagnosa_pre_operasi' => $this->diagnosa_pre_operasi,
                'rencana_tindakan_bedah' => $this->rencana_tindakan_bedah,
                'hal_hal_yang_perludi_persiapkan' => $this->hal_hal_yang_perludi_persiapkan,
                'terapi_pre_operasi' => $this->terapi_pre_operasi,
            ]);

            $this->resetInput();
            $this->getData();
            $this->alert('success', 'Berhasil input penilaian pre operasi');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => App::environment('local') ? $e->getMessage() : 'Terjadi kesalahan saat menyimpan data',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
        }
    }

    public function update(): void
    {
        if (empty($this->tanggalEdit)) {
            $this->alert('warning', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => 'Data yang diubah tidak ditemukan',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
            return;
        }

        $this->validate([
            'ringkasan_klinik' => 'nullable|string|max:500',
            'pemeriksaan_fisik' => 'nullable|string|max:500',
            'pemeriksaan_diagnostik' => 'nullable|string|max:500',
            'diagnosa_pre_operasi' => 'nullable|string|max:500',
            'rencana_tindakan_bedah' => 'nullable|string|max:500',
            'hal_hal_yang_perludi_persiapkan' => 'nullable|string|max:500',
            'terapi_pre_operasi' => 'nullable|string|max:500',
        ], [
            'max' => 'Karakter maksimal 500 karakter.',
        ]);

        try {
            DB::table('penilaian_pre_operasi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $this->tanggalEdit)
                ->update([
                    'ringkasan_klinik' => $this->ringkasan_klinik,
                    'pemeriksaan_fisik' => $this->pemeriksaan_fisik,
                    'pemeriksaan_diagnostik' => $this->pemeriksaan_diagnostik,
                    'diagnosa_pre_operasi' => $this->diagnosa_pre_operasi,
                    'rencana_tindakan_bedah' => $this->rencana_tindakan_bedah,
                    'hal_hal_yang_perludi_persiapkan' => $this->hal_hal_yang_perludi_persiapkan,
                    'terapi_pre_operasi' => $this->terapi_pre_operasi,
                ]);

            $this->resetInput();
            $this->getData();
            $this->alert('success', 'Berhasil ubah penilaian pre operasi');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => App::environment('local') ? $e->getMessage() : 'Terjadi kesalahan saat mengubah data',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
        }
    }

    public function confirmHapus($tanggal): void
    {
        $data = DB::table('penilaian_pre_operasi')
            ->where('no_rawat', $this->no_rawat)
            ->where('tanggal', $tanggal)
            ->first();

        if (! $data) {
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

        $userDokter = session()->get('username') ?? $this->kd_dokter;
        if ($data->kd_dokter != $userDokter) {
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

        $this->tanggalEdit = $tanggal;
        $this->confirm('Apakah anda yakin ingin menghapus data ini?', [
            'onConfirmed' => 'hapusPenilaianPreOperasi',
            'cancelButtonText' => 'Batal',
        ]);
    }

    public function hapus(): void
    {
        if (! $this->tanggalEdit) {
            return;
        }

        try {
            $data = DB::table('penilaian_pre_operasi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $this->tanggalEdit)
                ->first();

            $userDokter = session()->get('username') ?? $this->kd_dokter;
            if (! $data || $data->kd_dokter != $userDokter) {
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

            DB::table('penilaian_pre_operasi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $this->tanggalEdit)
                ->delete();

            $this->resetInput();
            $this->getData();
            $this->alert('success', 'Berhasil hapus penilaian pre operasi');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => App::environment('local') ? $e->getMessage() : 'Terjadi kesalahan saat menghapus data',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.component.penilaian-pre-operasi');
    }
}


