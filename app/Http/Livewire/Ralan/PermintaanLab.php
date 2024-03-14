<?php

namespace App\Http\Livewire\Ralan;

use App\Traits\EnkripsiData;
use App\Traits\SwalResponse;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PermintaanLab extends Component
{
    use EnkripsiData, SwalResponse;
    public $noRawat, $klinis, $info, $jns_pemeriksaan = [], $permintaanLab = [], $isCollapsed = true, $isExpand = false;

    protected $rules = [
        'klinis' => 'required',
        'info' => 'required',
        'jns_pemeriksaan' => 'required',
    ];

    protected $messages = [
        'klinis.required' => 'Klinis tidak boleh kosong',
        'info.required' => 'Informasi tambahan tidak boleh kosong',
        'jns_pemeriksaan.required' => 'Jenis pemeriksaan tidak boleh kosong',
    ];

    protected $listeners = ['deletePermintaanLab'];

    public function mount($noRawat)
    {
        $this->noRawat = $noRawat;
    }

    public function hydrate()
    {
        $this->getPermintaanLab();
    }

    public function render()
    {
        return view('livewire.ralan.permintaan-lab');
    }

    public function selectedJnsPerawatan($item)
    {
        $this->jns_pemeriksaan = $item;
    }

    public function savePermintaanLab()
    {
        $this->validate();

        try {
            DB::beginTransaction();
            $getNumber = DB::table('permintaan_lab')
                ->where('tgl_permintaan', date('Y-m-d'))
                ->selectRaw('ifnull(MAX(CONVERT(RIGHT(noorder,4),signed)),0) as no')
                ->first();

            $lastNumber = substr($getNumber->no, 0, 4);
            $getNextNumber = sprintf('%04s', ($lastNumber + 1));
            $noOrder = 'PL' . date('Ymd') . $getNextNumber;

            DB::table('permintaan_lab')
                ->insert([
                    'noorder' => $noOrder,
                    'no_rawat' => $this->noRawat,
                    'tgl_permintaan' => date('Y-m-d'),
                    'jam_permintaan' => date('H:i:s'),
                    'dokter_perujuk' => session()->get('username'),
                    'diagnosa_klinis' =>  $this->klinis,
                    'informasi_tambahan' =>  $this->info,
                    'status' => 'ralan'
                ]);

            foreach ($this->jns_pemeriksaan as $pemeriksaan) {
                DB::table('permintaan_pemeriksaan_lab')
                    ->insert([
                        'noorder' => $noOrder,
                        'kd_jenis_prw' => $pemeriksaan,
                        'stts_bayar' => 'Belum'
                    ]);

                $template = DB::table('template_laboratorium')->where('kd_jenis_prw', $pemeriksaan)->select('id_template')->get();
                foreach ($template as $temp) {
                    DB::table('permintaan_detail_permintaan_lab')->insert([
                        'noorder'   =>  $noOrder,
                        'kd_jenis_prw'  =>  $pemeriksaan,
                        'id_template'   =>  $temp->id_template,
                        'stts_bayar'    =>  'Belum'
                    ]);
                }
            }
            DB::commit();
            $this->getPermintaanLab();
            $this->dispatchBrowserEvent('swal', $this->toastResponse('Permintaan Lab berhasil ditambahkan'));
            $this->emit('select2Lab');
            $this->resetForm();
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();

            $this->dispatchBrowserEvent('swal', $this->toastResponse($ex->getMessage() ?? 'Permintaan Lab gagal ditambahkan', 'error'));
        }
    }

    public function getPermintaanLab()
    {
        $this->permintaanLab = DB::table('permintaan_lab')
            ->where('no_rawat', $this->noRawat)
            ->get();
    }

    public function collapsed()
    {
        $this->isCollapsed = !$this->isCollapsed;
    }

    public function expanded()
    {
        $this->isExpand = !$this->isExpand;
    }

    public function resetForm()
    {
        $this->reset(['klinis', 'info', 'jns_pemeriksaan']);
        $this->dispatchBrowserEvent('select2Lab:reset');
    }

    public function getDetailPemeriksaan($noOrder)
    {
        return DB::table('permintaan_pemeriksaan_lab')
            ->join('jns_perawatan_lab', 'permintaan_pemeriksaan_lab.kd_jenis_prw', '=', 'jns_perawatan_lab.kd_jenis_prw')
            ->where('permintaan_pemeriksaan_lab.noorder', $noOrder)
            ->select('jns_perawatan_lab.*')
            ->get();
    }

    public function konfirmasiHapus($id)
    {
        $this->dispatchBrowserEvent('swal:confirm', [
            'title' => 'Konfirmasi Hapus Data',
            'text' => 'Apakah anda yakin ingin menghapus data ini?',
            'type' => 'warning',
            'confirmButtonText' => 'Ya, Hapus',
            'cancelButtonText' => 'Tidak',
            'function' => 'deletePermintaanLab',
            'params' => [$id]
        ]);
    }

    public function deletePermintaanLab($noOrder)
    {
        try {
            DB::beginTransaction();
            DB::table('permintaan_lab')
                ->where('noorder', $noOrder)
                ->delete();

            $this->getPermintaanLab();
            DB::commit();
            $this->dispatchBrowserEvent('swal', $this->toastResponse('Permintaan Lab berhasil dihapus'));
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            $this->dispatchBrowserEvent('swal', $this->toastResponse($ex->getMessage() ?? 'Permintaan Lab gagal dihapus', 'error'));
        }
    }
}
