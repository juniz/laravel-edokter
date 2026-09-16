<?php

namespace App\Http\Livewire\Component;

use App\Services\PenilaianPreInduksiService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class PenilaianPreInduksi extends Component
{
    use LivewireAlert;

    public $no_rawat;
    public $no_rm;
    public $kd_dokter;

    // Fields
    public $tanggal;
    public $tensi = '';
    public $nadi = '';
    public $rr = '';
    public $suhu = '';
    public $ekg = '';
    public $lain_lain = '';
    public $asesmen = '';
    public $perencanaan = '';
    public $infus_perifier = '';
    public $cvc = '';
    public $posisi = '';
    public $premedikasi = '';
    public $premedikasi_keterangan = '';
    public $induksi = '';
    public $induksi_keterangan = '';
    public $face_mask_no = '';
    public $nasopharing_no = '';
    public $ett_no = '';
    public $ett_jenis = '';
    public $ett_viksasi = '';
    public $lma_no = '';
    public $lma_jenis = '';
    public $tracheostomi = '';
    public $bronchoscopi_fiberoptik = '';
    public $glidescopi = '';
    public $lain_lain_tatalaksana = '';
    public $intubasi_sesudah_tidur = '';
    public $intubasi_oral = '';
    public $intubasi_tracheostomi = '';
    public $intubasi_keterangan = '';
    public $sulit_ventilasi = '';
    public $sulit_intubasi = '';
    public $ventilasi = '';
    public $teknik_regional_jenis = '';
    public $teknik_regional_lokasi = '';
    public $teknik_regional_jenis_jarum = '';
    public $teknik_regional_kateter = '';
    public $teknik_regional_kateter_viksasi = '';
    public $teknik_regional_obat_obatan = '';
    public $teknik_regional_komplikasi = '';
    public $teknik_regional_hasil = '';

    public $data = [];
    public $modeEdit = false;
    public $tanggalEdit = null;

    protected $listeners = [
        'hapusPenilaianPreInduksi' => 'hapus',
    ];

    public function mount($noRawat, $noRm = null): void
    {
        $this->no_rawat = $noRawat;
        $this->no_rm = $noRm;
        $this->kd_dokter = session()->get('username');
        $this->tanggal = Carbon::now()->format('Y-m-d\TH:i');
        $this->getData();
        $this->autofillVitalSigns();
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

        $this->data = DB::table('penilaian_pre_induksi')
            ->leftJoin('dokter', 'penilaian_pre_induksi.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('penilaian_pre_induksi.no_rawat', $this->no_rawat)
            ->select('penilaian_pre_induksi.*', 'dokter.nm_dokter')
            ->orderBy('penilaian_pre_induksi.tanggal', 'desc')
            ->get();
    }

    public function autofillVitalSigns(): void
    {
        if ($this->modeEdit || empty($this->no_rawat)) {
            return;
        }

        $pemeriksaan = DB::table('pemeriksaan_ralan')
            ->where('no_rawat', $this->no_rawat)
            ->orderBy('tgl_perawatan', 'desc')
            ->orderBy('jam_rawat', 'desc')
            ->first();

        if (! $pemeriksaan) {
            $pemeriksaan = DB::table('pemeriksaan_ranap')
                ->where('no_rawat', $this->no_rawat)
                ->orderBy('tgl_perawatan', 'desc')
                ->orderBy('jam_rawat', 'desc')
                ->first();
        }

        if ($pemeriksaan) {
            if (empty($this->tensi) && isset($pemeriksaan->tensi)) {
                $this->tensi = (string) $pemeriksaan->tensi;
            }
            if (empty($this->nadi) && isset($pemeriksaan->nadi)) {
                $this->nadi = (string) $pemeriksaan->nadi;
            }
            if (empty($this->rr) && isset($pemeriksaan->respirasi)) {
                $this->rr = (string) $pemeriksaan->respirasi;
            }
            if (empty($this->suhu) && isset($pemeriksaan->suhu_tubuh)) {
                $this->suhu = (string) $pemeriksaan->suhu_tubuh;
            }
        }
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required',
            'kd_dokter' => 'required',
            'tensi' => 'nullable|string|max:8',
            'nadi' => 'nullable|string|max:5',
            'rr' => 'nullable|string|max:5',
            'suhu' => 'nullable|string|max:5',
            'ekg' => 'nullable|string|max:50',
            'lain_lain' => 'nullable|string|max:50',
            'asesmen' => 'nullable|in:Sesuai Asesmen Pre Sedasi/Anestesi,Tidak Sesuai Asesmen Pre Sedasi/Anestesi',
            'perencanaan' => 'nullable|string|max:300',
            'infus_perifier' => 'nullable|string|max:300',
            'cvc' => 'nullable|string|max:70',
            'posisi' => 'nullable|in:Supine,Lithotomi,Lateral,Prone,Perlindungan Mata,Kanan,Kiri,Lain-lain',
            'premedikasi' => 'nullable|in:Oral,IM,IV',
            'premedikasi_keterangan' => 'nullable|string|max:50',
            'induksi' => 'nullable|in:Intravena,Inhalasi',
            'induksi_keterangan' => 'nullable|string|max:70',
            'face_mask_no' => 'nullable|string|max:20',
            'nasopharing_no' => 'nullable|string|max:20',
            'ett_no' => 'nullable|string|max:20',
            'ett_jenis' => 'nullable|string|max:20',
            'ett_viksasi' => 'nullable|string|max:25',
            'lma_no' => 'nullable|string|max:20',
            'lma_jenis' => 'nullable|string|max:20',
            'tracheostomi' => 'nullable|string|max:60',
            'bronchoscopi_fiberoptik' => 'nullable|string|max:60',
            'glidescopi' => 'nullable|string|max:60',
            'lain_lain_tatalaksana' => 'nullable|string|max:100',
            'intubasi_sesudah_tidur' => 'nullable|in:Ya,Tidak',
            'intubasi_oral' => 'nullable|in:Ya,Tidak',
            'intubasi_tracheostomi' => 'nullable|in:Ya,Tidak',
            'intubasi_keterangan' => 'nullable|string|max:200',
            'sulit_ventilasi' => 'nullable|string|max:100',
            'sulit_intubasi' => 'nullable|string|max:100',
            'ventilasi' => 'nullable|string|max:100',
            'teknik_regional_jenis' => 'nullable|string|max:100',
            'teknik_regional_lokasi' => 'nullable|string|max:40',
            'teknik_regional_jenis_jarum' => 'nullable|string|max:30',
            'teknik_regional_kateter' => 'nullable|in:Ya,Tidak',
            'teknik_regional_kateter_viksasi' => 'nullable|string|max:40',
            'teknik_regional_obat_obatan' => 'nullable|string|max:400',
            'teknik_regional_komplikasi' => 'nullable|string|max:200',
            'teknik_regional_hasil' => 'nullable|string|max:100',
        ];
    }

    public function simpan(): void
    {
        $this->validate();

        try {
            $formattedTanggal = Carbon::parse($this->tanggal)->format('Y-m-d H:i:s');

            $exists = DB::table('penilaian_pre_induksi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $formattedTanggal)
                ->exists();

            if ($exists) {
                $this->alert('error', 'Penilaian Pre Induksi pada tanggal tersebut sudah ada!');
                return;
            }

            DB::table('penilaian_pre_induksi')->insert([
                'no_rawat' => $this->no_rawat,
                'tanggal' => $formattedTanggal,
                'kd_dokter' => $this->kd_dokter,
                'tensi' => $this->tensi ?: null,
                'nadi' => $this->nadi ?: null,
                'rr' => $this->rr ?: null,
                'suhu' => $this->suhu ?: null,
                'ekg' => $this->ekg ?: null,
                'lain_lain' => $this->lain_lain ?: null,
                'asesmen' => $this->asesmen ?: null,
                'perencanaan' => $this->perencanaan ?: null,
                'infus_perifier' => $this->infus_perifier ?: null,
                'cvc' => $this->cvc ?: null,
                'posisi' => $this->posisi ?: null,
                'premedikasi' => $this->premedikasi ?: null,
                'premedikasi_keterangan' => $this->premedikasi_keterangan ?: null,
                'induksi' => $this->induksi ?: null,
                'induksi_keterangan' => $this->induksi_keterangan ?: null,
                'face_mask_no' => $this->face_mask_no ?: null,
                'nasopharing_no' => $this->nasopharing_no ?: null,
                'ett_no' => $this->ett_no ?: null,
                'ett_jenis' => $this->ett_jenis ?: null,
                'ett_viksasi' => $this->ett_viksasi ?: null,
                'lma_no' => $this->lma_no ?: null,
                'lma_jenis' => $this->lma_jenis ?: null,
                'tracheostomi' => $this->tracheostomi ?: null,
                'bronchoscopi_fiberoptik' => $this->bronchoscopi_fiberoptik ?: null,
                'glidescopi' => $this->glidescopi ?: null,
                'lain_lain_tatalaksana' => $this->lain_lain_tatalaksana ?: null,
                'intubasi_sesudah_tidur' => $this->intubasi_sesudah_tidur ?: null,
                'intubasi_oral' => $this->intubasi_oral ?: null,
                'intubasi_tracheostomi' => $this->intubasi_tracheostomi ?: null,
                'intubasi_keterangan' => $this->intubasi_keterangan ?: null,
                'sulit_ventilasi' => $this->sulit_ventilasi ?: null,
                'sulit_intubasi' => $this->sulit_intubasi ?: null,
                'ventilasi' => $this->ventilasi ?: null,
                'teknik_regional_jenis' => $this->teknik_regional_jenis ?: null,
                'teknik_regional_lokasi' => $this->teknik_regional_lokasi ?: null,
                'teknik_regional_jenis_jarum' => $this->teknik_regional_jenis_jarum ?: null,
                'teknik_regional_kateter' => $this->teknik_regional_kateter ?: null,
                'teknik_regional_kateter_viksasi' => $this->teknik_regional_kateter_viksasi ?: null,
                'teknik_regional_obat_obatan' => $this->teknik_regional_obat_obatan ?: null,
                'teknik_regional_komplikasi' => $this->teknik_regional_komplikasi ?: null,
                'teknik_regional_hasil' => $this->teknik_regional_hasil ?: null,
            ]);

            $uploadResult = app(PenilaianPreInduksiService::class)->generateAndUploadPdf($this->no_rawat, $formattedTanggal);
            $this->batal();
            $this->getData();

            if (! empty($uploadResult['success'])) {
                if (! empty($uploadResult['is_replaced'])) {
                    $this->alert('success', 'Penilaian Pre Induksi berhasil disimpan dan berkas digital diperbarui.');
                } else {
                    $this->alert('success', 'Penilaian Pre Induksi berhasil disimpan dan diunggah ke berkas digital.');
                }
            } else {
                Log::warning('Penilaian Pre Induksi auto upload failed on save', [
                    'no_rawat' => $this->no_rawat,
                    'tanggal' => $formattedTanggal,
                    'result' => $uploadResult,
                ]);
                $this->alert('warning', 'Penilaian Pre Induksi berhasil disimpan, namun gagal diunggah otomatis: ' . ($uploadResult['message'] ?? 'Error upload'));
            }
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function edit($tanggal): void
    {
        $row = DB::table('penilaian_pre_induksi')
            ->where('no_rawat', $this->no_rawat)
            ->where('tanggal', $tanggal)
            ->first();

        if (! $row) {
            $this->alert('error', 'Data tidak ditemukan!');
            return;
        }

        $this->modeEdit = true;
        $this->tanggalEdit = $tanggal;
        $this->tanggal = Carbon::parse($row->tanggal)->format('Y-m-d\TH:i');
        $this->kd_dokter = $row->kd_dokter;

        $fields = [
            'tensi', 'nadi', 'rr', 'suhu', 'ekg', 'lain_lain', 'asesmen', 'perencanaan',
            'infus_perifier', 'cvc', 'posisi', 'premedikasi', 'premedikasi_keterangan',
            'induksi', 'induksi_keterangan', 'face_mask_no', 'nasopharing_no', 'ett_no',
            'ett_jenis', 'ett_viksasi', 'lma_no', 'lma_jenis', 'tracheostomi',
            'bronchoscopi_fiberoptik', 'glidescopi', 'lain_lain_tatalaksana',
            'intubasi_sesudah_tidur', 'intubasi_oral', 'intubasi_tracheostomi',
            'intubasi_keterangan', 'sulit_ventilasi', 'sulit_intubasi', 'ventilasi',
            'teknik_regional_jenis', 'teknik_regional_lokasi', 'teknik_regional_jenis_jarum',
            'teknik_regional_kateter', 'teknik_regional_kateter_viksasi',
            'teknik_regional_obat_obatan', 'teknik_regional_komplikasi', 'teknik_regional_hasil',
        ];

        foreach ($fields as $field) {
            $this->{$field} = $row->{$field} ?? '';
        }
    }

    public function update(): void
    {
        $this->validate();

        try {
            $formattedTanggal = Carbon::parse($this->tanggal)->format('Y-m-d H:i:s');

            DB::table('penilaian_pre_induksi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $this->tanggalEdit)
                ->update([
                    'tanggal' => $formattedTanggal,
                    'kd_dokter' => $this->kd_dokter,
                    'tensi' => $this->tensi ?: null,
                    'nadi' => $this->nadi ?: null,
                    'rr' => $this->rr ?: null,
                    'suhu' => $this->suhu ?: null,
                    'ekg' => $this->ekg ?: null,
                    'lain_lain' => $this->lain_lain ?: null,
                    'asesmen' => $this->asesmen ?: null,
                    'perencanaan' => $this->perencanaan ?: null,
                    'infus_perifier' => $this->infus_perifier ?: null,
                    'cvc' => $this->cvc ?: null,
                    'posisi' => $this->posisi ?: null,
                    'premedikasi' => $this->premedikasi ?: null,
                    'premedikasi_keterangan' => $this->premedikasi_keterangan ?: null,
                    'induksi' => $this->induksi ?: null,
                    'induksi_keterangan' => $this->induksi_keterangan ?: null,
                    'face_mask_no' => $this->face_mask_no ?: null,
                    'nasopharing_no' => $this->nasopharing_no ?: null,
                    'ett_no' => $this->ett_no ?: null,
                    'ett_jenis' => $this->ett_jenis ?: null,
                    'ett_viksasi' => $this->ett_viksasi ?: null,
                    'lma_no' => $this->lma_no ?: null,
                    'lma_jenis' => $this->lma_jenis ?: null,
                    'tracheostomi' => $this->tracheostomi ?: null,
                    'bronchoscopi_fiberoptik' => $this->bronchoscopi_fiberoptik ?: null,
                    'glidescopi' => $this->glidescopi ?: null,
                    'lain_lain_tatalaksana' => $this->lain_lain_tatalaksana ?: null,
                    'intubasi_sesudah_tidur' => $this->intubasi_sesudah_tidur ?: null,
                    'intubasi_oral' => $this->intubasi_oral ?: null,
                    'intubasi_tracheostomi' => $this->intubasi_tracheostomi ?: null,
                    'intubasi_keterangan' => $this->intubasi_keterangan ?: null,
                    'sulit_ventilasi' => $this->sulit_ventilasi ?: null,
                    'sulit_intubasi' => $this->sulit_intubasi ?: null,
                    'ventilasi' => $this->ventilasi ?: null,
                    'teknik_regional_jenis' => $this->teknik_regional_jenis ?: null,
                    'teknik_regional_lokasi' => $this->teknik_regional_lokasi ?: null,
                    'teknik_regional_jenis_jarum' => $this->teknik_regional_jenis_jarum ?: null,
                    'teknik_regional_kateter' => $this->teknik_regional_kateter ?: null,
                    'teknik_regional_kateter_viksasi' => $this->teknik_regional_kateter_viksasi ?: null,
                    'teknik_regional_obat_obatan' => $this->teknik_regional_obat_obatan ?: null,
                    'teknik_regional_komplikasi' => $this->teknik_regional_komplikasi ?: null,
                    'teknik_regional_hasil' => $this->teknik_regional_hasil ?: null,
                ]);

            $uploadResult = app(PenilaianPreInduksiService::class)->generateAndUploadPdf($this->no_rawat, $formattedTanggal);
            $this->batal();
            $this->getData();

            if (! empty($uploadResult['success'])) {
                $this->alert('success', 'Penilaian Pre Induksi berhasil diperbarui dan berkas digital diperbarui.');
            } else {
                Log::warning('Penilaian Pre Induksi auto upload failed on update', [
                    'no_rawat' => $this->no_rawat,
                    'tanggal' => $formattedTanggal,
                    'result' => $uploadResult,
                ]);
                $this->alert('warning', 'Penilaian Pre Induksi berhasil diperbarui, namun gagal diunggah otomatis: ' . ($uploadResult['message'] ?? 'Error upload'));
            }
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    public function confirmHapus($tanggal): void
    {
        $this->alert('warning', 'Apakah Anda yakin ingin menghapus data ini?', [
            'position' => 'center',
            'timer' => null,
            'toast' => false,
            'showConfirmButton' => true,
            'onConfirmed' => 'hapusPenilaianPreInduksi',
            'showCancelButton' => true,
            'onDismissed' => '',
            'data' => [
                'tanggal' => $tanggal,
            ],
        ]);
    }

    public function hapus($data): void
    {
        $tanggal = is_array($data) ? ($data['data']['tanggal'] ?? null) : $data;

        if ($tanggal) {
            DB::table('penilaian_pre_induksi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $tanggal)
                ->delete();

            app(PenilaianPreInduksiService::class)->deleteDigitalFile($this->no_rawat);

            $this->alert('success', 'Penilaian Pre Induksi berhasil dihapus!');
            $this->getData();
        }
    }

    public function batal(): void
    {
        $this->modeEdit = false;
        $this->tanggalEdit = null;
        $this->tanggal = Carbon::now()->format('Y-m-d\TH:i');

        $fields = [
            'tensi', 'nadi', 'rr', 'suhu', 'ekg', 'lain_lain', 'asesmen', 'perencanaan',
            'infus_perifier', 'cvc', 'posisi', 'premedikasi', 'premedikasi_keterangan',
            'induksi', 'induksi_keterangan', 'face_mask_no', 'nasopharing_no', 'ett_no',
            'ett_jenis', 'ett_viksasi', 'lma_no', 'lma_jenis', 'tracheostomi',
            'bronchoscopi_fiberoptik', 'glidescopi', 'lain_lain_tatalaksana',
            'intubasi_sesudah_tidur', 'intubasi_oral', 'intubasi_tracheostomi',
            'intubasi_keterangan', 'sulit_ventilasi', 'sulit_intubasi', 'ventilasi',
            'teknik_regional_jenis', 'teknik_regional_lokasi', 'teknik_regional_jenis_jarum',
            'teknik_regional_kateter', 'teknik_regional_kateter_viksasi',
            'teknik_regional_obat_obatan', 'teknik_regional_komplikasi', 'teknik_regional_hasil',
        ];

        foreach ($fields as $field) {
            $this->{$field} = '';
        }

        $this->autofillVitalSigns();
    }

    public function render()
    {
        return view('livewire.component.penilaian-pre-induksi');
    }
}
