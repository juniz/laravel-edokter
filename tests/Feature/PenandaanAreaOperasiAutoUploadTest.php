<?php

namespace Tests\Feature;

use App\Http\Livewire\Component\PenandaanAreaOperasi;
use App\Services\PenandaanAreaOperasiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class PenandaanAreaOperasiAutoUploadTest extends TestCase
{
    private const VALID_PNG_BASE64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

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
        });

        $this->createFixtureTable('penandaan_area_operasi', function ($table) {
            $table->id();
            $table->string('no_rawat')->unique();
            $table->string('no_rm');
            $table->string('nama_pasien');
            $table->date('tgl_lahir')->nullable();
            $table->text('prosedur_operasi');
            $table->string('operator_id')->nullable();
            $table->string('nama_operator');
            $table->dateTime('tanggal_penandaan');
            $table->longText('gambar_penandaan_base64');
            $table->string('format_gambar')->default('image/png');
            $table->timestamps();
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

        DB::table('penandaan_area_operasi')->insert([
            'no_rawat' => '2026/08/19/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'Tn. Pasien Uji',
            'tgl_lahir' => '1990-01-02',
            'prosedur_operasi' => 'Appendectomy Eksplorasi',
            'operator_id' => 'D001',
            'nama_operator' => 'Dr. Operator Bedah',
            'tanggal_penandaan' => '2026-08-19 09:00:00',
            'gambar_penandaan_base64' => json_encode([
                'body_front' => self::VALID_PNG_BASE64,
            ]),
            'format_gambar' => 'image/png',
            'created_at' => now(),
            'updated_at' => now(),
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
            throw new \LogicException('PenandaanAreaOperasiAutoUploadTest fixtures may only run in the testing environment.');
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
     * Test 1: Service PDF generation and upload with Http::fake() verifies:
     * - kd_berkas == 'A05'
     * - payload contains valid base64-encoded PDF (decodable starting with %PDF-)
     * - nama_file starts with 'PENANDAAN_OPERASI_'
     * - no_rawat matches expected format
     */
    public function test_service_generates_pdf_and_uploads_to_webapps_with_code_A05(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => Http::response([
                'success' => true,
                'message' => 'Upload berhasil',
            ], 200),
        ]);

        $service = app(PenandaanAreaOperasiService::class);
        $result = $service->generateAndUploadPdf('2026/08/19/000001');

        $this->assertTrue($result['success']);
        $this->assertSame('Berkas penandaan area operasi berhasil disimpan dan diunggah.', $result['message']);
        $this->assertNotNull($result['pdf_url']);
        $this->assertStringStartsWith('https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/pages/upload/PENANDAAN_OPERASI_2026-08-19-000001_', $result['pdf_url']);
        $this->assertStringEndsWith('.pdf', $result['pdf_url']);

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php') {
                return false;
            }

            $hasCorrectCode = ($request['kd_berkas'] ?? null) === 'A05';
            $hasCorrectNoRawat = ($request['no_rawat'] ?? null) === '2026/08/19/000001';
            $hasCorrectPrefix = str_starts_with($request['nama_file'] ?? '', 'PENANDAAN_OPERASI_2026-08-19-000001_');

            $decodedFile = base64_decode($request['file'] ?? '', true);
            $isValidPdf = $decodedFile !== false && str_starts_with($decodedFile, '%PDF-');

            return $hasCorrectCode && $hasCorrectNoRawat && $hasCorrectPrefix && $isValidPdf;
        });
    }

    /**
     * Test 2: Livewire PenandaanAreaOperasi component simpan() triggers automated upload and sends success alert.
     */
    public function test_livewire_simpan_triggers_automated_upload(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => Http::response([
                'success' => true,
                'message' => 'Upload berhasil',
            ], 200),
        ]);

        $payload = ['body_front' => self::VALID_PNG_BASE64];

        Livewire::test(PenandaanAreaOperasi::class, [
            'noRawat' => '2026/08/19/000001',
            'noRm' => '000001',
        ])
            ->set('prosedurOperasi', 'Appendectomy Eksplorasi Baru')
            ->set('gambarPenandaanBase64', $payload)
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('alert', function ($eventName, $data) {
                return ($data['type'] ?? '') === 'success'
                    && str_contains($data['message'] ?? '', 'Penandaan area operasi berhasil disimpan dan diunggah');
            });

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php') {
                return false;
            }

            return ($request['kd_berkas'] ?? null) === 'A05'
                && ($request['no_rawat'] ?? null) === '2026/08/19/000001'
                && str_starts_with($request['nama_file'] ?? '', 'PENANDAAN_OPERASI_2026-08-19-000001_');
        });

        $this->assertDatabaseHas('penandaan_area_operasi', [
            'no_rawat' => '2026/08/19/000001',
            'prosedur_operasi' => 'Appendectomy Eksplorasi Baru',
            'nama_pasien' => 'Tn. Pasien Uji',
            'nama_operator' => 'Dr. Operator Bedah',
        ]);
    }

    /**
     * Test 3: Controller uploadPdf endpoint returns JSON format matching MasterEkgController::cetakEkg.
     */
    public function test_controller_upload_pdf_returns_format_matching_master_ekg(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => Http::response([
                'success' => true,
                'message' => 'Upload berhasil',
            ], 200),
        ]);

        $response = $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->post(route('penandaan-area-operasi.upload-pdf'), [
                'no_rawat' => '2026/08/19/000001',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'pdf_url',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Berkas penandaan area operasi berhasil disimpan dan diunggah.',
            ]);

        $responseData = $response->json();
        $this->assertNotNull($responseData['pdf_url']);
        $this->assertStringContainsString('https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/pages/upload/PENANDAAN_OPERASI_2026-08-19-000001_', $responseData['pdf_url']);
        $this->assertStringEndsWith('.pdf', $responseData['pdf_url']);
    }

    /**
     * Test 4: Controller uploadPdf handles upload server errors gracefully.
     */
    public function test_controller_upload_pdf_handles_upload_server_error(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => Http::response([
                'success' => false,
                'message' => 'Internal server error on webapps',
            ], 200),
        ]);

        $response = $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->post(route('penandaan-area-operasi.upload-pdf'), [
                'no_rawat' => '2026/08/19/000001',
            ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Internal server error on webapps',
                'pdf_url' => null,
            ]);
    }

    /**
     * Test 5: Controller uploadPdf handles missing parameter.
     */
    public function test_controller_upload_pdf_requires_no_rawat(): void
    {
        $response = $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->post(route('penandaan-area-operasi.upload-pdf'), []);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Parameter no_rawat is required.',
                'pdf_url' => null,
            ]);
    }

    /**
     * Test 6: Service handles missing record during generateAndUploadPdf.
     */
    public function test_service_handles_missing_record_gracefully(): void
    {
        $service = app(PenandaanAreaOperasiService::class);
        $result = $service->generateAndUploadPdf('2026/99/99/999999');

        $this->assertFalse($result['success']);
        $this->assertNull($result['pdf_url']);
        $this->assertStringContainsString('Data penandaan area operasi tidak ditemukan', $result['message']);
    }

    /**
     * Test 7: Controller downloadPdf generates and streams PDF response.
     */
    public function test_controller_download_pdf_success(): void
    {
        $response = $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->get(route('penandaan-area-operasi.download-pdf', [
                'no_rawat' => '2026/08/19/000001',
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}

