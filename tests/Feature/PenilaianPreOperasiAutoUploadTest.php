<?php

namespace Tests\Feature;

use App\Http\Livewire\Component\PenilaianPreOperasi;
use App\Services\PenilaianPreOperasiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class PenilaianPreOperasiAutoUploadTest extends TestCase
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

        $this->createFixtureTable('template_laboratorium', function ($table) {
            $table->string('id_template')->primary();
            $table->string('Pemeriksaan');
            $table->string('satuan')->nullable();
        });

        $this->createFixtureTable('detail_periksa_lab', function ($table) {
            $table->string('no_rawat');
            $table->string('id_template');
            $table->string('nilai')->nullable();
            $table->date('tgl_periksa')->nullable();
            $table->time('jam')->nullable();
        });

        $this->createFixtureTable('hasil_radiologi', function ($table) {
            $table->string('no_rawat');
            $table->text('hasil')->nullable();
            $table->date('tgl_periksa')->nullable();
            $table->time('jam')->nullable();
        });

        $this->createFixtureTable('penilaian_pre_operasi', function ($table) {
            $table->string('no_rawat');
            $table->dateTime('tanggal');
            $table->string('kd_dokter');
            $table->text('ringkasan_klinik')->nullable();
            $table->text('pemeriksaan_fisik')->nullable();
            $table->text('pemeriksaan_diagnostik')->nullable();
            $table->text('diagnosa_pre_operasi')->nullable();
            $table->text('rencana_tindakan_bedah')->nullable();
            $table->text('hal_hal_yang_perludi_persiapkan')->nullable();
            $table->text('terapi_pre_operasi')->nullable();
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
            'nm_poli' => 'Poli Bedah',
        ]);

        DB::table('dokter')->insert([
            'kd_dokter' => 'D001',
            'nm_dokter' => 'Dr. Operator Bedah',
            'status' => '1',
        ]);

        DB::table('pasien')->insert([
            'no_rkm_medis' => '000001',
            'nm_pasien' => 'Tn. Pasien Uji',
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

        DB::table('penilaian_pre_operasi')->insert([
            'no_rawat' => '2026/08/19/000001',
            'tanggal' => '2026-08-19 09:00:00',
            'kd_dokter' => 'D001',
            'ringkasan_klinik' => 'Nyeri perut kanan bawah akut',
            'pemeriksaan_fisik' => 'Nyeri tekan McBurney (+)',
            'pemeriksaan_diagnostik' => 'USG: Appendicitis akut',
            'diagnosa_pre_operasi' => 'Appendicitis Akut',
            'rencana_tindakan_bedah' => 'Appendectomy Eksplorasi',
            'hal_hal_yang_perludi_persiapkan' => 'Puasa 6 jam, antibiotik profilaksis',
            'terapi_pre_operasi' => 'IVFD RL 20 tpm, Ceftriaxone 1g IV',
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
            throw new \LogicException('PenilaianPreOperasiAutoUploadTest fixtures may only run in the testing environment.');
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
     * Test 1: test_service_generates_pdf_and_uploads_with_code_a05
     * Http::fake() verifies payload:
     * - kd_berkas == 'A05'
     * - no_rawat == '2026/08/19/000001'
     * - base64 PDF starting with %PDF-
     * - filename starts with PENILAIAN_PRE_OPERASI_
     */
    public function test_service_generates_pdf_and_uploads_with_code_a05(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => Http::response([
                'success' => true,
                'message' => 'Upload berhasil',
            ], 200),
        ]);

        $service = app(PenilaianPreOperasiService::class);
        $result = $service->generateAndUploadPdf('2026/08/19/000001', '2026-08-19 09:00:00');

        $this->assertTrue($result['success']);
        $this->assertSame('Berkas penilaian pre operasi berhasil disimpan dan diunggah.', $result['message']);
        $this->assertNotNull($result['pdf_url']);
        $this->assertStringStartsWith('https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/pages/upload/PENILAIAN_PRE_OPERASI_2026-08-19-000001_', $result['pdf_url']);
        $this->assertStringEndsWith('.pdf', $result['pdf_url']);

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php') {
                return false;
            }

            $hasCorrectCode = ($request['kd_berkas'] ?? null) === 'A05';
            $hasCorrectNoRawat = ($request['no_rawat'] ?? null) === '2026/08/19/000001';
            $hasCorrectPrefix = str_starts_with($request['nama_file'] ?? '', 'PENILAIAN_PRE_OPERASI_2026-08-19-000001_');

            $decodedFile = base64_decode($request['file'] ?? '', true);
            $isValidPdf = $decodedFile !== false && str_starts_with($decodedFile, '%PDF-');

            return $hasCorrectCode && $hasCorrectNoRawat && $hasCorrectPrefix && $isValidPdf;
        });
    }

    /**
     * Test 2: test_livewire_simpan_triggers_auto_upload_and_success_alert
     * Livewire::test(PenilaianPreOperasi::class) calling simpan().
     */
    public function test_livewire_simpan_triggers_auto_upload_and_success_alert(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => Http::response([
                'success' => true,
                'message' => 'Upload berhasil',
            ], 200),
        ]);

        Livewire::test(PenilaianPreOperasi::class, [
            'noRawat' => '2026/08/19/000001',
            'noRm' => '000001',
        ])
            ->set('tanggal', '2026-08-19T10:00')
            ->set('ringkasan_klinik', 'Pasien datang dengan keluhan nyeri perut bawah')
            ->set('pemeriksaan_fisik', 'Nyeri tekan positif kuadran kanan bawah')
            ->set('pemeriksaan_diagnostik', 'Leukositosis 14.000')
            ->set('diagnosa_pre_operasi', 'Appendicitis Perforasi')
            ->set('rencana_tindakan_bedah', 'Laparotomi Appendectomy')
            ->set('hal_hal_yang_perludi_persiapkan', 'Sedia darah 2 kolf, ICU standby')
            ->set('terapi_pre_operasi', 'Ceftriaxone 1g, Metronidazole 500mg')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('alert', function ($eventName, $data) {
                return ($data['type'] ?? '') === 'success'
                    && str_contains($data['message'] ?? '', 'Penilaian pre operasi berhasil disimpan');
            });

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php') {
                return false;
            }

            return ($request['kd_berkas'] ?? null) === 'A05'
                && ($request['no_rawat'] ?? null) === '2026/08/19/000001'
                && str_starts_with($request['nama_file'] ?? '', 'PENILAIAN_PRE_OPERASI_2026-08-19-000001_');
        });

        $this->assertDatabaseHas('penilaian_pre_operasi', [
            'no_rawat' => '2026/08/19/000001',
            'diagnosa_pre_operasi' => 'Appendicitis Perforasi',
            'rencana_tindakan_bedah' => 'Laparotomi Appendectomy',
            'kd_dokter' => 'D001',
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

        Livewire::test(PenilaianPreOperasi::class, [
            'noRawat' => '2026/08/19/000001',
            'noRm' => '000001',
        ])
            ->call('edit', '2026-08-19 09:00:00')
            ->set('diagnosa_pre_operasi', 'Appendicitis Akut Revisi')
            ->set('rencana_tindakan_bedah', 'Appendectomy Laparoskopi')
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

            return ($request['kd_berkas'] ?? null) === 'A05'
                && ($request['no_rawat'] ?? null) === '2026/08/19/000001'
                && str_starts_with($request['nama_file'] ?? '', 'PENILAIAN_PRE_OPERASI_2026-08-19-000001_');
        });

        $this->assertDatabaseHas('penilaian_pre_operasi', [
            'no_rawat' => '2026/08/19/000001',
            'tanggal' => '2026-08-19 09:00:00',
            'diagnosa_pre_operasi' => 'Appendicitis Akut Revisi',
            'rencana_tindakan_bedah' => 'Appendectomy Laparoskopi',
        ]);
    }

    /**
     * Test 4: test_upload_replaces_old_penilaian_pre_operasi_files_and_preserves_other_a05_documents
     * Tests cleanup of old assessment entries while preserving non-penilaian A05 records.
     */
    public function test_upload_replaces_old_penilaian_pre_operasi_files_and_preserves_other_a05_documents(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        // Pre-populate berkas_digital_perawatan with:
        // 1. Old penilaian pre operasi file for patient 000001
        // 2. Another A05 surgical document for patient 000001 (e.g. LAPORAN_OPERASI_...)
        // 3. Penilaian file for a different patient 000002
        DB::table('berkas_digital_perawatan')->insert([
            [
                'no_rawat' => '2026/08/19/000001',
                'kode' => 'A05',
                'lokasi_file' => 'pages/upload/PENILAIAN_PRE_OPERASI_2026-08-19-000001_20260819080000.pdf',
            ],
            [
                'no_rawat' => '2026/08/19/000001',
                'kode' => 'A05',
                'lokasi_file' => 'pages/upload/LAPORAN_OPERASI_2026-08-19-000001.pdf',
            ],
            [
                'no_rawat' => '2026/08/19/000002',
                'kode' => 'A05',
                'lokasi_file' => 'pages/upload/PENILAIAN_PRE_OPERASI_2026-08-19-000002_20260819080000.pdf',
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

        $service = app(PenilaianPreOperasiService::class);
        $result = $service->generateAndUploadPdf('2026/08/19/000001', '2026-08-19 09:00:00');

        $this->assertTrue($result['success']);
        $this->assertTrue($result['is_replaced']);
        $this->assertSame('Berkas penilaian pre operasi berhasil disimpan dan berkas digital diperbarui.', $result['message']);

        // Assert old penilaian file for 000001 was deleted
        $this->assertDatabaseMissing('berkas_digital_perawatan', [
            'no_rawat' => '2026/08/19/000001',
            'kode' => 'A05',
            'lokasi_file' => 'pages/upload/PENILAIAN_PRE_OPERASI_2026-08-19-000001_20260819080000.pdf',
        ]);

        // Assert other A05 surgical document for 000001 is PRESERVED
        $this->assertDatabaseHas('berkas_digital_perawatan', [
            'no_rawat' => '2026/08/19/000001',
            'kode' => 'A05',
            'lokasi_file' => 'pages/upload/LAPORAN_OPERASI_2026-08-19-000001.pdf',
        ]);

        // Assert penilaian file for patient 000002 is PRESERVED
        $this->assertDatabaseHas('berkas_digital_perawatan', [
            'no_rawat' => '2026/08/19/000002',
            'kode' => 'A05',
            'lokasi_file' => 'pages/upload/PENILAIAN_PRE_OPERASI_2026-08-19-000002_20260819080000.pdf',
        ]);

        // Assert exactly 1 penilaian record remains for patient 000001
        $countPenilaian001 = DB::table('berkas_digital_perawatan')
            ->where('no_rawat', '2026/08/19/000001')
            ->where('kode', 'A05')
            ->where('lokasi_file', 'like', '%PENILAIAN_PRE_OPERASI_2026-08-19-000001_%')
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
                'kode' => 'A05',
                'lokasi_file' => 'pages/upload/PENILAIAN_PRE_OPERASI_2026-08-19-000001_20260819090000.pdf',
            ],
            [
                'no_rawat' => '2026/08/19/000001',
                'kode' => 'A05',
                'lokasi_file' => 'pages/upload/LAPORAN_OPERASI_2026-08-19-000001.pdf',
            ],
        ]);

        Livewire::test(PenilaianPreOperasi::class, [
            'noRawat' => '2026/08/19/000001',
            'noRm' => '000001',
        ])
            ->call('confirmHapus', '2026-08-19 09:00:00')
            ->call('hapus')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('alert', function ($eventName, $data) {
                return ($data['type'] ?? '') === 'success'
                    && str_contains($data['message'] ?? '', 'Berhasil hapus penilaian pre operasi');
            });

        $this->assertDatabaseMissing('penilaian_pre_operasi', [
            'no_rawat' => '2026/08/19/000001',
            'tanggal' => '2026-08-19 09:00:00',
        ]);

        $this->assertDatabaseMissing('berkas_digital_perawatan', [
            'no_rawat' => '2026/08/19/000001',
            'kode' => 'A05',
            'lokasi_file' => 'pages/upload/PENILAIAN_PRE_OPERASI_2026-08-19-000001_20260819090000.pdf',
        ]);

        $this->assertDatabaseHas('berkas_digital_perawatan', [
            'no_rawat' => '2026/08/19/000001',
            'kode' => 'A05',
            'lokasi_file' => 'pages/upload/LAPORAN_OPERASI_2026-08-19-000001.pdf',
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
            ->post(route('penilaian-pre-operasi.upload-pdf'), [
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
                'message' => 'Berkas penilaian pre operasi berhasil disimpan dan diunggah.',
                'is_replaced' => false,
            ]);

        $responseData = $response->json();
        $this->assertNotNull($responseData['pdf_url']);
        $this->assertStringContainsString('https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/pages/upload/PENILAIAN_PRE_OPERASI_2026-08-19-000001_', $responseData['pdf_url']);
        $this->assertStringEndsWith('.pdf', $responseData['pdf_url']);

        // Subcase B: Missing no_rawat
        $missingParamResponse = $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->post(route('penilaian-pre-operasi.upload-pdf'), []);

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
            ->post(route('penilaian-pre-operasi.upload-pdf'), [
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
            ->get(route('penilaian-pre-operasi.download-pdf', [
                'no_rawat' => '2026/08/19/000001',
                'tanggal' => '2026-08-19 09:00:00',
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    /**
     * Test 8: test_pdf_template_omits_tanggal_penilaian_row_but_keeps_signature_timestamp
     * Verifies TANGGAL PENILAIAN row is omitted while signature timestamp is kept.
     */
    public function test_pdf_template_omits_tanggal_penilaian_row_but_keeps_signature_timestamp(): void
    {
        $penilaian = (object) [
            'no_rawat' => '2026/08/19/000001',
            'no_rkm_medis' => '000001',
            'nama_pasien' => 'Tn. Pasien Uji',
            'tgl_lahir' => '1990-01-02',
            'jk' => 'L',
            'nama_dokter' => 'Dr. Operator Bedah',
            'kd_dokter' => 'D001',
            'tanggal' => '2026-08-19 08:30:00',
            'ringkasan_klinik' => 'Ringkasan klinik uji',
            'pemeriksaan_fisik' => 'Fisik uji',
            'pemeriksaan_diagnostik' => 'Diagnostik uji',
            'diagnosa_pre_operasi' => 'Diagnosa uji',
            'rencana_tindakan_bedah' => 'Tindakan uji',
            'hal_hal_yang_perludi_persiapkan' => 'Persiapan uji',
            'terapi_pre_operasi' => 'Terapi uji',
        ];

        $view = view('prints.penilaian-pre-operasi-pdf', [
            'penilaian' => $penilaian,
            'setting' => (object) [
                'nama_instansi' => 'RUMAH SAKIT BHAYANGKARA TK. III NGANJUK',
            ],
            'tanggalPenilaian' => '19 Agustus 2026 08:30',
            'qrBase64' => 'data:image/png;base64,test',
        ])->render();

        $this->assertStringNotContainsString('TANGGAL PENILAIAN', $view);
        $this->assertStringContainsString('Waktu TTD: 19 Agustus 2026 08:30', $view);
    }
}
