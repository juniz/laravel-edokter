<?php

namespace App\Http\Livewire\Ranap;

use Illuminate\Support\Arr;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Carbon;

class LapOperasi extends Component
{
    use LivewireAlert;
    public $no_rawat, $tanggal_operasi, $tanggal_selesai, $kd_dokter, $diagnosa_pra_bedah, $diagnosa_pasca_bedah, $uraian_bedah, $tindakan_bedah;
    public $jenis_operasi = 'Kecil', $jenis_anestesi = 'Umum', $histo = 'Tidak', $kd_ruang_ok = 'O1';
    public $tglOperasi, $tglSelesai;
    public $data = [], $modeEdit = false;
    protected $listeners = ['hapusLapOperasi' => 'hapus', 'pilihTemplateOperasi' => 'pilihTemplateOperasi'];
    public function render()
    {
        $jns_operasi = ['Kecil', 'Sedang', 'Besar', 'Khusus', 'Bersih', 'Bersih Terkontaminasi', 'Terkontaminasi', 'Kotor', 'Cito', 'Elektif'];
        $jns_anestesi = ['Umum', 'Reguler', 'Lokal'];
        $histopatologi = ['Tidak', 'Ya'];
        $kd_ok = DB::table('ruang_ok')->get();
        return view('livewire.ranap.lap-operasi', compact('jns_operasi', 'jns_anestesi', 'histopatologi', 'kd_ok'));
    }

    public function mount($noRawat)
    {
        $this->no_rawat = $noRawat;
        $this->tanggal_operasi = Carbon::now()->format('Y-m-d H:i:s');
        $this->tanggal_selesai = Carbon::now()->format('Y-m-d H:i:s');
        $this->kd_dokter = session()->get('username');
    }

    public function hydrate()
    {
        $this->getData();
    }


    public function getData()
    {
        $this->data = DB::table('laporan_operasi_detail')
            ->where('no_rawat', $this->no_rawat)
            ->get();
    }

    public function pilihTemplateOperasi($id)
    {
        $data = DB::table('template_laporan_operasi')
            ->where('no_template', $id)
            ->first();
        $this->diagnosa_pra_bedah = $data->diagnosa_preop;
        $this->diagnosa_pasca_bedah = $data->diagnosa_postop;
        $this->uraian_bedah = $data->laporan_operasi;

        $this->dispatchBrowserEvent('closeModalTemplateOperasi');
    }

    public function confirmHapus($tglOperasi, $tglSelesai)
    {
        $this->tglOperasi = $tglOperasi;
        $this->tglSelesai = $tglSelesai;
        $data = DB::table('laporan_operasi_detail')
            ->where('no_rawat', $this->no_rawat)
            ->where('tanggal_operasi', $tglOperasi)
            ->where('tanggal_selesai', $tglSelesai)
            ->first();
        if ($data->kd_dokter_bedah != session()->get('username')) {

            $this->alert('warning', 'Gagal', [
                'position' =>  'center',
                'timer' =>  '',
                'toast' =>  false,
                'text' =>  'Anda tidak memiliki akses untuk menghapus data ini',
                'confirmButtonText' =>  'Ok',
                'showConfirmButton' =>  true,
            ]);
            return;
        } else {

            $this->confirm('Apakah anda yakin ingin menghapus data ini?', [
                'onConfirmed' => 'hapusLapOperasi',
                'cancelButtonText' => 'Batal',
            ]);
        }
    }

