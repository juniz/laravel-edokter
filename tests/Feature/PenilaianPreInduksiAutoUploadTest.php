<?php

namespace Tests\Feature;

use App\Http\Livewire\Component\PenilaianPreInduksi;
use App\Services\PenilaianPreInduksiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class PenilaianPreInduksiAutoUploadTest extends TestCase
{
    private array $createdFixtureTables = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->requireTestingEnvironment();

        $this->createFixtureTable('dokter', function ($table) {
            $table->string('kd_dokter')->primary();
            $table->string('nm_dokter');
            $table->enum('status', ['0', '1'])->default('1');
        });

        $this->createFixtureTable('pasien', function ($table) {
            $table->string('no_rkm_medis')->primary();
            $table->string('nm_pasien');
            $table->date('tgl_lahir')->nullable();
            $table->enum('jk', ['L', 'P'])->default('L');
            $table->string('kd_pj')->default('BPJ');
            $table->string('kd_kel')->nullable();
            $table->string('kd_kec')->nullable();
            $table->string('kd_kab')->nullable();
            $table->string('kd_prop')->nullable();
        });

        $this->createFixtureTable('reg_periksa', function ($table) {
            $table->string('no_rawat')->primary();
            $table->string('no_rkm_medis');
            $table->string('umurdaftar')->default('30');
            $table->string('sttsumur')->default('Th');
            $table->date('tgl_registrasi')->nullable();
            $table->string('kd_pj')->default('BPJ');
            $table->string('kd_poli')->default('POL01');
            $table->string('kd_dokter')->default('D001');
            $table->enum('status_lanjut', ['Ralan', 'Ranap'])->default('Ralan');
        });

        $this->createFixtureTable('penjab', function ($table) {
            $table->string('kd_pj')->primary();
            $table->string('png_jawab')->nullable();
        });

        $this->createFixtureTable('poliklinik', function ($table) {
            $table->string('kd_poli')->primary();
            $table->string('nm_poli')->nullable();
        });

        $this->createFixtureTable('catatan_pasien', function ($table) {
            $table->string('no_rkm_medis')->primary();
            $table->text('catatan')->nullable();
        });

        $this->createFixtureTable('personal_pasien', function ($table) {
            $table->string('no_rkm_medis')->primary();
            $table->string('gambar')->nullable();
        });

        $this->createFixtureTable('propinsi', function ($table) {
            $table->string('kd_prop')->primary();
            $table->string('nm_prop')->nullable();
        });

        $this->createFixtureTable('kabupaten', function ($table) {
            $table->string('kd_kab')->primary();
            $table->string('nm_kab')->nullable();
        });

        $this->createFixtureTable('kecamatan', function ($table) {
            $table->string('kd_kec')->primary();
            $table->string('nm_kec')->nullable();
        });

        $this->createFixtureTable('kelurahan', function ($table) {
            $table->string('kd_kel')->primary();
            $table->string('nm_kel')->nullable();
        });

        $this->createFixtureTable('pemeriksaan_ralan', function ($table) {
            $table->string('no_rawat')->primary();
            $table->string('alergi')->nullable();
            $table->date('tgl_perawatan')->nullable();
            $table->time('jam_rawat')->nullable();
            $table->text('keluhan')->nullable();
            $table->text('pemeriksaan')->nullable();
            $table->string('suhu_tubuh')->nullable();
            $table->string('tensi')->nullable();
            $table->string('nadi')->nullable();
            $table->string('respirasi')->nullable();
            $table->string('spo2')->nullable();
            $table->string('gcs')->nullable();
            $table->string('tinggi')->nullable();
            $table->string('berat')->nullable();
        });

        $this->createFixtureTable('pemeriksaan_ranap', function ($table) {
            $table->string('no_rawat')->primary();
            $table->date('tgl_perawatan')->nullable();
            $table->time('jam_rawat')->nullable();
            $table->text('keluhan')->nullable();
            $table->text('pemeriksaan')->nullable();
            $table->string('suhu_tubuh')->nullable();
            $table->string('tensi')->nullable();
            $table->string('nadi')->nullable();
            $table->string('respirasi')->nullable();
            $table->string('spo2')->nullable();
            $table->string('gcs')->nullable();
            $table->string('tinggi')->nullable();
            $table->string('berat')->nullable();
        });

        $this->createFixtureTable('penilaian_pre_induksi', function ($table) {
            $table->string('no_rawat');
            $table->dateTime('tanggal');
            $table->string('kd_dokter');
            $table->string('tensi', 8)->nullable();
            $table->string('nadi', 5)->nullable();
            $table->string('rr', 5)->nullable();
            $table->string('suhu', 5)->nullable();
            $table->string('ekg', 50)->nullable();
            $table->string('lain_lain', 50)->nullable();
            $table->string('asesmen')->nullable();
            $table->string('perencanaan', 300)->nullable();
            $table->string('infus_perifier', 300)->nullable();
            $table->string('cvc', 70)->nullable();
            $table->string('posisi')->nullable();
            $table->string('premedikasi')->nullable();
            $table->string('premedikasi_keterangan', 50)->nullable();
            $table->string('induksi')->nullable();
            $table->string('induksi_keterangan', 70)->nullable();
            $table->string('face_mask_no', 20)->nullable();
            $table->string('nasopharing_no', 20)->nullable();
            $table->string('ett_no', 20)->nullable();
            $table->string('ett_jenis', 20)->nullable();
            $table->string('ett_viksasi', 25)->nullable();
            $table->string('lma_no', 20)->nullable();
            $table->string('lma_jenis', 20)->nullable();
            $table->string('tracheostomi', 60)->nullable();
            $table->string('bronchoscopi_fiberoptik', 60)->nullable();
            $table->string('glidescopi', 60)->nullable();
            $table->string('lain_lain_tatalaksana', 100)->nullable();
            $table->string('intubasi_sesudah_tidur')->nullable();
            $table->string('intubasi_oral')->nullable();
            $table->string('intubasi_tracheostomi')->nullable();
            $table->string('intubasi_keterangan', 200)->nullable();
            $table->string('sulit_ventilasi', 100)->nullable();
            $table->string('sulit_intubasi', 100)->nullable();
            $table->string('ventilasi', 100)->nullable();
            $table->string('teknik_regional_jenis', 100)->nullable();
            $table->string('teknik_regional_lokasi', 40)->nullable();
            $table->string('teknik_regional_jenis_jarum', 30)->nullable();
            $table->string('teknik_regional_kateter')->nullable();
            $table->string('teknik_regional_kateter_viksasi', 40)->nullable();
            $table->string('teknik_regional_obat_obatan', 400)->nullable();
            $table->string('teknik_regional_komplikasi', 200)->nullable();
            $table->string('teknik_regional_hasil', 100)->nullable();
            $table->primary(['no_rawat', 'tanggal']);
        });

        $this->createFixtureTable('berkas_digital_perawatan', function ($table) {
            $table->string('no_rawat');
            $table->string('kode');
            $table->string('lokasi_file');
        });

        $this->createFixtureTable('setting', function ($table) {
            $table->id();
            $table->string('nama_instansi')->nullable();
            $table->string('alamat_instansi')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('propinsi')->nullable();
            $table->string('kontak')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
        });

        DB::table('penjab')->insert([
            'kd_pj' => 'BPJ',
            'png_jawab' => 'BPJS Kesehatan',
        ]);

        DB::table('poliklinik')->insert([
            'kd_poli' => 'POL01',
            'nm_poli' => 'Poli Anestesi',
        ]);

        DB::table('dokter')->insert([
            'kd_dokter' => 'D001',
            'nm_dokter' => 'dr. Anestesi, Sp.An',
            'status' => '1',
        ]);

        DB::table('pasien')->insert([
            'no_rkm_medis' => '000001',
            'nm_pasien' => 'Tn. Pasien Uji Anestesi',
            'tgl_lahir' => '1990-01-02',
            'jk' => 'L',
            'kd_pj' => 'BPJ',
        ]);

        DB::table('reg_periksa')->insert([
            'no_rawat' => '2026/08/19/000001',
            'no_rkm_medis' => '000001',
            'umurdaftar' => '36',
            'sttsumur' => 'Th',
            'tgl_registrasi' => '2026-08-19',
            'kd_pj' => 'BPJ',
            'kd_poli' => 'POL01',
            'kd_dokter' => 'D001',
        ]);

        DB::table('setting')->insert([
            'nama_instansi' => 'RUMAH SAKIT BHAYANGKARA TK. III NGANJUK',
            'alamat_instansi' => 'Jl. Langsep No. 1',
            'kabupaten' => 'NGANJUK',
            'propinsi' => 'JAWA TIMUR',
        ]);

        DB::table('penilaian_pre_induksi')->insert([
            'no_rawat' => '2026/08/19/000001',
            'tanggal' => '2026-08-19 09:00:00',
            'kd_dokter' => 'D001',
            'tensi' => '120/80',
            'nadi' => '80',
            'rr' => '20',
            'suhu' => '36.5',
            'ekg' => 'Normal Sinus Rhythm',
            'lain_lain' => 'Tidak ada',
            'asesmen' => 'Sesuai Asesmen Pre Sedasi/Anestesi',
            'perencanaan' => 'General Anesthesia',
            'posisi' => 'Supine',
            'premedikasi' => 'IV',
            'induksi' => 'Intravena',
        ]);

        session(['username' => 'D001']);
    }

    protected function tearDown(): void
    {
        try {
            $this->requireTestingEnvironment();

            foreach (array_reverse($this->createdFixtureTables) as $table) {
                Schema::dropIfExists($table);
            }
        } finally {
            parent::tearDown();
        }
    }

    private function requireTestingEnvironment(): void
    {
        if (! app()->environment('testing')) {
            throw new \LogicException('PenilaianPreInduksiAutoUploadTest fixtures may only run in the testing environment.');
        }
    }

    private function createFixtureTable(string $table, \Closure $definition): void
    {
        if (Schema::hasTable($table)) {
            throw new \LogicException("Test fixture table [{$table}] already exists.");
        }

        Schema::create($table, $definition);
        $this->createdFixtureTables[] = $table;
    }

    /**
     * Test 1: test_service_generates_pdf_and_uploads_with_code_a07
     * Http::fake() verifies payload:
     * - kd_berkas == 'A07'
     * - no_rawat == '2026/08/19/000001'
     * - base64 PDF starting with %PDF-
     * - filename starts with PENILAIAN_PRE_INDUKSI_
     * - record stored in berkas_digital_perawatan
     */
    public function test_service_generates_pdf_and_uploads_with_code_a07(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => function ($request) {
                $data = $request->data();
                DB::table('berkas_digital_perawatan')->insert([
                    'no_rawat' => $data['no_rawat'],
                    'kode' => $data['kd_berkas'],
                    'lokasi_file' => 'pages/upload/' . $data['nama_file'] . '.pdf',
                ]);

                return Http::response([
                    'success' => true,
                    'message' => 'Upload berhasil',
                ], 200);
            },
        ]);

        $service = app(PenilaianPreInduksiService::class);
        $result = $service->generateAndUploadPdf('2026/08/19/000001', '2026-08-19 09:00:00');

        $this->assertTrue($result['success']);
        $this->assertSame('Berkas penilaian pre induksi berhasil disimpan dan diunggah.', $result['message']);
        $this->assertNotNull($result['pdf_url']);
        $this->assertStringStartsWith('https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/pages/upload/PENILAIAN_PRE_INDUKSI_2026-08-19-000001_', $result['pdf_url']);
        $this->assertStringEndsWith('.pdf', $result['pdf_url']);

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php') {
                return false;
            }

            $hasCorrectCode = ($request['kd_berkas'] ?? null) === 'A07';
            $hasCorrectNoRawat = ($request['no_rawat'] ?? null) === '2026/08/19/000001';
            $hasCorrectPrefix = str_starts_with($request['nama_file'] ?? '', 'PENILAIAN_PRE_INDUKSI_2026-08-19-000001_');

            $decodedFile = base64_decode($request['file'] ?? '', true);
            $isValidPdf = $decodedFile !== false && str_starts_with($decodedFile, '%PDF-');

            return $hasCorrectCode && $hasCorrectNoRawat && $hasCorrectPrefix && $isValidPdf;
        });

        $this->assertDatabaseHas('berkas_digital_perawatan', [
            'no_rawat' => '2026/08/19/000001',
            'kode' => 'A07',
        ]);
    }

    /**
     * Test 2: test_livewire_simpan_triggers_auto_upload_and_success_alert
     * Livewire::test(PenilaianPreInduksi::class) calling simpan().
     */
    public function test_livewire_simpan_triggers_auto_upload_and_success_alert(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => function ($request) {
                $data = $request->data();
                DB::table('berkas_digital_perawatan')->insert([
                    'no_rawat' => $data['no_rawat'],
                    'kode' => $data['kd_berkas'],
                    'lokasi_file' => 'pages/upload/' . $data['nama_file'] . '.pdf',
                ]);

                return Http::response([
                    'success' => true,
                    'message' => 'Upload berhasil',
                ], 200);
            },
        ]);

        Livewire::test(PenilaianPreInduksi::class, [
            'noRawat' => '2026/08/19/000001',
            'noRm' => '000001',
        ])
            ->set('tanggal', '2026-08-19T10:00')
            ->set('kd_dokter', 'D001')
            ->set('tensi', '125/85')
            ->set('nadi', '82')
            ->set('rr', '18')
            ->set('suhu', '36.6')
            ->set('ekg', 'Sinus')
            ->set('asesmen', 'Sesuai Asesmen Pre Sedasi/Anestesi')
            ->set('perencanaan', 'General Anesthesia Masker')
            ->set('posisi', 'Supine')
            ->set('premedikasi', 'IV')
            ->set('induksi', 'Intravena')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('alert', function ($eventName, $data) {
                return ($data['type'] ?? '') === 'success'
                    && str_contains($data['message'] ?? '', 'Penilaian Pre Induksi berhasil disimpan');
            });

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php') {
                return false;
            }

            return ($request['kd_berkas'] ?? null) === 'A07'
                && ($request['no_rawat'] ?? null) === '2026/08/19/000001'
                && str_starts_with($request['nama_file'] ?? '', 'PENILAIAN_PRE_INDUKSI_2026-08-19-000001_');
        });

        $this->assertDatabaseHas('penilaian_pre_induksi', [
            'no_rawat' => '2026/08/19/000001',
            'tanggal' => '2026-08-19 10:00:00',
            'kd_dokter' => 'D001',
            'tensi' => '125/85',
            'perencanaan' => 'General Anesthesia Masker',
        ]);

        $this->assertDatabaseHas('berkas_digital_perawatan', [
            'no_rawat' => '2026/08/19/000001',
            'kode' => 'A07',
        ]);
    }

    /**
     * Test 3: test_livewire_update_triggers_auto_upload_and_updated_alert
     * Livewire::test calling update().
     */
    public function test_livewire_update_triggers_auto_upload_and_updated_alert(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => function ($request) {
                $data = $request->data();
                DB::table('berkas_digital_perawatan')->insert([
                    'no_rawat' => $data['no_rawat'],
                    'kode' => $data['kd_berkas'],
                    'lokasi_file' => 'pages/upload/' . $data['nama_file'] . '.pdf',
                ]);

                return Http::response([
                    'success' => true,
                    'message' => 'Upload berhasil',
                ], 200);
            },
        ]);

        Livewire::test(PenilaianPreInduksi::class, [
            'noRawat' => '2026/08/19/000001',
            'noRm' => '000001',
        ])
            ->call('edit', '2026-08-19 09:00:00')
            ->set('tensi', '130/85')
            ->set('perencanaan', 'General Anesthesia ETT No 7.5')
            ->call('update')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('alert', function ($eventName, $data) {
                return ($data['type'] ?? '') === 'success'
                    && str_contains($data['message'] ?? '', 'berkas digital diperbarui');
            });

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php') {
                return false;
            }

            return ($request['kd_berkas'] ?? null) === 'A07'
                && ($request['no_rawat'] ?? null) === '2026/08/19/000001'
                && str_starts_with($request['nama_file'] ?? '', 'PENILAIAN_PRE_INDUKSI_2026-08-19-000001_');
        });

        $this->assertDatabaseHas('penilaian_pre_induksi', [
            'no_rawat' => '2026/08/19/000001',
            'tanggal' => '2026-08-19 09:00:00',
            'tensi' => '130/85',
            'perencanaan' => 'General Anesthesia ETT No 7.5',
        ]);
    }

    /**
     * Test 4: test_upload_replaces_old_penilaian_pre_induksi_files_and_preserves_other_a07_documents
     * Tests cleanup of old assessment entries while preserving other A07 documents.
     */
    public function test_upload_replaces_old_penilaian_pre_induksi_files_and_preserves_other_a07_documents(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        // Pre-populate berkas_digital_perawatan with:
        // 1. Old penilaian pre induksi file for patient 000001
        // 2. Another A07 anestesi document for patient 000001 (e.g. LAPORAN_ANESTESI_...)
        // 3. Penilaian file for a different patient 000002
        DB::table('berkas_digital_perawatan')->insert([
            [
                'no_rawat' => '2026/08/19/000001',
                'kode' => 'A07',
                'lokasi_file' => 'pages/upload/PENILAIAN_PRE_INDUKSI_2026-08-19-000001_20260819080000.pdf',
            ],
            [
                'no_rawat' => '2026/08/19/000001',
                'kode' => 'A07',
                'lokasi_file' => 'pages/upload/LAPORAN_ANESTESI_2026-08-19-000001.pdf',
            ],
            [
                'no_rawat' => '2026/08/19/000002',
                'kode' => 'A07',
                'lokasi_file' => 'pages/upload/PENILAIAN_PRE_INDUKSI_2026-08-19-000002_20260819080000.pdf',
            ],
        ]);

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => function ($request) {
                $data = $request->data();
                DB::table('berkas_digital_perawatan')->insert([
                    'no_rawat' => $data['no_rawat'],
                    'kode' => $data['kd_berkas'],
                    'lokasi_file' => 'pages/upload/' . $data['nama_file'] . '.pdf',
                ]);

                return Http::response([
                    'success' => true,
                    'message' => 'Upload berhasil',
                ], 200);
            },
        ]);

        $service = app(PenilaianPreInduksiService::class);
        $result = $service->generateAndUploadPdf('2026/08/19/000001', '2026-08-19 09:00:00');

        $this->assertTrue($result['success']);
        $this->assertTrue($result['is_replaced']);
        $this->assertSame('Berkas penilaian pre induksi berhasil disimpan dan berkas digital diperbarui.', $result['message']);

        // Assert old penilaian file for 000001 was deleted
        $this->assertDatabaseMissing('berkas_digital_perawatan', [
            'no_rawat' => '2026/08/19/000001',
            'kode' => 'A07',
            'lokasi_file' => 'pages/upload/PENILAIAN_PRE_INDUKSI_2026-08-19-000001_20260819080000.pdf',
        ]);

        // Assert other A07 anestesi document for 000001 is PRESERVED
        $this->assertDatabaseHas('berkas_digital_perawatan', [
            'no_rawat' => '2026/08/19/000001',
            'kode' => 'A07',
            'lokasi_file' => 'pages/upload/LAPORAN_ANESTESI_2026-08-19-000001.pdf',
        ]);

        // Assert penilaian file for patient 000002 is PRESERVED
        $this->assertDatabaseHas('berkas_digital_perawatan', [
            'no_rawat' => '2026/08/19/000002',
            'kode' => 'A07',
            'lokasi_file' => 'pages/upload/PENILAIAN_PRE_INDUKSI_2026-08-19-000002_20260819080000.pdf',
        ]);

        // Assert exactly 1 penilaian record remains for patient 000001
        $countPenilaian001 = DB::table('berkas_digital_perawatan')
            ->where('no_rawat', '2026/08/19/000001')
            ->where('kode', 'A07')
            ->where('lokasi_file', 'like', '%PENILAIAN_PRE_INDUKSI_2026-08-19-000001_%')
            ->count();
        $this->assertSame(1, $countPenilaian001);
    }

    /**
     * Test 5: test_livewire_hapus_deletes_digital_file_from_berkas_digital_perawatan
     * Tests digital record cleanup on assessment deletion.
     */
    public function test_livewire_hapus_deletes_digital_file_from_berkas_digital_perawatan(): void
    {
        DB::table('berkas_digital_perawatan')->insert([
            [
                'no_rawat' => '2026/08/19/000001',
                'kode' => 'A07',
                'lokasi_file' => 'pages/upload/PENILAIAN_PRE_INDUKSI_2026-08-19-000001_20260819090000.pdf',
            ],
            [
                'no_rawat' => '2026/08/19/000001',
                'kode' => 'A07',
                'lokasi_file' => 'pages/upload/LAPORAN_ANESTESI_2026-08-19-000001.pdf',
            ],
        ]);

        Livewire::test(PenilaianPreInduksi::class, [
            'noRawat' => '2026/08/19/000001',
            'noRm' => '000001',
        ])
            ->call('confirmHapus', '2026-08-19 09:00:00')
            ->call('hapus', ['data' => ['tanggal' => '2026-08-19 09:00:00']])
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('alert', function ($eventName, $data) {
                return ($data['type'] ?? '') === 'success'
                    && str_contains($data['message'] ?? '', 'Penilaian Pre Induksi berhasil dihapus');
            });

        $this->assertDatabaseMissing('penilaian_pre_induksi', [
            'no_rawat' => '2026/08/19/000001',
            'tanggal' => '2026-08-19 09:00:00',
        ]);

        $this->assertDatabaseMissing('berkas_digital_perawatan', [
            'no_rawat' => '2026/08/19/000001',
            'kode' => 'A07',
            'lokasi_file' => 'pages/upload/PENILAIAN_PRE_INDUKSI_2026-08-19-000001_20260819090000.pdf',
        ]);

        $this->assertDatabaseHas('berkas_digital_perawatan', [
            'no_rawat' => '2026/08/19/000001',
            'kode' => 'A07',
            'lokasi_file' => 'pages/upload/LAPORAN_ANESTESI_2026-08-19-000001.pdf',
        ]);
    }

    /**
     * Test 6: test_controller_upload_pdf_endpoint_success_and_error
     * Tests controller JSON contract matching MasterEkg.
     */
    public function test_controller_upload_pdf_endpoint_success_and_error(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        // Subcase A: Success
        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => Http::response([
                'success' => true,
                'message' => 'Upload berhasil',
            ], 200),
        ]);

        $response = $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->post(route('penilaian-pre-induksi.upload-pdf'), [
                'no_rawat' => '2026/08/19/000001',
                'tanggal' => '2026-08-19 09:00:00',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'pdf_url',
                'is_replaced',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Berkas penilaian pre induksi berhasil disimpan dan diunggah.',
                'is_replaced' => false,
            ]);

        $responseData = $response->json();
        $this->assertNotNull($responseData['pdf_url']);
        $this->assertStringContainsString('https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/pages/upload/PENILAIAN_PRE_INDUKSI_2026-08-19-000001_', $responseData['pdf_url']);
        $this->assertStringEndsWith('.pdf', $responseData['pdf_url']);

        // Subcase B: Missing no_rawat
        $missingParamResponse = $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->post(route('penilaian-pre-induksi.upload-pdf'), []);

        $missingParamResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Parameter no_rawat is required.',
                'pdf_url' => null,
                'is_replaced' => false,
            ]);

        // Subcase C: Server error response
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => Http::response([
                'success' => false,
                'message' => 'Internal server error on webapps',
            ], 200),
        ]);

        $errorResponse = $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->post(route('penilaian-pre-induksi.upload-pdf'), [
                'no_rawat' => '2026/08/19/000001',
                'tanggal' => '2026-08-19 09:00:00',
            ]);

        $errorResponse->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Internal server error on webapps',
                'pdf_url' => null,
                'is_replaced' => false,
            ]);
    }

    /**
     * Test 7: test_controller_download_pdf_streams_valid_pdf
     * Tests binary PDF response header and content.
     */
    public function test_controller_download_pdf_streams_valid_pdf(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
        ]);

        $response = $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->get(route('penilaian-pre-induksi.download-pdf', [
                'no_rawat' => '2026/08/19/000001',
                'tanggal' => '2026-08-19 09:00:00',
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
