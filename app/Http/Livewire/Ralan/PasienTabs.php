<?php

namespace App\Http\Livewire\Ralan;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PasienTabs extends Component
{
    public $noRawat;
    protected $pasien;
    public $data = [];
    public $selectDokter = "";
    public $tanggalMulai = "";
    public $tanggalAkhir = "";
    public $jenisPerawatan = ""; // "" untuk semua, "Ralan" untuk ralan, "Ranap" untuk ranap
    public $activeTab = 'riwayat'; // 'detail' atau 'riwayat'
    public $activeTabTindakan = 'ralan'; // 'ralan', 'ranap', 'lab', 'radiologi', 'operasi'
    protected $listeners = ['loadRiwayatPasien' => 'init'];

    public function mount($noRawat)
    {
        $this->noRawat = $noRawat;
        $this->pasien = $this->getPasien($noRawat);
        $this->setActiveTab('riwayat');
    }

    public function hydrate()
    {
        $this->pasien = $this->getPasien($this->noRawat);
    }

    public function render()
    {
        return view('livewire.ralan.pasien-tabs', [
            'dokter' => $this->getListDokter(),
        ]);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        if ($tab === 'riwayat') {
            // Selalu reload data ketika kembali ke tab riwayat untuk memastikan data fresh
            $this->init();
        } elseif ($tab === 'tindakan') {
            // Set default tab tindakan ke ralan
            $this->activeTabTindakan = 'ralan';
        } else {
            // Reset data dan filter ketika kembali ke tab detail untuk menghindari error
            $this->data = [];
            $this->selectDokter = "";
            $this->tanggalMulai = "";
            $this->tanggalAkhir = "";
            $this->jenisPerawatan = "";
        }
        $this->dispatchBrowserEvent('activeTabUpdated', ['activeTab' => $tab]);
    }

    public function getStatusLanjut($noRawat)
    {
        $data = DB::table('reg_periksa')
            ->where('no_rawat', $noRawat)
            ->select('status_lanjut')
            ->first();

        return $data ? $data->status_lanjut : 'Ralan';
    }

    public function setActiveTabTindakan($tab)
    {
        $this->activeTabTindakan = $tab;
    }

