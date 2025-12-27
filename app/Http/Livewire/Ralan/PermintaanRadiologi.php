<?php

namespace App\Http\Livewire\Ralan;

use App\Traits\EnkripsiData;
use App\Traits\SwalResponse;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PermintaanRadiologi extends Component
{
    use EnkripsiData, SwalResponse;
    public $noRawat, $klinis, $info, $jns_pemeriksaan = [], $permintaanRad = [], $isCollapsed = true;
    public $select2Count = 1; // Jumlah select2 yang ditampilkan

    protected $rules = [
        'klinis' => 'required',
        'info' => 'required',
        'jns_pemeriksaan' => 'required|array|min:1',
        'jns_pemeriksaan.*' => 'required',
    ];

    protected $messages = [
        'klinis.required' => 'Klinis tidak boleh kosong',
        'info.required' => 'Informasi tambahan tidak boleh kosong',
        'jns_pemeriksaan.required' => 'Jenis pemeriksaan tidak boleh kosong',
        'jns_pemeriksaan.min' => 'Minimal harus ada 1 jenis pemeriksaan',
        'jns_pemeriksaan.*.required' => 'Semua jenis pemeriksaan harus dipilih',
    ];

    public function mount($noRawat)
    {
        $this->noRawat = $noRawat;
    }

    public function hydrate()
    {
        $this->getPermintaanRadiologi();
    }

    public function render()
    {
        return view('livewire.ralan.permintaan-radiologi');
    }

    public function selectedJnsPerawatan($item)
    {
        $this->jns_pemeriksaan = $item;
    }

    public function savePermintaanRadiologi()
    {
        // Filter array untuk validasi (hanya untuk pengecekan, tidak mengubah state)
        $filteredPemeriksaan = array_filter($this->jns_pemeriksaan, function ($value) {
            return $value !== null && $value !== '';
        });

        // Validasi dengan array yang sudah di-filter untuk pengecekan
        // Tapi state asli tidak diubah sampai validasi berhasil
        if (empty($filteredPemeriksaan)) {
            $this->addError('jns_pemeriksaan', 'Minimal harus ada 1 jenis pemeriksaan yang dipilih');
            return;
        }

        $this->validate([
            'klinis' => 'required',
            'info' => 'required',
        ], [
            'klinis.required' => 'Klinis tidak boleh kosong',
            'info.required' => 'Informasi tambahan tidak boleh kosong',
        ]);

        // Filter dan re-index array untuk operasi database (gunakan variabel lokal)
        // Jangan modifikasi state component sampai transaksi berhasil commit
        $processedPemeriksaan = array_values($filteredPemeriksaan);

        try {
            DB::beginTransaction();

            // Ambil status_lanjut dari reg_periksa
            $regPeriksa = DB::table('reg_periksa')
                ->where('no_rawat', $this->noRawat)
                ->select('status_lanjut')
                ->first();

            // Konversi status_lanjut ke lowercase (Ralan -> ralan, Ranap -> ranap)
            $status = $regPeriksa ? strtolower($regPeriksa->status_lanjut) : 'ralan';

            $getNumber = DB::table('permintaan_radiologi')
                ->where('tgl_permintaan', date('Y-m-d'))
                ->selectRaw('ifnull(MAX(CONVERT(RIGHT(noorder,4),signed)),0) as no')
                ->first();

            $lastNumber = substr($getNumber->no, 0, 4);
            $getNextNumber = sprintf('%04s', ($lastNumber + 1));
            $noOrder = 'PR' . date('Ymd') . $getNextNumber;

            DB::table('permintaan_radiologi')
                ->insert([
                    'noorder' => $noOrder,
                    'no_rawat' => $this->noRawat,
                    'tgl_permintaan' => date('Y-m-d'),
                    'jam_permintaan' => date('H:i:s'),
                    'dokter_perujuk' => session()->get('username'),
                    'diagnosa_klinis' =>  $this->klinis,
                    'informasi_tambahan' =>  $this->info,
                    'status' => $status
                ]);

            foreach ($processedPemeriksaan as $pemeriksaan) {
                if ($pemeriksaan) {
                    DB::table('permintaan_pemeriksaan_radiologi')
                        ->insert([
                            'noorder' => $noOrder,
                            'kd_jenis_prw' => $pemeriksaan,
                            'stts_bayar' => 'Belum'
                        ]);
                }
            }
            DB::commit();

            // Setelah commit berhasil, refresh data dan reset form
            // Tidak perlu mengatur $this->jns_pemeriksaan karena resetForm() akan mereset semua field
            $this->getPermintaanRadiologi();
            $this->dispatchBrowserEvent('swal', $this->toastResponse("Permintaan Radiologi berhasil ditambahkan"));
            $this->resetForm();
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();

            // State tidak perlu di-restore karena tidak pernah diubah sebelum commit
            // Semua operasi menggunakan variabel lokal $processedPemeriksaan
            $this->dispatchBrowserEvent('swal', $this->toastResponse("Permintaan Radiologi gagal ditambahkan", 'error'));
        }
    }

    public function getPermintaanRadiologi()
    {
        $this->permintaanRad = DB::table('permintaan_radiologi')
            ->where('no_rawat', $this->noRawat)
            ->get();
    }

    public function collapsed()
    {
        $this->isCollapsed = !$this->isCollapsed;
    }

    public function resetForm()
    {
        $this->reset(['klinis', 'info', 'jns_pemeriksaan']);
        $this->select2Count = 1;
        $this->jns_pemeriksaan = [];
        $this->dispatchBrowserEvent('select2Rad:reset');
    }

    public function addSelect2()
    {
        $this->select2Count++;
        // Pastikan array memiliki cukup elemen
        while (count($this->jns_pemeriksaan) < $this->select2Count) {
            $this->jns_pemeriksaan[] = null;
        }
        $this->dispatchBrowserEvent('select2Rad:add', ['index' => $this->select2Count - 1]);
    }

    public function removeSelect2($index)
    {
        // Validasi: pastikan ada minimal 1 select2
        if ($this->select2Count <= 1) {
            return; // Tidak bisa menghapus jika hanya ada 1
        }

        // Validasi: pastikan index dalam range yang valid (0 sampai select2Count-1)
        if (!is_numeric($index) || $index < 0 || $index >= $this->select2Count) {
            return; // Index tidak valid, abort tanpa mengubah state
        }

        $removedIndex = (int)$index;

        // Pastikan array memiliki cukup elemen sebelum operasi
        // Ini memastikan array selaras dengan select2Count
        while (count($this->jns_pemeriksaan) < $this->select2Count) {
            $this->jns_pemeriksaan[] = null;
        }

        // Hapus nilai dari array berdasarkan index
        if (isset($this->jns_pemeriksaan[$removedIndex])) {
            unset($this->jns_pemeriksaan[$removedIndex]);
        }

        // Re-index array untuk menjaga kontinuitas
        $this->jns_pemeriksaan = array_values($this->jns_pemeriksaan);

        // Kurangi select2Count karena kita menghapus satu select2 dari UI
        // Ini harus dilakukan setelah validasi index untuk memastikan konsistensi
        $this->select2Count--;

        // Pastikan select2Count tidak kurang dari 1 (safety check)
        if ($this->select2Count < 1) {
            $this->select2Count = 1;
        }

        // Pastikan array tetap selaras dengan select2Count setelah penghapusan
        while (count($this->jns_pemeriksaan) < $this->select2Count) {
            $this->jns_pemeriksaan[] = null;
        }

        // Dispatch event dengan index yang dihapus untuk cleanup
        // Setelah re-index, semua select2 perlu di-reinitialize karena index berubah
        $this->dispatchBrowserEvent('select2Rad:remove', [
            'removedIndex' => $removedIndex,
            'newCount' => $this->select2Count
        ]);
    }

    public function setJnsPemeriksaan($index, $value)
    {
        // Pastikan array memiliki cukup elemen
        while (count($this->jns_pemeriksaan) <= $index) {
            $this->jns_pemeriksaan[] = null;
        }
        $this->jns_pemeriksaan[$index] = $value;
    }

    public function getDetailPemeriksaan($noOrder)
    {
        return DB::table('permintaan_pemeriksaan_radiologi')
            ->join('jns_perawatan_radiologi', 'permintaan_pemeriksaan_radiologi.kd_jenis_prw', '=', 'jns_perawatan_radiologi.kd_jenis_prw')
            ->where('permintaan_pemeriksaan_radiologi.noorder', $noOrder)
            ->select('jns_perawatan_radiologi.*')
            ->get();
    }

    public function deletePermintaanRadiologi($noOrder)
    {
        try {
            DB::beginTransaction();
            DB::table('permintaan_radiologi')
                ->where('noorder', $noOrder)
                ->delete();

            $this->getPermintaanRadiologi();
            DB::commit();
            $this->dispatchBrowserEvent('swal', $this->toastResponse("Permintaan Radiologi berhasil dihapus"));
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            $this->dispatchBrowserEvent('swal', $this->toastResponse("Permintaan Radiologi gagal dihapus", 'error'));
        }
    }
}
