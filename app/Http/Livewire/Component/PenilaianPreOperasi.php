<?php

namespace App\Http\Livewire\Component;

use App\Services\PenilaianPreOperasiService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class PenilaianPreOperasi extends Component
{
    use LivewireAlert;

    public $no_rawat;
    public $no_rm;
    public $kd_dokter;

    public $tanggal;
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

    public $listSoap = [];
    public $listLab = [];
    public $listRadiologi = [];

    protected $listeners = [
        'hapusPenilaianPreOperasi' => 'hapus',
    ];

    public function mount($noRawat, $noRm = null): void
    {
        $this->no_rawat = $noRawat;
        $this->no_rm = $noRm;
        $this->kd_dokter = session()->get('username');
        $this->tanggal = Carbon::now()->format('Y-m-d\TH:i');
        $this->getData();
        $this->loadDataImportPasien();
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

        $this->loadDataImportPasien();
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
        $this->tanggal = Carbon::parse($data->tanggal)->format('Y-m-d\TH:i');
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
        $this->tanggal = Carbon::now()->format('Y-m-d\TH:i');
        $this->modeEdit = false;
        $this->tanggalEdit = null;
        $this->resetErrorBag();
    }

    public function simpan(): void
    {
        $this->validate([
            'tanggal' => 'required',
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
            $formattedTanggal = ! empty($this->tanggal)
                ? Carbon::parse($this->tanggal)->format('Y-m-d H:i:s')
                : Carbon::now()->format('Y-m-d H:i:s');

            DB::table('penilaian_pre_operasi')->insert([
                'no_rawat' => $this->no_rawat,
                'tanggal' => $formattedTanggal,
                'kd_dokter' => $userDokter,
                'ringkasan_klinik' => $this->ringkasan_klinik,
                'pemeriksaan_fisik' => $this->pemeriksaan_fisik,
                'pemeriksaan_diagnostik' => $this->pemeriksaan_diagnostik,
                'diagnosa_pre_operasi' => $this->diagnosa_pre_operasi,
                'rencana_tindakan_bedah' => $this->rencana_tindakan_bedah,
                'hal_hal_yang_perludi_persiapkan' => $this->hal_hal_yang_perludi_persiapkan,
                'terapi_pre_operasi' => $this->terapi_pre_operasi,
            ]);

            $uploadResult = app(PenilaianPreOperasiService::class)->generateAndUploadPdf($this->no_rawat, $formattedTanggal);

            $this->resetInput();
            $this->getData();

            if (! empty($uploadResult['success'])) {
                if (! empty($uploadResult['is_replaced'])) {
                    $this->alert('success', 'Penilaian pre operasi berhasil disimpan dan berkas digital diperbarui.');
                } else {
                    $this->alert('success', 'Penilaian pre operasi berhasil disimpan dan diunggah ke berkas digital.');
                }
            } else {
                $errorMessage = $uploadResult['message'] ?? 'Error upload berkas digital';
                Log::warning('Penilaian pre operasi auto upload failed: ' . $errorMessage, [
                    'no_rawat' => $this->no_rawat,
                    'tanggal' => $formattedTanggal,
                ]);
                $this->alert('warning', 'Penilaian pre operasi berhasil disimpan, namun gagal diunggah otomatis: ' . $errorMessage);
            }
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan penilaian pre operasi: ' . $e->getMessage(), [
                'no_rawat' => $this->no_rawat,
                'exception' => $e,
            ]);
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
            'tanggal' => 'required',
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
            $formattedTanggal = ! empty($this->tanggal)
                ? Carbon::parse($this->tanggal)->format('Y-m-d H:i:s')
                : $this->tanggalEdit;

            DB::table('penilaian_pre_operasi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $this->tanggalEdit)
                ->update([
                    'tanggal' => $formattedTanggal,
                    'ringkasan_klinik' => $this->ringkasan_klinik,
                    'pemeriksaan_fisik' => $this->pemeriksaan_fisik,
                    'pemeriksaan_diagnostik' => $this->pemeriksaan_diagnostik,
                    'diagnosa_pre_operasi' => $this->diagnosa_pre_operasi,
                    'rencana_tindakan_bedah' => $this->rencana_tindakan_bedah,
                    'hal_hal_yang_perludi_persiapkan' => $this->hal_hal_yang_perludi_persiapkan,
                    'terapi_pre_operasi' => $this->terapi_pre_operasi,
                ]);

            $uploadResult = app(PenilaianPreOperasiService::class)->generateAndUploadPdf($this->no_rawat, $formattedTanggal);

            $this->resetInput();
            $this->getData();

            if (! empty($uploadResult['success'])) {
                $this->alert('success', 'Penilaian pre operasi berhasil diubah dan berkas digital diperbarui.');
            } else {
                $errorMessage = $uploadResult['message'] ?? 'Error upload berkas digital';
                Log::warning('Penilaian pre operasi auto upload update failed: ' . $errorMessage, [
                    'no_rawat' => $this->no_rawat,
                    'tanggal' => $formattedTanggal,
                ]);
                $this->alert('warning', 'Penilaian pre operasi berhasil diubah, namun gagal diunggah otomatis: ' . $errorMessage);
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengubah penilaian pre operasi: ' . $e->getMessage(), [
                'no_rawat' => $this->no_rawat,
                'exception' => $e,
            ]);
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

            app(PenilaianPreOperasiService::class)->deleteDigitalFile($this->no_rawat);

            $this->resetInput();
            $this->getData();
            $this->alert('success', 'Berhasil hapus penilaian pre operasi');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus penilaian pre operasi: ' . $e->getMessage(), [
                'no_rawat' => $this->no_rawat,
                'tanggal' => $this->tanggalEdit,
                'exception' => $e,
            ]);
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

    public function loadDataImportPasien(): void
    {
        if (empty($this->no_rawat)) {
            $this->listSoap = collect([]);
            $this->listLab = collect([]);
            $this->listRadiologi = collect([]);
            return;
        }

        $ranap = DB::table('pemeriksaan_ranap')
            ->where('no_rawat', $this->no_rawat)
            ->select(
                'tgl_perawatan', 'jam_rawat', 'keluhan', 'pemeriksaan',
                'suhu_tubuh', 'tensi', 'nadi', 'respirasi', 'spo2', 'gcs',
                'tinggi', 'berat', DB::raw("'ranap' as sumber")
            );

        $ralan = DB::table('pemeriksaan_ralan')
            ->where('no_rawat', $this->no_rawat)
            ->select(
                'tgl_perawatan', 'jam_rawat', 'keluhan', 'pemeriksaan',
                'suhu_tubuh', 'tensi', 'nadi', 'respirasi', 'spo2', 'gcs',
                'tinggi', 'berat', DB::raw("'ralan' as sumber")
            );

        $this->listSoap = $ranap->unionAll($ralan)
            ->orderBy('tgl_perawatan', 'desc')
            ->orderBy('jam_rawat', 'desc')
            ->get();

        $this->listLab = DB::table('detail_periksa_lab')
            ->join('template_laboratorium', 'detail_periksa_lab.id_template', '=', 'template_laboratorium.id_template')
            ->where('detail_periksa_lab.no_rawat', $this->no_rawat)
            ->select(
                'template_laboratorium.Pemeriksaan',
                'detail_periksa_lab.nilai',
                'template_laboratorium.satuan',
                'detail_periksa_lab.tgl_periksa',
                'detail_periksa_lab.jam'
            )
            ->get();

        $this->listRadiologi = DB::table('hasil_radiologi')
            ->where('no_rawat', $this->no_rawat)
            ->get();
    }

    public function importKeluhan($index): void
    {
        if (is_numeric($index) && isset($this->listSoap[$index])) {
            $text = $this->listSoap[$index]->keluhan ?? '';
        } else {
            $text = $index;
        }

        if (empty($text)) {
            return;
        }
        $this->ringkasan_klinik = mb_substr($text, 0, 500);
    }

    public function importPemeriksaanFisik($index): void
    {
        if (! isset($this->listSoap[$index])) {
            return;
        }

        $item = $this->listSoap[$index];
        $parts = [];

        if (! empty($item->pemeriksaan)) {
            $parts[] = $item->pemeriksaan;
        }

        $ttv = [];
        if (! empty($item->suhu_tubuh)) {
            $ttv[] = "Suhu: {$item->suhu_tubuh} °C";
        }
        if (! empty($item->tensi)) {
            $ttv[] = "TD: {$item->tensi} mmHg";
        }
        if (! empty($item->nadi)) {
            $ttv[] = "Nadi: {$item->nadi} x/m";
        }
        if (! empty($item->respirasi)) {
            $ttv[] = "RR: {$item->respirasi} x/m";
        }
        if (! empty($item->spo2)) {
            $ttv[] = "SpO2: {$item->spo2} %";
        }
        if (! empty($item->gcs)) {
            $ttv[] = "GCS: {$item->gcs}";
        }

        if (! empty($ttv)) {
            $parts[] = "TTV: " . implode(', ', $ttv);
        }

        $formatted = implode("\n", $parts);
        $this->pemeriksaan_fisik = mb_substr($formatted, 0, 500);
    }

    public function importDiagnostikLab($index = null): void
    {
        if (empty($this->listLab) || (is_object($this->listLab) && $this->listLab->isEmpty())) {
            return;
        }

        if ($index !== null) {
            if (! isset($this->listLab[$index])) {
                return;
            }
            $lab = $this->listLab[$index];
            $satuan = ! empty($lab->satuan) ? " {$lab->satuan}" : '';
            $tglJam = (! empty($lab->tgl_periksa) && ! empty($lab->jam))
                ? Carbon::parse("{$lab->tgl_periksa} {$lab->jam}")->format('d/m/Y H:i')
                : Carbon::now()->format('d/m/Y H:i');
            $itemText = "[{$tglJam}] {$lab->Pemeriksaan}: {$lab->nilai}{$satuan}";

            if (empty($this->pemeriksaan_diagnostik)) {
                $this->pemeriksaan_diagnostik = mb_substr($itemText, 0, 500);
            } else {
                $combined = $this->pemeriksaan_diagnostik . "\n" . $itemText;
                $this->pemeriksaan_diagnostik = mb_substr($combined, 0, 500);
            }
            return;
        }

        $firstLab = is_object($this->listLab) ? $this->listLab->first() : ($this->listLab[0] ?? null);
        $tglJam = ($firstLab && ! empty($firstLab->tgl_periksa) && ! empty($firstLab->jam))
            ? Carbon::parse("{$firstLab->tgl_periksa} {$firstLab->jam}")->format('d/m/Y H:i')
            : Carbon::now()->format('d/m/Y H:i');

        $labTexts = [];
        foreach ($this->listLab as $lab) {
            $satuan = ! empty($lab->satuan) ? " {$lab->satuan}" : '';
            $labTexts[] = "- {$lab->Pemeriksaan}: {$lab->nilai}{$satuan}";
        }

        $labFormatted = "Lab ({$tglJam}):\n" . implode("\n", $labTexts);

        if (empty($this->pemeriksaan_diagnostik)) {
            $this->pemeriksaan_diagnostik = mb_substr($labFormatted, 0, 500);
        } else {
            if (strpos($this->pemeriksaan_diagnostik, 'Lab') === false) {
                $combined = $this->pemeriksaan_diagnostik . "\n\n" . $labFormatted;
                $this->pemeriksaan_diagnostik = mb_substr($combined, 0, 500);
            } else {
                $this->pemeriksaan_diagnostik = mb_substr($labFormatted, 0, 500);
            }
        }
    }

    public function importDiagnostikRadiologi($index = null): void
    {
        if (empty($this->listRadiologi) || (is_object($this->listRadiologi) && $this->listRadiologi->isEmpty())) {
            return;
        }

        if ($index !== null) {
            if (! isset($this->listRadiologi[$index])) {
                return;
            }
            $rad = $this->listRadiologi[$index];
            if (empty($rad->hasil)) {
                return;
            }
            $tglJam = (! empty($rad->tgl_periksa) && ! empty($rad->jam))
                ? Carbon::parse("{$rad->tgl_periksa} {$rad->jam}")->format('d/m/Y H:i')
                : Carbon::now()->format('d/m/Y H:i');
            $itemText = "[{$tglJam}] Radiologi: " . trim($rad->hasil);

            if (empty($this->pemeriksaan_diagnostik)) {
                $this->pemeriksaan_diagnostik = mb_substr($itemText, 0, 500);
            } else {
                $combined = $this->pemeriksaan_diagnostik . "\n" . $itemText;
                $this->pemeriksaan_diagnostik = mb_substr($combined, 0, 500);
            }
            return;
        }

        $radTexts = [];
        foreach ($this->listRadiologi as $rad) {
            if (! empty($rad->hasil)) {
                $radTexts[] = trim($rad->hasil);
            }
        }

        if (empty($radTexts)) {
            return;
        }

        $firstRad = is_object($this->listRadiologi) ? $this->listRadiologi->first() : ($this->listRadiologi[0] ?? null);
        $tglJam = ($firstRad && ! empty($firstRad->tgl_periksa) && ! empty($firstRad->jam))
            ? Carbon::parse("{$firstRad->tgl_periksa} {$firstRad->jam}")->format('d/m/Y H:i')
            : Carbon::now()->format('d/m/Y H:i');

        $radFormatted = "Radiologi ({$tglJam}):\n" . implode("\n", $radTexts);

        if (empty($this->pemeriksaan_diagnostik)) {
            $this->pemeriksaan_diagnostik = mb_substr($radFormatted, 0, 500);
        } else {
            if (strpos($this->pemeriksaan_diagnostik, 'Radiologi') === false) {
                $combined = $this->pemeriksaan_diagnostik . "\n\n" . $radFormatted;
                $this->pemeriksaan_diagnostik = mb_substr($combined, 0, 500);
            } else {
                $this->pemeriksaan_diagnostik = mb_substr($radFormatted, 0, 500);
            }
        }
    }

    public function autoFillSemua(): void
    {
        $this->loadDataImportPasien();

        if (! empty($this->listSoap) && count($this->listSoap) > 0) {
            $latestSoap = $this->listSoap->first();
            if (! empty($latestSoap->keluhan)) {
                $this->importKeluhan($latestSoap->keluhan);
            }
            $this->importPemeriksaanFisik(0);
        }

        $this->importDiagnostikLab();
        $this->importDiagnostikRadiologi();

        $this->alert('success', 'Berhasil mengambil data pasien terbaru');
    }

    public function render()
    {
        return view('livewire.component.penilaian-pre-operasi');
    }
}


