<?php

namespace App\Http\Livewire\Ranap;

use App\Traits\SwalResponse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Gemini\Laravel\Facades\Gemini;

class Pemeriksaan extends Component
{
    use SwalResponse, LivewireAlert;
    public $listPemeriksaan, $isCollapsed = false, $noRawat, $noRm, $isMaximized = true, $keluhan, $pemeriksaan, $penilaian, $instruksi, $rtl, $alergi, $suhu, $berat, $tinggi, $tensi, $nadi, $respirasi, $evaluasi, $gcs, $kesadaran = 'Compos Mentis', $lingkar, $spo2;
    public $tgl, $jam;
    public $statistikData = [];
    public $listeners = ['refreshData' => '$refresh', 'hapusPemeriksaan' => 'hapus', 'openModalStatistik' => 'openModalStatistik'];
    public $collapsedCards = [];

    public function mount($noRawat, $noRm)
    {
        $this->noRawat = $noRawat;
        $this->noRm = $noRm;
        if (!$this->isCollapsed) {
            $this->getPemeriksaan();
            $this->getListPemeriksaan();
        }
    }

    public function openModal()
    {
        $this->emit('openModalRehabMedik');
    }

    public function render()
    {
        return view('livewire.ranap.pemeriksaan');
    }

    public function geminiSoap()
    {
        try {
            $resume = DB::table('resume_pasien')
                ->where('no_rawat', $this->noRawat)
                ->select('diagnosa_utama')
                ->first();

            $promp = 'Objek Pasien dengan dengan subjek MUAL KADANG BATUK';
            $result = Gemini::geminiPro()->generateContent($promp);

            $hasil = $result->text();
            $this->alert('info', 'AI Response', [
                'position' =>  'center',
                'timer' =>  5000,
                'toast' =>  false,
                'text' =>  $hasil,
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal memproses AI', [
                'position' =>  'center',
                'timer' =>  3000,
                'toast' =>  false,
                'text' =>  $e->getMessage(),
            ]);
        }
    }

    public function hydrate()
    {
        $this->getListPemeriksaan();
    }

    public function getListPemeriksaan()
    {
        $this->listPemeriksaan = DB::table('pemeriksaan_ranap')
            ->join('pegawai', 'pemeriksaan_ranap.nip', '=', 'pegawai.nik')
            ->where('no_rawat', $this->noRawat)
            ->select('pemeriksaan_ranap.*', 'pegawai.nama')
            ->orderByDesc('tgl_perawatan')
            ->orderByDesc('jam_rawat')
            ->get();
    }

    public function collapsed()
    {
        $this->isCollapsed = !$this->isCollapsed;
    }

    public function expanded()
    {
        $this->isMaximized = !$this->isMaximized;
    }

    public function getPemeriksaan()
    {
        $data = DB::table('reg_periksa')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('pemeriksaan_ranap', 'reg_periksa.no_rawat', '=', 'pemeriksaan_ranap.no_rawat')
            ->where('pasien.no_rkm_medis', $this->noRm)
            ->where('pemeriksaan_ranap.alergi', '<>', 'Tidak Ada')
            ->select('pemeriksaan_ranap.alergi')
            ->first();

        $pemeriksaan = DB::table('pemeriksaan_ranap')
            ->where('no_rawat', $this->noRawat)
            ->where('nip', session()->get('username'))
            ->orderBy('tgl_perawatan', 'desc')
            ->orderBy('jam_rawat', 'desc')
            ->first();

        if ($pemeriksaan) {
            $this->keluhan = $pemeriksaan->keluhan ?? '';
            $this->pemeriksaan = $pemeriksaan->pemeriksaan ?? '';
            $this->penilaian = $pemeriksaan->penilaian ?? '';
            $this->instruksi = $pemeriksaan->instruksi ?? '';
            $this->rtl = $pemeriksaan->rtl ?? '';
            // Safe access: check if $data exists before accessing its property
            $this->alergi = $pemeriksaan->alergi ?? ($data && isset($data->alergi) ? $data->alergi : null) ?? 'Tidak Ada';
            $this->suhu = $pemeriksaan->suhu_tubuh ?? '';
            $this->berat = $pemeriksaan->berat ?? '';
            $this->tinggi = $pemeriksaan->tinggi ?? '';
            $this->tensi = $pemeriksaan->tensi ?? '';
            $this->nadi = $pemeriksaan->nadi ?? '';
            $this->respirasi = $pemeriksaan->respirasi ?? '';
            $this->evaluasi = $pemeriksaan->evaluasi ?? '';
            $this->gcs = $pemeriksaan->gcs ?? '';
            $this->kesadaran = $pemeriksaan->kesadaran ?? 'Compos Mentis';
            $this->lingkar = null; // Field tidak ada di tabel pemeriksaan_ranap
            $this->spo2 = $pemeriksaan->spo2 ?? '';
        }
    }

