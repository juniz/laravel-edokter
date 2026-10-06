<?php

namespace App\Http\Livewire\Ranap;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Carbon;

class LapTindakanVentilator extends Component
{
    use LivewireAlert;

    public $no_rawat;
    public $kd_dokter;
    public $nip;
    public $tanggalpemasangan;
    public $tanggalpelepasan;
    public $durasi;
    public $diagnosa_pra_tindakan;
    public $diagnosa_pasca_tindakan;
    public $uraian;
    public $hasil;

    public $data = [];
    public $dokterList = [];
    public $petugasList = [];
    public $modeEdit = false;
    public $editKey = null; // ['no_rawat', 'tanggalpemasangan', 'tanggalpelepasan']

    protected $listeners = ['hapusLapVentilator' => 'hapus'];

    public function render()
    {
        return view('livewire.ranap.lap-tindakan-ventilator');
    }

    public function mount($noRawat)
    {
        $this->no_rawat  = $noRawat;
        $this->kd_dokter = session()->get('username');
    }

    public function hydrate()
    {
        $this->getData();
        $this->loadDependencies();
    }

    public function getData()
    {
        $this->data = DB::table('laporan_tindakan_ventilator as ltv')
            ->leftJoin('dokter as d', 'ltv.kd_dokter', '=', 'd.kd_dokter')
            ->leftJoin('petugas as p', 'ltv.nip', '=', 'p.nip')
            ->where('ltv.no_rawat', $this->no_rawat)
            ->orderBy('ltv.tanggalpemasangan', 'desc')
            ->select(
                'ltv.*',
                'd.nm_dokter',
                'p.nama as nm_petugas'
            )
            ->get();
    }

    public function loadDependencies()
    {
        // Dropdown dokter & petugas dihapus dari form
    }

    public function updatedTanggalpemasangan()
    {
        $this->hitungDurasi();
    }

    public function updatedTanggalpelepasan()
    {
        $this->hitungDurasi();
    }

    private function hitungDurasi()
    {
        if ($this->tanggalpemasangan && $this->tanggalpelepasan) {
            try {
                $pasang = Carbon::parse($this->tanggalpemasangan);
                $lepas  = Carbon::parse($this->tanggalpelepasan);
                if ($lepas->greaterThan($pasang)) {
                    $this->durasi = (string) $pasang->diffInHours($lepas);
                } else {
                    $this->durasi = '0';
                }
            } catch (\Exception $e) {
                $this->durasi = null;
            }
        }
    }

    public function edit($pemasangan)
    {
        $data = DB::table('laporan_tindakan_ventilator')
            ->where('no_rawat', $this->no_rawat)
            ->where('tanggalpemasangan', $pemasangan)
            ->first();

        if (!$data) {
            $this->alert('warning', 'Data tidak ditemukan', [
                'position' => 'center', 'timer' => '', 'toast' => false,
                'confirmButtonText' => 'Ok', 'showConfirmButton' => true,
            ]);
            return;
        }

        if ($data->kd_dokter != session()->get('username')) {
            $this->alert('warning', 'Akses ditolak', [
                'position' => 'center', 'timer' => '', 'toast' => false,
                'text' => 'Anda tidak memiliki akses untuk mengubah data ini',
                'confirmButtonText' => 'Ok', 'showConfirmButton' => true,
            ]);
            return;
        }

        $this->editKey              = $pemasangan;
        $this->kd_dokter            = $data->kd_dokter;
        $this->nip                  = $data->nip;
        $this->tanggalpemasangan    = Carbon::parse($data->tanggalpemasangan)->format('Y-m-d\TH:i');
        $this->tanggalpelepasan     = Carbon::parse($data->tanggalpelepasan)->format('Y-m-d\TH:i');
        $this->durasi               = $data->durasi;
        $this->diagnosa_pra_tindakan  = $data->diagnosa_pra_tindakan;
        $this->diagnosa_pasca_tindakan = $data->diagnosa_pasca_tindakan;
        $this->uraian               = $data->uraian;
        $this->hasil                = $data->hasil;
        $this->modeEdit             = true;
    }

    public function confirmHapus($pemasangan)
    {
        $data = DB::table('laporan_tindakan_ventilator')
            ->where('no_rawat', $this->no_rawat)
            ->where('tanggalpemasangan', $pemasangan)
            ->first();

        if (!$data) {
            $this->alert('warning', 'Data tidak ditemukan', [
                'position' => 'center', 'timer' => '', 'toast' => false,
                'confirmButtonText' => 'Ok', 'showConfirmButton' => true,
            ]);
            return;
        }

        if ($data->kd_dokter != session()->get('username')) {
            $this->alert('warning', 'Akses ditolak', [
                'position' => 'center', 'timer' => '', 'toast' => false,
                'text' => 'Anda tidak memiliki akses untuk menghapus data ini',
                'confirmButtonText' => 'Ok', 'showConfirmButton' => true,
            ]);
            return;
        }

        $this->editKey = $pemasangan;
        $this->confirm('Apakah anda yakin ingin menghapus data ini?', [
            'onConfirmed'      => 'hapusLapVentilator',
            'cancelButtonText' => 'Batal',
        ]);
    }

