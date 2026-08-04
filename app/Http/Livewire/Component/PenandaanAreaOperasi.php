<?php

namespace App\Http\Livewire\Component;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PenandaanAreaOperasi extends Component
{
    private const DIAGRAM_KEYS = [
        'body_front', 'body_back', 'head_left_profile', 'head_right_profile',
        'head_front', 'head_back', 'hand_left_palm', 'hand_right_palm',
        'hand_left_dorsal', 'hand_right_dorsal', 'foot_left_plantar',
        'foot_right_plantar', 'foot_left_dorsal', 'foot_right_dorsal',
    ];

    public $noRawat;
    public $noRm;
    public $prosedurOperasi = '';
    public $gambarPenandaanBase64 = [];
    public $regPeriksa;
    public $pasien;
    public $dokter;

    public function mount(string $noRawat, string $noRm): void
    {
        $this->noRawat = $noRawat;
        $this->noRm = $noRm;

        $this->regPeriksa = DB::table('reg_periksa')
            ->where('no_rawat', $this->noRawat)
            ->first();

        $this->pasien = $this->regPeriksa
            ? DB::table('pasien')->where('no_rkm_medis', $this->regPeriksa->no_rkm_medis)->first()
            : null;

        $operatorId = session('username');
        $this->dokter = $operatorId
            ? DB::table('dokter')->where('kd_dokter', $operatorId)->first()
            : null;

        $savedMarking = DB::table('penandaan_area_operasi')
            ->where('no_rawat', $this->noRawat)
            ->first();

        if ($savedMarking) {
            $this->prosedurOperasi = $savedMarking->prosedur_operasi;
            $this->emit(
                'loadPenandaanCanvases',
                json_decode($savedMarking->gambar_penandaan_base64, true, 512, JSON_THROW_ON_ERROR)
            );
        }
    }

    public function simpan(): void
    {
        $this->validate([
            'prosedurOperasi' => ['required', 'string'],
            'gambarPenandaanBase64' => ['required', 'array', 'min:1', function ($attribute, $payload, $fail) {
                foreach ($payload as $key => $image) {
                    if (! in_array($key, self::DIAGRAM_KEYS, true)
                        || ! is_string($image)
                        || ! str_starts_with($image, 'data:image/png;base64,')
                        || base64_decode(substr($image, 22), true) === false) {
                        $fail('The diagram payload must use canonical keys and valid PNG Base64 data URLs.');
                    }
                }
            }],
        ]);

        $operatorId = session('username');
        $namaPasien = $this->pasien->nm_pasien ?? '';
        $tglLahir = $this->pasien->tgl_lahir ?? null;
        $namaOperator = $this->dokter->nm_dokter ?? $operatorId;
        $noRm = $this->regPeriksa->no_rkm_medis ?? $this->noRm;

        DB::table('penandaan_area_operasi')->updateOrInsert(
            ['no_rawat' => $this->noRawat],
            ['no_rm' => $noRm, 'nama_pasien' => $namaPasien, 'tgl_lahir' => $tglLahir,
             'prosedur_operasi' => $this->prosedurOperasi, 'operator_id' => $operatorId,
             'nama_operator' => $namaOperator, 'tanggal_penandaan' => now(),
             'gambar_penandaan_base64' => json_encode($this->gambarPenandaanBase64, JSON_THROW_ON_ERROR),
             'format_gambar' => 'image/png', 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function render()
    {
        return '<div></div>';
    }
}