    public function resetForm()
    {
        $this->keluhan = '';
        $this->pemeriksaan = '';
        $this->penilaian = '';
        $this->instruksi = '';
        $this->rtl = '';
        $this->alergi = 'Tidak Ada';
        $this->suhu = '';
        $this->berat = '';
        $this->tinggi = '';
        $this->tensi = '';
        $this->nadi = '';
        $this->respirasi = '';
        $this->evaluasi = '';
        $this->gcs = '';
        $this->kesadaran = 'Compos Mentis';
        $this->lingkar = '';
        $this->spo2 = '';
    }

    public function simpanPemeriksaan()
    {
        $this->validate([
            'keluhan' => 'required|min:3',
            'pemeriksaan' => 'required|min:3',
            'penilaian' => 'required',
            'instruksi' => 'required',
            'rtl' => 'required',
        ], [
            'keluhan.required' => 'Subjek tidak boleh kosong',
            'keluhan.min' => 'Subjek minimal 3 karakter',
            'pemeriksaan.required' => 'Objek tidak boleh kosong',
            'pemeriksaan.min' => 'Objek minimal 3 karakter',
            'penilaian.required' => 'Penilaian tidak boleh kosong',
            'instruksi.required' => 'Instruksi tidak boleh kosong',
            'rtl.required' => 'RTL tidak boleh kosong',
        ]);

        try {
            DB::beginTransaction();
            DB::table('pemeriksaan_ranap')
                ->insert([
                    'no_rawat' => $this->noRawat,
                    'keluhan' => $this->keluhan ?? '-',
                    'pemeriksaan' => $this->pemeriksaan ?? '-',
                    'penilaian' => $this->penilaian ?? '-',
                    'instruksi' => $this->instruksi ?? '-',
                    'rtl' => $this->rtl ?? '-',
                    'alergi' => $this->alergi ?? '-',
                    'suhu_tubuh' => $this->suhu ?? 'Na',
                    'berat' => $this->berat ?? 'Na',
                    'tinggi' => $this->tinggi ?? 'Na',
                    'tensi' => $this->tensi ?? 'Na',
                    'nadi' => $this->nadi ?? 'Na',
                    'respirasi' => $this->respirasi ?? 'Na',
                    'gcs' => $this->gcs ?? 'Na',
                    'kesadaran' => $this->kesadaran ?? 'Compos Mentis',
                    'spo2' => $this->spo2 ?? 'Na',
                    'evaluasi' => $this->evaluasi ?? '-',
                    'tgl_perawatan' => date('Y-m-d'),
                    'jam_rawat' => date('H:i:s'),
                    'nip' => session()->get('username'),
                ]);

            DB::commit();

            // Reset form setelah berhasil simpan
            $this->resetForm();

            // Refresh list pemeriksaan
            $this->getListPemeriksaan();

            // Tampilkan notifikasi sukses
            $this->dispatchBrowserEvent('swal:pemeriksaan', $this->toastResponse('Pemeriksaan berhasil ditambahkan', 'success'));
        } catch (\Exception $ex) {
            DB::rollback();
            $this->dispatchBrowserEvent('swal:pemeriksaan', $this->toastResponse($ex->getMessage() ?? 'Pemeriksaan gagal ditambahkan', 'error'));
        }
    }

    public function confirmHapus($noRawat, $tgl, $jam)
    {
        $this->noRawat = $noRawat;
        $this->tgl = $tgl;
        $this->jam = $jam;
        $this->confirm('Yakin ingin menghapus pemeriksaan ini?', [
            'toast' => false,
            'position' => 'center',
            'showConfirmButton' => true,
            'cancelButtonText' => 'Tidak',
            'onConfirmed' => 'hapusPemeriksaan',
        ]);
    }