    /**
     * Ambil data tindakan Ralan berdasarkan dokter dan no_rawat.
     */
    public function getTindakanRalan($noRawat)
    {
        return DB::table('rawat_jl_dr')
            ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw', '=', 'jns_perawatan.kd_jenis_prw')
            ->leftJoin('dokter', 'rawat_jl_dr.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('rawat_jl_dr.no_rawat', $noRawat)
            ->where('rawat_jl_dr.kd_dokter', session()->get('username'))
            ->select(
                'rawat_jl_dr.*',
                'jns_perawatan.nm_perawatan',
                'jns_perawatan.kd_jenis_prw',
                'dokter.nm_dokter',
                'rawat_jl_dr.tarif_tindakandr'
            )
            ->orderBy('rawat_jl_dr.tgl_perawatan', 'desc')
            ->orderBy('rawat_jl_dr.jam_rawat', 'desc')
            ->get();
    }

    /**
     * Ambil data tindakan Ranap berdasarkan dokter dan no_rawat.
     */
    public function getTindakanRanap($noRawat)
    {
        return DB::table('rawat_inap_dr')
            ->join('jns_perawatan_inap', 'rawat_inap_dr.kd_jenis_prw', '=', 'jns_perawatan_inap.kd_jenis_prw')
            ->leftJoin('dokter', 'rawat_inap_dr.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('rawat_inap_dr.no_rawat', $noRawat)
            ->where('rawat_inap_dr.kd_dokter', session()->get('username'))
            ->select(
                'rawat_inap_dr.*',
                'jns_perawatan_inap.nm_perawatan',
                'jns_perawatan_inap.kd_jenis_prw',
                'dokter.nm_dokter',
                'rawat_inap_dr.tarif_tindakandr'
            )
            ->orderBy('rawat_inap_dr.tgl_perawatan', 'desc')
            ->orderBy('rawat_inap_dr.jam_rawat', 'desc')
            ->get();
    }

    /**
     * Ambil data tindakan radiologi berdasarkan dokter dan no_rawat.
     */
    public function getTindakanRadiologi($noRawat)
    {
        return DB::table('periksa_radiologi')
            ->join('reg_periksa', 'periksa_radiologi.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('jns_perawatan_radiologi', 'periksa_radiologi.kd_jenis_prw', '=', 'jns_perawatan_radiologi.kd_jenis_prw')
            ->where('periksa_radiologi.no_rawat', $noRawat)
            ->where('periksa_radiologi.kd_dokter', session()->get('username'))
            ->select(
                'periksa_radiologi.no_rawat',
                'periksa_radiologi.kd_jenis_prw',
                'jns_perawatan_radiologi.nm_perawatan',
                'periksa_radiologi.kd_dokter',
                'periksa_radiologi.tgl_periksa',
                'periksa_radiologi.jam',
                'periksa_radiologi.status',
                'periksa_radiologi.tarif_tindakan_dokter'
            )
            ->orderBy('periksa_radiologi.tgl_periksa', 'desc')
            ->orderBy('periksa_radiologi.jam', 'desc')
            ->get();
    }

    /**
     * Ambil data tindakan laboratorium berdasarkan dokter dan no_rawat.
     */
    public function getTindakanLab($noRawat)
    {
        return DB::table('periksa_lab')
            ->join('reg_periksa', 'periksa_lab.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('jns_perawatan_lab', 'periksa_lab.kd_jenis_prw', '=', 'jns_perawatan_lab.kd_jenis_prw')
            ->where('periksa_lab.no_rawat', $noRawat)
            ->where('periksa_lab.kd_dokter', session()->get('username'))
            ->select(
                'periksa_lab.no_rawat',
                'periksa_lab.kd_jenis_prw',
                'jns_perawatan_lab.nm_perawatan',
                'periksa_lab.kd_dokter',
                'periksa_lab.tgl_periksa',
                'periksa_lab.jam',
                'periksa_lab.status',
                'periksa_lab.tarif_tindakan_dokter'
            )
            ->orderBy('periksa_lab.tgl_periksa', 'desc')
            ->orderBy('periksa_lab.jam', 'desc')
            ->get();
    }

    /**
     * Ambil data tindakan operasi berdasarkan dokter dan no_rawat.
     * Dokter bisa terlibat sebagai operator1, operator2, operator3, dokter_anak, dokter_anestesi, dokter_pjanak, atau dokter_umum.
     */
    public function getTindakanOperasi($noRawat)
    {
        $kdDokter = session()->get('username');
        // Escape kdDokter untuk mencegah SQL injection
        $kdDokterEscaped = DB::connection()->getPdo()->quote($kdDokter);

        return DB::table('operasi')
            ->join('reg_periksa', 'operasi.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('paket_operasi', 'operasi.kode_paket', '=', 'paket_operasi.kode_paket')
            ->where('operasi.no_rawat', $noRawat)
            ->where(function ($query) use ($kdDokter) {
                $query->where('operasi.operator1', $kdDokter)
                    ->orWhere('operasi.operator2', $kdDokter)
                    ->orWhere('operasi.operator3', $kdDokter)
                    ->orWhere('operasi.dokter_anak', $kdDokter)
                    ->orWhere('operasi.dokter_anestesi', $kdDokter)
                    ->orWhere('operasi.dokter_pjanak', $kdDokter)
                    ->orWhere('operasi.dokter_umum', $kdDokter);
            })
            ->select(
                'operasi.no_rawat',
                'operasi.kode_paket',
                'paket_operasi.nm_perawatan',
                'operasi.tgl_operasi',
                'operasi.status',
                'operasi.kategori',
                'operasi.operator1',
                'operasi.operator2',
                'operasi.operator3',
                'operasi.dokter_anak',
                'operasi.dokter_anestesi',
                'operasi.dokter_pjanak',
                'operasi.dokter_umum',
                DB::raw("CASE 
                    WHEN operasi.operator1 = " . $kdDokterEscaped . " THEN operasi.biayaoperator1
                    WHEN operasi.operator2 = " . $kdDokterEscaped . " THEN operasi.biayaoperator2
                    WHEN operasi.operator3 = " . $kdDokterEscaped . " THEN operasi.biayaoperator3
                    WHEN operasi.dokter_anak = " . $kdDokterEscaped . " THEN operasi.biayadokter_anak
                    WHEN operasi.dokter_anestesi = " . $kdDokterEscaped . " THEN operasi.biayadokter_anestesi
                    WHEN operasi.dokter_pjanak = " . $kdDokterEscaped . " THEN operasi.biaya_dokter_pjanak
                    WHEN operasi.dokter_umum = " . $kdDokterEscaped . " THEN operasi.biaya_dokter_umum
                    ELSE 0
                END as biaya_dokter"),
                DB::raw("CASE 
                    WHEN operasi.operator1 = " . $kdDokterEscaped . " THEN 'Operator 1'
                    WHEN operasi.operator2 = " . $kdDokterEscaped . " THEN 'Operator 2'
                    WHEN operasi.operator3 = " . $kdDokterEscaped . " THEN 'Operator 3'
                    WHEN operasi.dokter_anak = " . $kdDokterEscaped . " THEN 'Dokter Anak'
                    WHEN operasi.dokter_anestesi = " . $kdDokterEscaped . " THEN 'Dokter Anestesi'
                    WHEN operasi.dokter_pjanak = " . $kdDokterEscaped . " THEN 'Dokter PJ Anak'
                    WHEN operasi.dokter_umum = " . $kdDokterEscaped . " THEN 'Dokter Umum'
                    ELSE '-'
                END as peran_dokter")
            )
            ->orderBy('operasi.tgl_operasi', 'desc')
            ->get();
    }

    /**
     * Hitung total biaya untuk semua kategori tindakan dokter.
     */
    public function getTotalSemuaTindakanDokter($noRawat)
    {
        $totalRalan = $this->getTindakanRalan($noRawat)->sum('tarif_tindakandr');
        $totalRanap = $this->getTindakanRanap($noRawat)->sum('tarif_tindakandr');
        $totalRadiologi = $this->getTindakanRadiologi($noRawat)->sum('tarif_tindakan_dokter');
        $totalLab = $this->getTindakanLab($noRawat)->sum('tarif_tindakan_dokter');
        $totalOperasi = $this->getTindakanOperasi($noRawat)->sum('biaya_dokter');

        return [
            'ralan' => $totalRalan,
            'ranap' => $totalRanap,
            'radiologi' => $totalRadiologi,
            'lab' => $totalLab,
            'operasi' => $totalOperasi,
            'total' => $totalRalan + $totalRanap + $totalRadiologi + $totalLab + $totalOperasi
        ];
    }

    /**
     * Hitung total tindakan untuk semua kategori.
     */
    public function getTotalSemuaTindakanCount($noRawat)
    {
        return [
            'ralan' => $this->getTindakanRalan($noRawat)->count(),
            'ranap' => $this->getTindakanRanap($noRawat)->count(),
            'radiologi' => $this->getTindakanRadiologi($noRawat)->count(),
            'lab' => $this->getTindakanLab($noRawat)->count(),
            'operasi' => $this->getTindakanOperasi($noRawat)->count(),
        ];
    }

    public function init()
    {
        $this->data = $this->getRiwayatPemeriksaan($this->pasien->no_rkm_medis, $this->selectDokter, $this->tanggalMulai, $this->tanggalAkhir, $this->jenisPerawatan);
    }

    public function getListDokter()
    {
        return DB::table('dokter')->where('status', '1')->select('kd_dokter', 'nm_dokter')->get();
    }

    public function updatedSelectDokter()
    {
        $this->data = $this->getRiwayatPemeriksaan($this->pasien->no_rkm_medis, $this->selectDokter, $this->tanggalMulai, $this->tanggalAkhir, $this->jenisPerawatan);
    }

    public function updatedTanggalMulai()
    {
        $this->data = $this->getRiwayatPemeriksaan($this->pasien->no_rkm_medis, $this->selectDokter, $this->tanggalMulai, $this->tanggalAkhir, $this->jenisPerawatan);
    }

    public function updatedTanggalAkhir()
    {
        $this->data = $this->getRiwayatPemeriksaan($this->pasien->no_rkm_medis, $this->selectDokter, $this->tanggalMulai, $this->tanggalAkhir, $this->jenisPerawatan);
    }

    public function updatedJenisPerawatan()
    {
        $this->data = $this->getRiwayatPemeriksaan($this->pasien->no_rkm_medis, $this->selectDokter, $this->tanggalMulai, $this->tanggalAkhir, $this->jenisPerawatan);
    }

    public function resetFilter()
    {
        $this->selectDokter = "";
        $this->tanggalMulai = "";
        $this->tanggalAkhir = "";
        $this->jenisPerawatan = "";
        $this->data = $this->getRiwayatPemeriksaan($this->pasien->no_rkm_medis);

        // Reset select2
        $this->dispatchBrowserEvent('resetSelect2');
    }

    public function getPasien($noRawat)
    {
        $data = DB::table('reg_periksa')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->where('reg_periksa.no_rawat', $noRawat)
            ->select('reg_periksa.no_rkm_medis')
            ->first();

        return $data;
    }

    public function getRiwayatPemeriksaan($noRM, $kdDokter = "", $tanggalMulai = "", $tanggalAkhir = "", $jenisPerawatan = "")
    {
        $query = DB::table('reg_periksa')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('no_rkm_medis', $noRM)
            ->where('reg_periksa.stts', '<>', 'Batal');

        // Filter dokter
        if ($kdDokter != "") {
            $query->where('reg_periksa.kd_dokter', $kdDokter);
        }

        // Filter tanggal mulai
        if ($tanggalMulai != "") {
            $query->whereDate('reg_periksa.tgl_registrasi', '>=', $tanggalMulai);
        }

        // Filter tanggal akhir
        if ($tanggalAkhir != "") {
            $query->whereDate('reg_periksa.tgl_registrasi', '<=', $tanggalAkhir);
        }

        // Filter jenis perawatan (Ralan/Ranap)
        if ($jenisPerawatan != "") {
            $query->where('reg_periksa.status_lanjut', $jenisPerawatan);
        }

        $data = $query->select(
            'reg_periksa.tgl_registrasi',
            'reg_periksa.no_rawat',
            'dokter.nm_dokter',
            'reg_periksa.status_lanjut',
            'poliklinik.nm_poli',
            'reg_periksa.no_reg'
        )
            ->orderBy('reg_periksa.tgl_registrasi', 'desc')
            ->limit(10)
            ->get();

        return $data;
    }

    public function getPemeriksaanRalan($noRawat, $status)
    {
        if ($status == 'Ralan') {
            $data = DB::table('pemeriksaan_ralan')
                ->leftJoin('pegawai', 'pemeriksaan_ralan.nip', '=', 'pegawai.nik')
                ->where('pemeriksaan_ralan.no_rawat', $noRawat)
                ->select('pemeriksaan_ralan.*', 'pegawai.nama as nama_petugas', 'pegawai.jbtn as jabatan')
                ->orderByDesc('pemeriksaan_ralan.tgl_perawatan')
                ->orderByDesc('pemeriksaan_ralan.jam_rawat')
                ->get();

            // Tambahkan informasi jenis petugas (Dokter atau Perawat)
            foreach ($data as $item) {
                if ($item->nip) {
                    $isDokter = DB::table('dokter')
                        ->where('kd_dokter', $item->nip)
                        ->exists();
                    $item->jenis_petugas = $isDokter ? 'Dokter' : 'Perawat';
                } else {
                    $item->jenis_petugas = 'Perawat';
                }
            }
        } else {
            $data = DB::table('pemeriksaan_ranap')
                ->leftJoin('pegawai', 'pemeriksaan_ranap.nip', '=', 'pegawai.nik')
                ->where('pemeriksaan_ranap.no_rawat', $noRawat)
                ->select('pemeriksaan_ranap.*', 'pegawai.nama as nama_petugas', 'pegawai.jbtn as jabatan')
                ->orderByDesc('pemeriksaan_ranap.tgl_perawatan')
                ->orderByDesc('pemeriksaan_ranap.jam_rawat')
                ->get();

            // Tambahkan informasi jenis petugas (Dokter atau Perawat)
            foreach ($data as $item) {
                if ($item->nip) {
                    $isDokter = DB::table('dokter')
                        ->where('kd_dokter', $item->nip)
                        ->exists();
                    $item->jenis_petugas = $isDokter ? 'Dokter' : 'Perawat';
                } else {
                    $item->jenis_petugas = 'Perawat';
                }
            }
        }
        return $data;
    }

    public function getDiagnosa($noRawat)
    {
        $data = DB::table('diagnosa_pasien')
            ->join('penyakit', 'diagnosa_pasien.kd_penyakit', '=', 'penyakit.kd_penyakit')
            ->where('diagnosa_pasien.no_rawat', $noRawat)
            ->select('penyakit.kd_penyakit', 'penyakit.nm_penyakit', 'diagnosa_pasien.status')
            ->get();
        return $data;
    }

    public function getTono($noRawat)
    {
        return DB::table('pemeriksaan_tono')->where('no_rawat', $noRawat)->first();
    }

    public function getPemeriksaanLab($noRawat)
    {
        $data = DB::table('detail_periksa_lab')
            ->join('template_laboratorium', 'detail_periksa_lab.id_template', '=', 'template_laboratorium.id_template')
            ->where('detail_periksa_lab.no_rawat', $noRawat)
            ->select('template_laboratorium.Pemeriksaan', 'detail_periksa_lab.tgl_periksa', 'detail_periksa_lab.jam', 'detail_periksa_lab.nilai', 'template_laboratorium.satuan', 'detail_periksa_lab.nilai_rujukan', 'detail_periksa_lab.keterangan')
            ->orderBy('detail_periksa_lab.tgl_periksa', 'desc')
            ->get();
        return $data;
    }

    public function getResume($noRM)
    {
        return DB::table('resume_pasien')
            ->where('no_rawat', $noRM)
            ->first();
    }

    public function getRadiologi($noRM)
    {
        return DB::table('hasil_radiologi')
            ->where('no_rawat', $noRM)
            ->get();
    }

    public function getFotoRadiologi($noRM)
    {
        return DB::table('gambar_radiologi')
            ->where('no_rawat', $noRM)
            ->get();
    }

    public function getRiwayatObat($noRawat)
    {
        $data = DB::table('resep_obat')
            ->leftJoin('dokter', 'resep_obat.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('resep_obat.no_rawat', $noRawat)
            ->select(
                'resep_obat.no_resep',
                'resep_obat.tgl_perawatan',
                'resep_obat.jam',
                'resep_obat.tgl_peresepan',
                'resep_obat.jam_peresepan',
                'resep_obat.tgl_penyerahan',
                'resep_obat.jam_penyerahan',
                'resep_obat.status',
                'dokter.nm_dokter'
            )
            ->orderByDesc('resep_obat.tgl_peresepan')
            ->orderByDesc('resep_obat.jam_peresepan')
            ->get();

        // Ambil detail obat untuk setiap resep
        foreach ($data as $resep) {
            $resep->detail_obat = DB::table('resep_dokter')
                ->join('databarang', 'resep_dokter.kode_brng', '=', 'databarang.kode_brng')
                ->where('resep_dokter.no_resep', $resep->no_resep)
                ->select(
                    'databarang.nama_brng',
                    'resep_dokter.jml',
                    'resep_dokter.aturan_pakai'
                )
                ->get();
        }

        return $data;
    }

    public function getBerkasDigital($noRawat)
    {
        $data = DB::table('berkas_digital_perawatan')
            ->join('master_berkas_digital', 'berkas_digital_perawatan.kode', '=', 'master_berkas_digital.kode')
            ->where('berkas_digital_perawatan.no_rawat', $noRawat)
            ->select(
                'berkas_digital_perawatan.kode',
                'berkas_digital_perawatan.lokasi_file',
                'master_berkas_digital.nama'
            )
            ->orderBy('master_berkas_digital.nama')
            ->get();

        return $data;
    }

    public static function getobatRalan($noRM)
    {

        $dataobat = DB::table('detail_pemberian_obat')
            ->join('databarang', 'detail_pemberian_obat.kode_brng', '=', 'databarang.kode_brng')
            ->where('detail_pemberian_obat.no_rawat', $noRM)
            ->where('detail_pemberian_obat.status', 'Ralan')
            ->select('databarang.nama_brng', 'detail_pemberian_obat.jml', 'detail_pemberian_obat.kode_brng', 'detail_pemberian_obat.tgl_perawatan', 'detail_pemberian_obat.jam')
            ->get();

        foreach ($dataobat as $obat) {
            $aturan = DB::table('aturan_pakai')
                ->where('kode_brng', $obat->kode_brng)
                ->where('no_rawat', $noRM)
                ->value('aturan');
            $obat->aturan = $aturan ?? '-';
        }

        return $dataobat;
    }

    public function getobatRanap($noRM)
    {
        $dataobat = DB::table('detail_pemberian_obat')
            ->join('databarang', 'detail_pemberian_obat.kode_brng', '=', 'databarang.kode_brng')
            ->where('detail_pemberian_obat.no_rawat', $noRM)
            ->where('detail_pemberian_obat.status', 'Ranap')
            ->select('databarang.nama_brng', 'detail_pemberian_obat.jml', 'detail_pemberian_obat.kode_brng', 'detail_pemberian_obat.tgl_perawatan', 'detail_pemberian_obat.jam')
            ->orderBy('detail_pemberian_obat.tgl_perawatan', 'desc')
            ->get();

        foreach ($dataobat as $obat) {
            $aturan = DB::table('aturan_pakai')
                ->where('kode_brng', $obat->kode_brng)
                ->where('no_rawat', $noRM)
                ->value('aturan');
            $obat->aturan = $aturan ?? '-';
        }

        return $dataobat;
    }

    public function getPenilaianAwalKeperawatanKebidananRanap($noRawat)
    {
        return DB::table('penilaian_awal_keperawatan_kebidanan_ranap')
            ->leftJoin('petugas as petugas1', 'penilaian_awal_keperawatan_kebidanan_ranap.nip1', '=', 'petugas1.nip')
            ->leftJoin('petugas as petugas2', 'penilaian_awal_keperawatan_kebidanan_ranap.nip2', '=', 'petugas2.nip')
            ->leftJoin('dokter', 'penilaian_awal_keperawatan_kebidanan_ranap.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('penilaian_awal_keperawatan_kebidanan_ranap.no_rawat', $noRawat)
            ->select(
                'penilaian_awal_keperawatan_kebidanan_ranap.*',
                'petugas1.nama as nama_petugas1',
                'petugas2.nama as nama_petugas2',
                'dokter.nm_dokter'
            )
            ->first();
    }
}
