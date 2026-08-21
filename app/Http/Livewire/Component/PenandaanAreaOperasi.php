<?php

namespace App\Http\Livewire\Component;

use App\Services\PenandaanAreaOperasiService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class PenandaanAreaOperasi extends Component
{
    use LivewireAlert;

    private const DIAGRAM_KEYS = [
        'body_front', 'body_back', 'head_left_profile', 'head_right_profile',
        'head_front', 'head_back', 'hand_left_palm', 'hand_right_palm',
        'hand_left_dorsal', 'hand_right_dorsal', 'foot_left_plantar',
        'foot_right_plantar', 'foot_left_dorsal', 'foot_right_dorsal',
    ];

    public $noRawat;
    public $noRm;
    public $jenisKelamin = 'P';
    public $tanggalPenandaan;
    public $prosedurOperasi = '';
    public $gambarPenandaanBase64 = [];
    public $savedGambarPenandaanBase64 = [];

    public $data = [];
    public $selectedId = null;
    public $selectedIdHapus = null;
    public $modeEdit = false;

    protected $listeners = [
        'hapusPenandaanAreaOperasi' => 'hapus',
    ];

    public function mount(string $noRawat, string $noRm): void
    {
        $this->noRawat = $noRawat;
        $this->noRm = $noRm;
        $this->tanggalPenandaan = Carbon::now()->format('Y-m-d\TH:i');

        $pasien = DB::table('reg_periksa')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->where('reg_periksa.no_rawat', $this->noRawat)
            ->select('pasien.jk')
            ->first();

        if ($pasien && in_array($pasien->jk, ['L', 'P'], true)) {
            $this->jenisKelamin = $pasien->jk;
        }

        $this->getData();
    }

    public function getData(): void
    {
        if (empty($this->noRawat)) {
            $this->data = [];
            return;
        }

        $this->data = DB::table('penandaan_area_operasi')
            ->leftJoin('dokter', 'penandaan_area_operasi.operator_id', '=', 'dokter.kd_dokter')
            ->where('penandaan_area_operasi.no_rawat', $this->noRawat)
            ->select('penandaan_area_operasi.*', 'dokter.nm_dokter')
            ->orderBy('penandaan_area_operasi.tanggal_penandaan', 'desc')
            ->orderBy('penandaan_area_operasi.id', 'desc')
            ->get();
    }

    public function simpan(): void
    {
        $this->validate([
            'tanggalPenandaan' => ['required'],
            'prosedurOperasi' => ['required', 'string'],
            'gambarPenandaanBase64' => ['required', 'array', 'min:1', function ($attribute, $payload, $fail) {
                foreach ($payload as $key => $image) {
                    $decodedImage = is_string($image)
                        ? base64_decode(substr($image, 22), true)
                        : false;

                    if (! in_array($key, self::DIAGRAM_KEYS, true)
                        || ! is_string($image)
                        || ! str_starts_with($image, 'data:image/png;base64,')
                        || $decodedImage === false
                        || $decodedImage === '') {
                        $fail('The diagram payload must use canonical keys and valid PNG Base64 data URLs.');
                    }
                }
            }],
        ]);

        $operatorId = session('username');
        if (! is_string($operatorId) || trim($operatorId) === '') {
            $this->addError('operator', 'An authenticated operator is required.');
            $this->alert('error', 'Operator tidak terotentikasi. Silakan login kembali.');
            return;
        }

        try {
            $visit = DB::table('reg_periksa')
                ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->where('reg_periksa.no_rawat', $this->noRawat)
                ->select('reg_periksa.no_rkm_medis', 'pasien.nm_pasien', 'pasien.tgl_lahir')
                ->first();
            $dokter = DB::table('dokter')->where('kd_dokter', $operatorId)->first();
            $namaPasien = $visit->nm_pasien ?? '';
            $tglLahir = $visit->tgl_lahir ?? null;
            $namaOperator = $dokter->nm_dokter ?? $operatorId;
            $noRm = $visit->no_rkm_medis ?? $this->noRm;

            $formattedTanggal = ! empty($this->tanggalPenandaan)
                ? Carbon::parse($this->tanggalPenandaan)->format('Y-m-d H:i:s')
                : Carbon::now()->format('Y-m-d H:i:s');

            $insertedId = DB::table('penandaan_area_operasi')->insertGetId([
                'no_rawat' => $this->noRawat,
                'no_rm' => $noRm,
                'nama_pasien' => $namaPasien,
                'tgl_lahir' => $tglLahir,
                'prosedur_operasi' => $this->prosedurOperasi,
                'operator_id' => $operatorId,
                'nama_operator' => $namaOperator,
                'tanggal_penandaan' => $formattedTanggal,
                'gambar_penandaan_base64' => json_encode($this->gambarPenandaanBase64, JSON_THROW_ON_ERROR),
                'format_gambar' => 'image/png',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $uploadResult = app(PenandaanAreaOperasiService::class)->generateAndUploadPdf($this->noRawat, $insertedId);

            $this->batalEdit();
            $this->getData();

            if (! empty($uploadResult['success'])) {
                $this->alert('success', 'Penandaan area operasi berhasil disimpan dan diunggah ke berkas digital.');
            } else {
                $errorMessage = $uploadResult['message'] ?? 'Error upload';
                Log::warning('Penandaan area operasi automatic upload failed: ' . $errorMessage, [
                    'no_rawat' => $this->noRawat,
                    'id' => $insertedId,
                ]);
                $this->alert('warning', 'Penandaan area operasi berhasil disimpan, namun gagal diunggah otomatis: ' . $errorMessage);
            }
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan penandaan area operasi: ' . $e->getMessage(), [
                'no_rawat' => $this->noRawat,
                'exception' => $e,
            ]);
            $this->alert('error', 'Gagal menyimpan penandaan area operasi: ' . $e->getMessage());
        }
    }

    public function edit(int $id): void
    {
        $marking = DB::table('penandaan_area_operasi')
            ->where('no_rawat', $this->noRawat)
            ->where('id', $id)
            ->first();

        if (! $marking) {
            $this->alert('warning', 'Data penandaan tidak ditemukan.');
            return;
        }

        $userDokter = session('username');
        if (! empty($marking->operator_id) && $marking->operator_id !== $userDokter) {
            $this->alert('warning', 'Anda tidak memiliki hak akses untuk mengubah data penandaan dokter lain.');
            return;
        }

        $this->selectedId = $marking->id;
        $this->modeEdit = true;
        $this->prosedurOperasi = $marking->prosedur_operasi;
        $this->tanggalPenandaan = Carbon::parse($marking->tanggal_penandaan)->format('Y-m-d\TH:i');

        try {
            $this->savedGambarPenandaanBase64 = json_decode($marking->gambar_penandaan_base64, true, 512, JSON_THROW_ON_ERROR) ?? [];
        } catch (\Throwable $e) {
            $this->savedGambarPenandaanBase64 = [];
        }

        $this->gambarPenandaanBase64 = $this->savedGambarPenandaanBase64;
        $this->emit('loadPenandaanCanvases', $this->savedGambarPenandaanBase64);
    }

    public function update(): void
    {
        if (empty($this->selectedId)) {
            $this->alert('warning', 'Data yang ingin diubah tidak valid.');
            return;
        }

        $this->validate([
            'tanggalPenandaan' => ['required'],
            'prosedurOperasi' => ['required', 'string'],
            'gambarPenandaanBase64' => ['required', 'array', 'min:1', function ($attribute, $payload, $fail) {
                foreach ($payload as $key => $image) {
                    $decodedImage = is_string($image)
                        ? base64_decode(substr($image, 22), true)
                        : false;

                    if (! in_array($key, self::DIAGRAM_KEYS, true)
                        || ! is_string($image)
                        || ! str_starts_with($image, 'data:image/png;base64,')
                        || $decodedImage === false
                        || $decodedImage === '') {
                        $fail('The diagram payload must use canonical keys and valid PNG Base64 data URLs.');
                    }
                }
            }],
        ]);

        try {
            $formattedTanggal = ! empty($this->tanggalPenandaan)
                ? Carbon::parse($this->tanggalPenandaan)->format('Y-m-d H:i:s')
                : Carbon::now()->format('Y-m-d H:i:s');

            DB::table('penandaan_area_operasi')
                ->where('id', $this->selectedId)
                ->where('no_rawat', $this->noRawat)
                ->update([
                    'prosedur_operasi' => $this->prosedurOperasi,
                    'tanggal_penandaan' => $formattedTanggal,
                    'gambar_penandaan_base64' => json_encode($this->gambarPenandaanBase64, JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);

            $uploadResult = app(PenandaanAreaOperasiService::class)->generateAndUploadPdf($this->noRawat, $this->selectedId);

            $this->batalEdit();
            $this->getData();

            if (! empty($uploadResult['success'])) {
                $this->alert('success', 'Penandaan area operasi berhasil diperbarui dan berkas digital disinkronkan.');
            } else {
                $this->alert('warning', 'Penandaan area operasi berhasil diperbarui.');
            }
        } catch (\Throwable $e) {
            Log::error('Gagal memperbarui penandaan area operasi: ' . $e->getMessage(), [
                'no_rawat' => $this->noRawat,
                'id' => $this->selectedId,
                'exception' => $e,
            ]);
            $this->alert('error', 'Gagal memperbarui penandaan area operasi: ' . $e->getMessage());
        }
    }

    public function confirmHapus(int $id): void
    {
        $marking = DB::table('penandaan_area_operasi')
            ->where('no_rawat', $this->noRawat)
            ->where('id', $id)
            ->first();

        if (! $marking) {
            $this->alert('warning', 'Data tidak ditemukan');
            return;
        }

        $userDokter = session('username');
        if (! empty($marking->operator_id) && $marking->operator_id !== $userDokter) {
            $this->alert('warning', 'Anda tidak memiliki akses untuk menghapus data penandaan dokter lain');
            return;
        }

        $this->selectedIdHapus = $id;
        $this->confirm('Apakah Anda yakin ingin menghapus data penandaan ini?', [
            'onConfirmed' => 'hapusPenandaanAreaOperasi',
            'cancelButtonText' => 'Batal',
            'confirmButtonText' => 'Ya, Hapus',
        ]);
    }

    public function hapus(): void
    {
        if (! $this->selectedIdHapus) {
            return;
        }

        try {
            $marking = DB::table('penandaan_area_operasi')
                ->where('id', $this->selectedIdHapus)
                ->where('no_rawat', $this->noRawat)
                ->first();

            if ($marking) {
                DB::table('penandaan_area_operasi')
                    ->where('id', $this->selectedIdHapus)
                    ->delete();

                if ($this->selectedId === $this->selectedIdHapus) {
                    $this->batalEdit();
                }

                $this->getData();
                $this->alert('success', 'Data penandaan area operasi berhasil dihapus');
            }
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus penandaan area operasi: ' . $e->getMessage(), [
                'id' => $this->selectedIdHapus,
                'exception' => $e,
            ]);
            $this->alert('error', 'Gagal menghapus penandaan: ' . $e->getMessage());
        } finally {
            $this->selectedIdHapus = null;
        }
    }

    public function batalEdit(): void
    {
        $this->reset([
            'prosedurOperasi',
            'selectedId',
            'modeEdit',
            'gambarPenandaanBase64',
            'savedGambarPenandaanBase64',
        ]);
        $this->tanggalPenandaan = Carbon::now()->format('Y-m-d\TH:i');
        $this->resetErrorBag();
        $this->emit('resetAllCanvases');
    }

    public function render()
    {
        return view('livewire.component.penandaan-area-operasi');
    }
}