    public function hapus()
    {
        try {
            $cek = DB::table('laporan_tindakan_ventilator')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggalpemasangan', $this->editKey)
                ->first();

            if (!$cek) {
                $this->alert('warning', 'Data tidak ditemukan', [
                    'position' => 'center', 'timer' => '', 'toast' => false,
                    'confirmButtonText' => 'Ok', 'showConfirmButton' => true,
                ]);
                return;
            }

            DB::table('laporan_tindakan_ventilator')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggalpemasangan', $this->editKey)
                ->delete();

            $this->getData();
            $this->alert('success', 'Berhasil hapus laporan tindakan ventilator');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' => 'center', 'timer' => '', 'toast' => false,
                'text' => $e->getMessage(),
                'confirmButtonText' => 'Ok', 'showConfirmButton' => true,
            ]);
        }
    }

    public function resetInput()
    {
        $this->reset([
            'tanggalpemasangan', 'tanggalpelepasan', 'durasi',
            'diagnosa_pra_tindakan', 'diagnosa_pasca_tindakan',
            'uraian', 'hasil', 'nip',
        ]);
        $this->kd_dokter = session()->get('username');
        $this->modeEdit  = false;
        $this->editKey   = null;
    }

    public function simpan()
    {
        $this->kd_dokter = session()->get('username');

        $this->validate([
            'tanggalpemasangan'      => 'required|date',
            'tanggalpelepasan'       => 'required|date|after:tanggalpemasangan',
            'kd_dokter'              => 'required',
            'diagnosa_pra_tindakan'  => 'required|max:50',
            'diagnosa_pasca_tindakan'=> 'required|max:50',
            'uraian'                 => 'required',
            'hasil'                  => 'required',
        ], [
            'tanggalpemasangan.required'       => 'Tanggal pemasangan wajib diisi!',
            'tanggalpemasangan.date'           => 'Format tanggal pemasangan tidak valid!',
            'tanggalpelepasan.required'        => 'Tanggal pelepasan wajib diisi!',
            'tanggalpelepasan.date'            => 'Format tanggal pelepasan tidak valid!',
            'tanggalpelepasan.after'           => 'Tanggal pelepasan harus setelah tanggal pemasangan!',
            'kd_dokter.required'               => 'Dokter wajib dipilih!',
            'diagnosa_pra_tindakan.required'   => 'Diagnosa pra tindakan wajib diisi!',
            'diagnosa_pra_tindakan.max'        => 'Diagnosa pra tindakan maks 50 karakter!',
            'diagnosa_pasca_tindakan.required' => 'Diagnosa pasca tindakan wajib diisi!',
            'diagnosa_pasca_tindakan.max'      => 'Diagnosa pasca tindakan maks 50 karakter!',
            'uraian.required'                  => 'Uraian wajib diisi!',
            'hasil.required'                   => 'Hasil wajib diisi!',
        ]);

        $pasang = Carbon::parse($this->tanggalpemasangan)->format('Y-m-d H:i:s');
        $lepas  = Carbon::parse($this->tanggalpelepasan)->format('Y-m-d H:i:s');
        $durasi = $this->durasi;

        $payload = [
            'no_rawat'               => $this->no_rawat,
            'tanggalpemasangan'      => $pasang,
            'tanggalpelepasan'       => $lepas,
            'durasi'                 => $durasi,
            'kd_dokter'              => $this->kd_dokter,
            'nip'                    => $this->nip ?: null,
            'diagnosa_pra_tindakan'  => $this->diagnosa_pra_tindakan,
            'diagnosa_pasca_tindakan'=> $this->diagnosa_pasca_tindakan,
            'uraian'                 => $this->uraian,
            'hasil'                  => $this->hasil,
        ];

        try {
            if ($this->modeEdit) {
                DB::table('laporan_tindakan_ventilator')
                    ->where('no_rawat', $this->no_rawat)
                    ->where('tanggalpemasangan', $this->editKey)
                    ->update([
                        'tanggalpelepasan'       => $lepas,
                        'durasi'                 => $durasi,
                        'kd_dokter'              => $this->kd_dokter,
                        'nip'                    => $this->nip ?: null,
                        'diagnosa_pra_tindakan'  => $this->diagnosa_pra_tindakan,
                        'diagnosa_pasca_tindakan'=> $this->diagnosa_pasca_tindakan,
                        'uraian'                 => $this->uraian,
                        'hasil'                  => $this->hasil,
                    ]);

                $this->resetInput();
                $this->getData();
                $this->alert('success', 'Berhasil ubah laporan tindakan ventilator');
            } else {
                // Check duplicate primary key
                $exists = DB::table('laporan_tindakan_ventilator')
                    ->where('no_rawat', $this->no_rawat)
                    ->where('tanggalpemasangan', $pasang)
                    ->where('tanggalpelepasan', $lepas)
                    ->exists();

                if ($exists) {
                    $this->alert('warning', 'Data sudah ada', [
                        'position' => 'center', 'timer' => '', 'toast' => false,
                        'text' => 'Kombinasi no rawat, tanggal pemasangan, dan tanggal pelepasan sudah ada.',
                        'confirmButtonText' => 'Ok', 'showConfirmButton' => true,
                    ]);
                    return;
                }

                DB::table('laporan_tindakan_ventilator')->insert($payload);

                $this->resetInput();
                $this->getData();
                $this->alert('success', 'Berhasil input laporan tindakan ventilator');
            }
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' => 'center', 'timer' => '', 'toast' => false,
                'text' => $e->getMessage(),
                'confirmButtonText' => 'Ok', 'showConfirmButton' => true,
            ]);
        }
    }
}