    public function hapus()
    {
        $noRawat = $this->no_rawat;
        $tglOperasi = $this->tglOperasi;
        $tglSelesai = $this->tglSelesai;
        try {
            $cek = DB::table('laporan_operasi_detail')
                ->where('no_rawat', $noRawat)
                ->where('tanggal_operasi', $tglOperasi)
                ->where('tanggal_selesai', $tglSelesai)
                ->first();
            if ($cek->kd_dokter_bedah != session()->get('username')) {
                $this->alert('warning', 'Gagal', [
                    'position' =>  'center',
                    'timer' =>  '',
                    'toast' =>  false,
                    'text' =>  'Anda tidak memiliki akses untuk menghapus data ini',
                    'confirmButtonText' =>  'Ok',
                    'showConfirmButton' =>  true,
                ]);
                return;
            }
            DB::table('laporan_operasi_detail')
                ->where('no_rawat', $noRawat)
                ->where('tanggal_operasi', $tglOperasi)
                ->where('tanggal_selesai', $tglSelesai)
                ->delete();

            $this->getData();
            $this->alert('success', 'Berhasil hapus laporan operasi');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' =>  'center',
                'timer' =>  '',
                'toast' =>  false,
                'text' =>  $e->getMessage(),
                'confirmButtonText' =>  'Ok',
                'showConfirmButton' =>  true,
            ]);
        }
    }

    public function edit($tglOperasi, $tglSelesai)
    {
        // dd($tglOperasi, $tglSelesai);
        $data = DB::table('laporan_operasi_detail')
            ->where('no_rawat', $this->no_rawat)
            ->where('tanggal_operasi', $tglOperasi)
            ->first();

        if ($data->kd_dokter_bedah == session()->get('username')) {

            $this->tanggal_operasi = $data->tanggal_operasi;
            $this->tanggal_selesai = $data->tanggal_selesai;
            $this->diagnosa_pra_bedah = $data->diagnosa_pra_bedah;
            $this->diagnosa_pasca_bedah = $data->diagnosa_pasca_bedah;
            $this->uraian_bedah = $data->uraian_bedah;
            $this->tindakan_bedah = $data->tindakan_bedah;

            $this->modeEdit = true;
            $this->emit('editLapOperasi');
        } else {

            $this->alert('warning', 'Gagal', [
                'position' =>  'center',
                'timer' =>  '',
                'toast' =>  false,
                'text' =>  'Anda tidak memiliki akses untuk mengubah data ini',
                'confirmButtonText' =>  'Ok',
                'showConfirmButton' =>  true,
            ]);
        }
    }

    public function resetInput()
    {
        $this->reset(['tanggal_operasi', 'tanggal_selesai', 'diagnosa_pra_bedah', 'diagnosa_pasca_bedah', 'uraian_bedah', 'tindakan_bedah']);
        $this->modeEdit = false;
        $this->emit('resetInput');
    }

    public function simpan()
    {
        // dd($this->tanggal_operasi);
        $this->validate([
            'tanggal_operasi' => 'required',
            'tanggal_selesai' => 'required',
            'diagnosa_pra_bedah' => 'required',
            'diagnosa_pasca_bedah' => 'required',
            'uraian_bedah' => 'required',
            'tindakan_bedah' => 'required',
        ], [
            'tanggal_operasi.required' => 'Tanggal Operasi tidak boleh kosong!',
            'tanggal_selesai.required' => 'Tanggal Selesai tidak boleh kosong!',
            'diagnosa_pra_bedah.required' => 'Diagnosa Pra Bedah tidak boleh kosong!',
            'diagnosa_pasca_bedah.required' => 'Diagnosa Pasca Bedah tidak boleh kosong!',
            'uraian_bedah.required' => 'Uraian Bedah tidak boleh kosong!',
            'tindakan_bedah.required' => 'Tindakan Bedah tidak boleh kosong!',
        ]);

        $start = Carbon::parse($this->tanggal_operasi);
        $end = Carbon::parse($this->tanggal_selesai);
        $diff = $start->diffInDays($end);
        $data = [
            'no_rawat' => $this->no_rawat,
            'tanggal_operasi' => $this->tanggal_operasi,
            'tanggal_selesai' => $this->tanggal_selesai,
            'kd_dokter_bedah' => $this->kd_dokter,
            'diagnosa_pra_bedah' => $this->diagnosa_pra_bedah,
            'diagnosa_pasca_bedah' => $this->diagnosa_pasca_bedah,
            'uraian_bedah' => $this->uraian_bedah,
            'lama_operasi' => $diff + 1,
            'tindakan_bedah' => $this->tindakan_bedah,
            'jenis_operasi' => $this->jenis_operasi,
            'jenis_operasi' => 'Bersih',
            'sifat_operasi' => 'Elektif',
            'kategori_anastesi' => 'Umum',
            'jenis_anestesi' => $this->jenis_anestesi,
            'histopatologi' => $this->histo,
            'nip_asisten_bedah' => '-',
            'kd_ruang_ok' => $this->kd_ruang_ok,
            'nip_instrumen' => '-',
            'kd_ahli_anestesi' => '-',
            'obat_anestesi' => '-',
            'jumlah_pendarahan' => '-',
            'jumlah_cairan_transfusi' => '-',
            'macam_jaringan' => '-',
        ];

        try {
            if ($this->modeEdit) {
                $dataEdit = Arr::except($data, ['no_rawat', 'kd_dokter_bedah']);
                DB::table('laporan_operasi_detail')
                    ->where('no_rawat', $this->no_rawat)
                    ->where('tanggal_operasi', $this->tanggal_operasi)
                    ->where('tanggal_selesai', $this->tanggal_selesai)
                    ->where('kd_dokter_bedah', $this->kd_dokter)
                    ->update($dataEdit);

                $this->resetInput();
                $this->getData();
                $this->alert('success', 'Berhasil ubah laporan operasi');
            } else {
                DB::table('laporan_operasi_detail')
                    ->insert($data);

                $this->resetInput();
                $this->getData();
                $this->alert('success', 'Berhasil input laporan operasi');
            }
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' =>  'center',
                'timer' =>  '',
                'toast' =>  false,
                'text' =>  $e->getMessage(),
                'confirmButtonText' =>  'Ok',
                'showConfirmButton' =>  true,
            ]);
        }
    }
}
