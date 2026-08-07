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

        $userDokter = session('username') ?? $this->kd_dokter;

        try {
            if ($this->modeEdit) {
                if (empty($this->tanggalEdit)) {
                    $this->alert('error', 'Tanggal data yang diubah tidak valid');
                    return;
                }

                $tanggalTarget = Carbon::parse($this->tanggalEdit)->format('Y-m-d H:i:s');

                $existing = DB::table('penilaian_pre_operasi')
                    ->where('no_rawat', $this->no_rawat)
                    ->where('tanggal', $tanggalTarget)
                    ->first();

                if (! $existing) {
                    $this->alert('error', 'Data penilaian pre operasi yang akan diubah tidak ditemukan');
                    return;
                }

                if ($existing->kd_dokter != $userDokter) {
                    $this->alert('warning', 'Anda tidak memiliki akses untuk mengubah/menghapus data ini');
                    return;
                }

                DB::table('penilaian_pre_operasi')
                    ->where('no_rawat', $this->no_rawat)
                    ->where('tanggal', $tanggalTarget)
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
                $this->alert('success', 'Penilaian Pre Operasi berhasil diubah');
            } else {
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
                $this->alert('success', 'Penilaian Pre Operasi berhasil disimpan');
            }
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal menyimpan data', [
                'text' => App::environment('local') ? $e->getMessage() : 'Terjadi kesalahan saat menyimpan data',
            ]);
        }
    }

    public function edit($tanggal): void
    {
        $tanggalFormatted = Carbon::parse($tanggal)->format('Y-m-d H:i:s');

        $record = DB::table('penilaian_pre_operasi')
            ->where('no_rawat', $this->no_rawat)
            ->where('tanggal', $tanggalFormatted)
            ->first();

        if (! $record) {
            $this->alert('error', 'Data tidak ditemukan');
            return;
        }

        $userDokter = session('username') ?? $this->kd_dokter;
        if ($record->kd_dokter != $userDokter) {
            $this->alert('warning', 'Anda tidak memiliki akses untuk mengubah/menghapus data ini');
            return;
        }

        $this->ringkasan_klinik = $record->ringkasan_klinik;
        $this->pemeriksaan_fisik = $record->pemeriksaan_fisik;
        $this->pemeriksaan_diagnostik = $record->pemeriksaan_diagnostik;
        $this->diagnosa_pre_operasi = $record->diagnosa_pre_operasi;
        $this->rencana_tindakan_bedah = $record->rencana_tindakan_bedah;
        $this->hal_hal_yang_perludi_persiapkan = $record->hal_hal_yang_perludi_persiapkan;
        $this->terapi_pre_operasi = $record->terapi_pre_operasi;
        $this->modeEdit = true;
        $this->tanggalEdit = $tanggalFormatted;
    }

    public function confirmHapus($tanggal): void
    {
        $tanggalFormatted = Carbon::parse($tanggal)->format('Y-m-d H:i:s');

        $record = DB::table('penilaian_pre_operasi')
            ->where('no_rawat', $this->no_rawat)
            ->where('tanggal', $tanggalFormatted)
            ->first();

        if (! $record) {
            $this->alert('error', 'Data tidak ditemukan');
            return;
        }

        $userDokter = session('username') ?? $this->kd_dokter;
        if ($record->kd_dokter != $userDokter) {
            $this->alert('warning', 'Anda tidak memiliki akses untuk mengubah/menghapus data ini');
            return;
        }

        $this->tanggalEdit = $tanggalFormatted;
        $this->confirm('Yakin ingin menghapus penilaian pre operasi ini?', [
            'toast' => false,
            'position' => 'center',
            'confirmButtonText' => 'Ya, Hapus',
            'cancelButtonText' => 'Batal',
            'onConfirmed' => 'hapusPenilaianPreOperasi',
        ]);
    }

    public function hapus(): void
    {
        if (! $this->tanggalEdit) {
            return;
        }

        try {
            $tanggalFormatted = Carbon::parse($this->tanggalEdit)->format('Y-m-d H:i:s');
            $userDokter = session('username') ?? $this->kd_dokter;

            $record = DB::table('penilaian_pre_operasi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $tanggalFormatted)
                ->first();

            if (! $record || $record->kd_dokter != $userDokter) {
                $this->alert('warning', 'Anda tidak memiliki akses untuk mengubah/menghapus data ini');
                return;
            }

            DB::table('penilaian_pre_operasi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $tanggalFormatted)
                ->delete();

            $this->resetInput();
            $this->getData();
            $this->alert('success', 'Penilaian Pre Operasi berhasil dihapus');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal menghapus data', [
                'text' => App::environment('local') ? $e->getMessage() : 'Terjadi kesalahan saat menghapus data',
            ]);
        }
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

    public function render()
    {
        return view('livewire.component.penilaian-pre-operasi');
    }
}