    public function hapus()
    {
        try {
            DB::table('pemeriksaan_ranap')
                ->where('no_rawat', $this->noRawat)
                ->where('tgl_perawatan', $this->tgl)
                ->where('jam_rawat', $this->jam)
                ->delete();
            $this->getListPemeriksaan();
            $this->alert('success', 'Pemeriksaan berhasil dihapus', [
                'position' =>  'center',
                'timer' =>  3000,
                'toast' =>  false,
            ]);
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' =>  'center',
                'timer' =>  3000,
                'toast' =>  false,
                'text' =>  $e->getMessage(),
            ]);
        }
    }

    public function loadDataTerakhir()
    {
        $this->getPemeriksaan();
        $this->dispatchBrowserEvent('swal:pemeriksaan', $this->toastResponse('Data pemeriksaan terakhir berhasil dimuat', 'info'));
    }

    public function openModalStatistik()
    {
        $this->getStatistikPemeriksaan();
        // Pass data directly to JavaScript via browser event
        $this->dispatchBrowserEvent('openModalStatistikPemeriksaan', [
            'statistikData' => $this->statistikData
        ]);
    }

    public function getStatistikPemeriksaan()
    {
        // Data trend tanda vital dari waktu ke waktu
        $tandaVital = DB::table('pemeriksaan_ranap')
            ->where('no_rawat', $this->noRawat)
            ->orderBy('tgl_perawatan')
            ->orderBy('jam_rawat')
            ->select('tgl_perawatan', 'jam_rawat', 'tensi', 'nadi', 'suhu_tubuh', 'spo2')
            ->get();

        // Format data untuk chart tanda vital
        $labels = [];
        $tensiData = [];
        $nadiData = [];
        $suhuData = [];
        $spo2Data = [];

        foreach ($tandaVital as $item) {
            $dateTime = \Carbon\Carbon::parse($item->tgl_perawatan . ' ' . $item->jam_rawat)->format('d M H:i');
            $labels[] = $dateTime;

            // Parse tensi (format: "120/80" atau "120")
            $tensiValue = null;
            if ($item->tensi && $item->tensi !== 'Na' && $item->tensi !== '-') {
                if (strpos($item->tensi, '/') !== false) {
                    $tensiParts = explode('/', $item->tensi);
                    $tensiValue = is_numeric(trim($tensiParts[0])) ? (float)trim($tensiParts[0]) : null;
                } else {
                    $tensiValue = is_numeric($item->tensi) ? (float)$item->tensi : null;
                }
            }
            $tensiData[] = $tensiValue;

            // Parse nadi
            $nadiValue = null;
            if ($item->nadi && $item->nadi !== 'Na' && $item->nadi !== '-') {
                $nadiValue = is_numeric($item->nadi) ? (float)$item->nadi : null;
            }
            $nadiData[] = $nadiValue;

            // Parse suhu
            $suhuValue = null;
            if ($item->suhu_tubuh && $item->suhu_tubuh !== 'Na' && $item->suhu_tubuh !== '-') {
                $suhuValue = is_numeric($item->suhu_tubuh) ? (float)$item->suhu_tubuh : null;
            }
            $suhuData[] = $suhuValue;

            // Parse SpO2
            $spo2Value = null;
            if ($item->spo2 && $item->spo2 !== 'Na' && $item->spo2 !== '-') {
                $spo2Value = is_numeric($item->spo2) ? (float)$item->spo2 : null;
            }
            $spo2Data[] = $spo2Value;
        }

        // Data distribusi pemeriksaan per dokter
        $distribusiDokter = DB::table('pemeriksaan_ranap')
            ->join('pegawai', 'pemeriksaan_ranap.nip', '=', 'pegawai.nik')
            ->where('pemeriksaan_ranap.no_rawat', $this->noRawat)
            ->select('pegawai.nama', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('pegawai.nama')
            ->orderByDesc('jumlah')
            ->get();

        $dokterLabels = [];
        $dokterData = [];
        foreach ($distribusiDokter as $item) {
            $dokterLabels[] = $item->nama;
            $dokterData[] = (int)$item->jumlah;
        }

        // Data jumlah pemeriksaan per hari
        $pemeriksaanPerHari = DB::table('pemeriksaan_ranap')
            ->where('no_rawat', $this->noRawat)
            ->select(DB::raw('DATE(tgl_perawatan) as tanggal'), DB::raw('COUNT(*) as jumlah'))
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $hariLabels = [];
        $hariData = [];
        foreach ($pemeriksaanPerHari as $item) {
            $hariLabels[] = \Carbon\Carbon::parse($item->tanggal)->format('d M Y');
            $hariData[] = (int)$item->jumlah;
        }

        // Rata-rata tanda vital
        $avgTensi = DB::table('pemeriksaan_ranap')
            ->where('no_rawat', $this->noRawat)
            ->whereNotNull('tensi')
            ->where('tensi', '<>', 'Na')
            ->selectRaw('AVG(CAST(SUBSTRING_INDEX(tensi, "/", 1) AS UNSIGNED)) as avg_tensi')
            ->first();

        $avgNadi = DB::table('pemeriksaan_ranap')
            ->where('no_rawat', $this->noRawat)
            ->whereNotNull('nadi')
            ->where('nadi', '<>', 'Na')
            ->selectRaw('AVG(CAST(nadi AS DECIMAL(10,2))) as avg_nadi')
            ->first();

        $avgSuhu = DB::table('pemeriksaan_ranap')
            ->where('no_rawat', $this->noRawat)
            ->whereNotNull('suhu_tubuh')
            ->where('suhu_tubuh', '<>', 'Na')
            ->selectRaw('AVG(CAST(suhu_tubuh AS DECIMAL(10,2))) as avg_suhu')
            ->first();

        $avgSpo2 = DB::table('pemeriksaan_ranap')
            ->where('no_rawat', $this->noRawat)
            ->whereNotNull('spo2')
            ->where('spo2', '<>', 'Na')
            ->selectRaw('AVG(CAST(spo2 AS DECIMAL(10,2))) as avg_spo2')
            ->first();

        $this->statistikData = [
            'tanda_vital' => [
                'labels' => $labels,
                'tensi' => $tensiData,
                'nadi' => $nadiData,
                'suhu' => $suhuData,
                'spo2' => $spo2Data,
            ],
            'distribusi_dokter' => [
                'labels' => $dokterLabels,
                'data' => $dokterData,
            ],
            'pemeriksaan_per_hari' => [
                'labels' => $hariLabels,
                'data' => $hariData,
            ],
            'rata_rata' => [
                'tensi' => round($avgTensi->avg_tensi ?? 0, 1),
                'nadi' => round($avgNadi->avg_nadi ?? 0, 1),
                'suhu' => round($avgSuhu->avg_suhu ?? 0, 1),
                'spo2' => round($avgSpo2->avg_spo2 ?? 0, 1),
            ],
            'total_pemeriksaan' => count($this->listPemeriksaan),
        ];
    }

    public function copyToForm($tgl, $jam)
    {
        $pemeriksaan = DB::table('pemeriksaan_ranap')
            ->where('no_rawat', $this->noRawat)
            ->where('tgl_perawatan', $tgl)
            ->where('jam_rawat', $jam)
            ->first();

        if ($pemeriksaan) {
            $this->keluhan = $pemeriksaan->keluhan ?? '';
            $this->pemeriksaan = $pemeriksaan->pemeriksaan ?? '';
            $this->penilaian = $pemeriksaan->penilaian ?? '';
            $this->instruksi = $pemeriksaan->instruksi ?? '';
            $this->rtl = $pemeriksaan->rtl ?? '';
            $this->alergi = $pemeriksaan->alergi ?? 'Tidak Ada';
            $this->suhu = $pemeriksaan->suhu_tubuh ?? '';
            $this->berat = $pemeriksaan->berat ?? '';
            $this->tinggi = $pemeriksaan->tinggi ?? '';
            $this->tensi = $pemeriksaan->tensi ?? '';
            $this->nadi = $pemeriksaan->nadi ?? '';
            $this->respirasi = $pemeriksaan->respirasi ?? '';
            $this->evaluasi = $pemeriksaan->evaluasi ?? '';
            $this->gcs = $pemeriksaan->gcs ?? '';
            $this->kesadaran = $pemeriksaan->kesadaran ?? 'Compos Mentis';
            $this->spo2 = $pemeriksaan->spo2 ?? '';
            $this->dispatchBrowserEvent('swal:pemeriksaan', $this->toastResponse('Data pemeriksaan berhasil disalin ke form', 'success'));
        } else {
            $this->dispatchBrowserEvent('swal:pemeriksaan', $this->toastResponse('Data pemeriksaan tidak ditemukan', 'error'));
        }
    }

    public function toggleCollapse($tgl, $jam)
    {
        $key = $tgl . '_' . $jam;
        // Default: card tertutup (collapsedCards[$key] tidak ada atau true)
        // Jika card belum pernah diklik atau sedang tertutup, buka (set false)
        // Jika card sedang terbuka (false), tutup (set true)
        if (isset($this->collapsedCards[$key]) && !$this->collapsedCards[$key]) {
            $this->collapsedCards[$key] = true; // Tutup
        } else {
            $this->collapsedCards[$key] = false; // Buka
        }
    }
}
