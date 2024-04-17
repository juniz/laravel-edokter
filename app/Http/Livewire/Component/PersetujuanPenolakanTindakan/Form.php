<?php

namespace App\Http\Livewire\Component\PersetujuanPenolakanTindakan;

use Illuminate\Support\Facades\App;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class Form extends Component
{
    use LivewireAlert;
    public $no_rawat;
    public $diagnosa;
    public $tindakan_dokter;
    public $indikasi_tindakan;
    public $tata_cara;
    public $tujuan;
    public $risiko;
    public $komplikasi;
    public $progonis;
    public $alternatif;
    public $lain;
    public $biaya = 0;
    public $hubungan = 'Diri Sendiri';
    public $alamat;
    public $alasan;
    public $tgl_lahir;
    public $no_hp;
    public $penerima_informasi;
    public $jk = 'L';
    public $umur;
    public $saksi2;
    public $saksi1;
    public $nopernyataan;

    protected $listeners = ['hapusPersetujuanTindakanMedis' => 'hapus'];


    public function render()
    {
        return view('livewire.component.persetujuan-penolakan-tindakan.form');
    }

    public function updatedNoRawat()
    {
        $this->reset([
            'diagnosa',
            'tindakan_dokter',
            'indikasi_tindakan',
            'tata_cara',
            'tujuan',
            'risiko',
            'komplikasi',
            'progonis',
            'alternatif',
            'lain',
            'biaya',
            'hubungan',
            'alamat',
            'alasan',
            'tgl_lahir',
            'no_hp',
            'penerima_informasi',
            'jk',
            'umur',
            'saksi2',
            'saksi1',
        ]);
        $this->umur = 0;
        $this->hubungan = 'Diri Sendiri';
        $this->jk = 'L';
        try {
            $pasien = DB::table('pasien')
                ->join('reg_periksa', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
                ->where('reg_periksa.no_rawat', $this->no_rawat)
                ->select('pasien.*')
                ->first();
            $data = DB::table('persetujuan_penolakan_tindakan')
                ->where('no_rawat', $this->no_rawat)
                ->first();
            if ($data) {
                $this->diagnosa = $data->diagnosa;
                $this->tindakan_dokter = $data->tindakan;
                $this->indikasi_tindakan = $data->indikasi_tindakan;
                $this->tata_cara = $data->tata_cara;
                $this->tujuan = $data->tujuan;
                $this->risiko = $data->risiko;
                $this->komplikasi = $data->komplikasi;
                $this->progonis = $data->prognosis;
                $this->alternatif = $data->alternatif_dan_risikonya;
                $this->lain = $data->lain_lain;
                $this->biaya = $data->biaya;
                $this->hubungan = $data->hubungan_penerima_informasi;
                $this->alamat = $data->alamat_penerima_informasi;
                $this->alasan = $data->alasan_diwakilkan_penerima_informasi;
                $this->tgl_lahir = $data->tanggal_lahir_penerima_informasi;
                $this->no_hp = $data->no_hp;
                $this->penerima_informasi = $data->penerima_informasi;
                $this->jk = $data->jk_penerima_informasi;
                $this->umur = $data->umur_penerima_informasi;
                $this->saksi2 = $data->nip;
                $this->saksi1 = $data->saksi_keluarga;
                $this->nopernyataan = $data->no_pernyataan;
            } else {
                $this->penerima_informasi = $pasien->nm_pasien;
                $this->alamat = $pasien->alamat;
                $this->no_hp = $pasien->no_tlp;
                $this->tgl_lahir = Carbon::parse($pasien->tgl_lahir)->format('Y-m-d');
                $this->jk = $pasien->jk == 'L' ? 'L' : 'P';
                $this->hitungUmur();
            }
        } catch (\Exception $e) {
            $this->alert('error', $e->getMessage());
        }
    }

    public function updatedSaksi2()
    {
        $this->emit('saksi2', $this->saksi2);
    }

    public function updatedTglLahir()
    {
        $this->hitungUmur();
    }

    public function hitungUmur()
    {
        $tgl_lahir = Carbon::parse($this->tgl_lahir);
        $now = Carbon::now();
        $this->umur = $now->diffInYears($tgl_lahir);
    }

    public function simpan()
    {
        // dd($this->noRawat, $this->diagnosa, $this->tindakan_dokter, $this->indikasi_tindakan, $this->tata_cara, $this->tujuan, $this->risiko, $this->komplikasi, $this->progonis, $this->alternatif, $this->lain, $this->biaya, $this->hubungan, $this->alamat, $this->alasan, $this->tgl_lahir, $this->no_hp, $this->penerima_informasi, $this->jk, $this->umur, $this->saksi2, $this->saksi1);
        try {
            $count = DB::table('persetujuan_penolakan_tindakan')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', Carbon::now()->format('Y-m-d'))
                ->count();

            $no = 'PM' . Carbon::now()->format('Ymd') . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
            DB::table('persetujuan_penolakan_tindakan')
                ->upsert([
                    'no_pernyataan' => $no,
                    'kd_dokter' => session()->get('username'),
                    'no_rawat' => $this->no_rawat,
                    'diagnosa' => $this->diagnosa ?? '-',
                    'tindakan' => $this->tindakan_dokter ?? '-',
                    'indikasi_tindakan' => $this->indikasi_tindakan ?? '-',
                    'tata_cara' => $this->tata_cara ?? '-',
                    'tujuan' => $this->tujuan ?? '-',
                    'risiko' => $this->risiko ?? '-',
                    'komplikasi' => $this->komplikasi ?? '-',
                    'prognosis' => $this->progonis ?? '-',
                    'alternatif_dan_risikonya' => $this->alternatif ?? '-',
                    'lain_lain' => $this->lain ?? '-',
                    'biaya' => $this->biaya ?? 0,
                    'hubungan_penerima_informasi' => $this->hubungan ?? '-',
                    'alamat_penerima_informasi' => $this->alamat ?? '-',
                    'alasan_diwakilkan_penerima_informasi' => $this->alasan ?? '-',
                    'tanggal_lahir_penerima_informasi' => $this->tgl_lahir ?? Carbon::now()->format('Y-m-d'),
                    'no_hp' => $this->no_hp ?? '-',
                    'penerima_informasi' => $this->penerima_informasi ?? '-',
                    'jk_penerima_informasi' => $this->jk ?? '-',
                    'umur_penerima_informasi' => $this->umur ?? '-',
                    'nip' => $this->saksi2 ?? '-',
                    'saksi_keluarga' => $this->saksi1 ?? '-',
                ], ['no_rawat', 'no_pernyataan'], ['diagnosa', 'tindakan', 'indikasi_tindakan', 'tata_cara', 'tujuan', 'risiko', 'komplikasi', 'prognosis', 'alternatif_dan_risikonya', 'lain_lain', 'biaya', 'hubungan_penerima_informasi', 'alamat_penerima_informasi', 'alasan_diwakilkan_penerima_informasi', 'tanggal_lahir_penerima_informasi', 'no_hp', 'penerima_informasi', 'jk_penerima_informasi', 'umur_penerima_informasi', 'nip', 'saksi_keluarga']);

            $this->nopernyataan = $no;
            // $this->emit('closeModalPersetujuanTindakan');
            $this->alert('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal menyimpan data', [
                'timer' =>  '',
                'toast' =>  false,
                'text' =>  App::environment('local') ? $e->getMessage() : 'Terjadi kesalahan',
                'showCancelButton' =>  false,
                'showConfirmButton' =>  false,
                'cancelButtonText' =>  'Tutup',
                'confirmButtonText' =>  'Ya',
                'showCloseButton' =>  false,
            ]);
        }
    }

    public function confirmHapus()
    {
        $this->confirm('Apakah anda yakin ingin menghapus data ini?', [
            'onConfirmed' => 'hapusPersetujuanTindakanMedis',
            'cancelButtonText' => 'Batal',
            'confirmButtonText' => 'Hapus',
        ]);
    }

    public function hapus()
    {
        try {
            DB::table('persetujuan_penolakan_tindakan')
                ->where('no_rawat', $this->no_rawat)
                ->delete();
            $this->alert('success', 'Data berhasil dihapus');
            $this->emit('closeModalPersetujuanTindakan');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal menghapus data', [
                'timer' =>  '',
                'toast' =>  false,
                'text' =>  App::environment('local') ? $e->getMessage() : 'Terjadi kesalahan',
                'showCancelButton' =>  false,
                'showConfirmButton' =>  false,
                'cancelButtonText' =>  'Tutup',
                'confirmButtonText' =>  'Ya',
                'showCloseButton' =>  false,
            ]);
        }
    }

    public function fotoPersetujuan()
    {
        $this->dispatchBrowserEvent('ambilFotoPersetujuan', ['no_rawat' => $this->no_rawat, 'nopernyataan' => $this->nopernyataan]);
        // return redirect()->to('/persetujuan-penolakan-tindakan')->with(['no_rawat' => $this->no_rawat, 'nopernyataan' => $this->nopernyataan]);
    }
}
