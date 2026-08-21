<?php

namespace Tests\Feature;

use App\Http\Controllers\PenandaanAreaOperasiController;
use App\Services\PenandaanAreaOperasiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PenandaanAreaOperasiServiceTest extends TestCase
{
    private $createdFixtureTables = [];
    private $record1Id;
    private $record2Id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requireTestingEnvironment();

        $this->createFixtureTable('dokter', function ($table) {
            $table->string('kd_dokter')->primary();
            $table->string('nm_dokter');
        });
        $this->createFixtureTable('pasien', function ($table) {
            $table->string('no_rkm_medis')->primary();
            $table->string('nm_pasien');
            $table->date('tgl_lahir')->nullable();
            $table->enum('jk', ['L', 'P'])->default('L');
        });
        $this->createFixtureTable('reg_periksa', function ($table) {
            $table->string('no_rawat')->primary();
            $table->string('no_rkm_medis');
            $table->string('umurdaftar')->default('30');
            $table->string('sttsumur')->default('Th');
            $table->date('tgl_registrasi')->nullable();
        });
        $this->createFixtureTable('penandaan_area_operasi', function ($table) {
            $table->id();
            $table->string('no_rawat');
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

        DB::table('pasien')->insert([
            ['no_rkm_medis' => '000001', 'nm_pasien' => 'Patient One', 'tgl_lahir' => '1990-01-02', 'jk' => 'L'],
        ]);
        DB::table('reg_periksa')->insert([
            ['no_rawat' => '2026/08/19/000001', 'no_rkm_medis' => '000001', 'umurdaftar' => '36', 'sttsumur' => 'Th', 'tgl_registrasi' => '2026-08-19'],
        ]);
        DB::table('dokter')->insert([
            'kd_dokter' => 'D001', 'nm_dokter' => 'Dr. Operator Spesialis',
        ]);
        DB::table('setting')->insert([
            'nama_instansi' => 'RUMAH SAKIT BHAYANGKARA TK. III NGANJUK',
            'alamat_instansi' => 'Jl. Langsep No. 1',
            'kabupaten' => 'NGANJUK',
            'propinsi' => 'JAWA TIMUR',
        ]);

        $this->record1Id = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => '2026/08/19/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'Patient One',
            'tgl_lahir' => '1990-01-02',
            'prosedur_operasi' => 'Prosedur Operasi Pertama',
            'operator_id' => 'D001',
            'nama_operator' => 'Dr. Operator Spesialis',
            'tanggal_penandaan' => '2026-08-19 09:00:00',
            'gambar_penandaan_base64' => json_encode([
                'body_front' => 'data:image/png;base64,' . base64_encode('fake-png-data-1'),
            ]),
            'format_gambar' => 'image/png',
            'created_at' => now()->subHours(5),
            'updated_at' => now()->subHours(5),
        ]);

        $this->record2Id = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => '2026/08/19/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'Patient One',
            'tgl_lahir' => '1990-01-02',
            'prosedur_operasi' => 'Prosedur Operasi Kedua',
            'operator_id' => 'D001',
            'nama_operator' => 'Dr. Operator Spesialis',
            'tanggal_penandaan' => '2026-08-19 14:00:00',
            'gambar_penandaan_base64' => json_encode([
                'body_back' => 'data:image/png;base64,' . base64_encode('fake-png-data-2'),
            ]),
            'format_gambar' => 'image/png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
            throw new \LogicException('PenandaanAreaOperasiServiceTest fixtures may only run in the testing environment.');
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

    public function test_generate_pdf_without_id_fetches_latest_record(): void
    {
        Http::fake([
            'https://api.qrserver.com/*' => Http::response('fake-qr-png-binary', 200),
        ]);

        $service = app(PenandaanAreaOperasiService::class);
        $pdfBinary = $service->generatePdf('2026/08/19/000001');

        $this->assertNotEmpty($pdfBinary);
        $this->assertStringStartsWith('%PDF-', $pdfBinary);
    }

    public function test_generate_pdf_with_id_fetches_specific_record(): void
    {
        Http::fake([
            'https://api.qrserver.com/*' => Http::response('fake-qr-png-binary', 200),
        ]);

        $service = app(PenandaanAreaOperasiService::class);
        $pdfBinary = $service->generatePdf('2026/08/19/000001', $this->record1Id);

        $this->assertNotEmpty($pdfBinary);
        $this->assertStringStartsWith('%PDF-', $pdfBinary);
    }

    public function test_it_throws_exception_when_generating_pdf_for_unknown_visit_or_id(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Data penandaan area operasi tidak ditemukan');

        $service = app(PenandaanAreaOperasiService::class);
        $service->generatePdf('2026/99/99/999999');
    }

    public function test_it_throws_exception_when_generating_pdf_for_non_existent_id(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Data penandaan area operasi tidak ditemukan');

        $service = app(PenandaanAreaOperasiService::class);
        $service->generatePdf('2026/08/19/000001', 999999);
    }

    public function test_generate_token_produces_consistent_hmac_with_and_without_id(): void
    {
        $tokenNoId = PenandaanAreaOperasiController::generateToken('2026/08/19/000001');
        $tokenWithDash = PenandaanAreaOperasiController::generateToken('2026-08-19-000001');
        $this->assertSame($tokenNoId, $tokenWithDash);
        $this->assertSame(32, strlen($tokenNoId));
        $this->assertTrue(ctype_xdigit($tokenNoId));

        $tokenId1 = PenandaanAreaOperasiController::generateToken('2026/08/19/000001', $this->record1Id);
        $tokenId2 = PenandaanAreaOperasiController::generateToken('2026/08/19/000001', $this->record2Id);

        $this->assertNotEquals($tokenNoId, $tokenId1);
        $this->assertNotEquals($tokenId1, $tokenId2);
        $this->assertSame(32, strlen($tokenId1));

        $tokenId1Dash = PenandaanAreaOperasiController::generateToken('2026-08-19-000001', $this->record1Id);
        $this->assertSame($tokenId1, $tokenId1Dash);
    }

    public function test_upload_pdf_includes_id_in_filename_and_replaces_old_files_by_prefix(): void
    {
        $sanitizedNoRawat = '2026-08-19-000001';
        $prefix1 = "PENANDAAN_OPERASI_{$sanitizedNoRawat}_{$this->record1Id}_";
        $prefix2 = "PENANDAAN_OPERASI_{$sanitizedNoRawat}_{$this->record2Id}_";

        DB::table('berkas_digital_perawatan')->insert([
            [
                'no_rawat' => '2026/08/19/000001',
                'kode' => 'A05',
                'lokasi_file' => "pages/upload/{$prefix1}20260819080000.pdf",
            ],
            [
                'no_rawat' => '2026/08/19/000001',
                'kode' => 'A05',
                'lokasi_file' => "pages/upload/{$prefix2}20260819080000.pdf",
            ],
        ]);

        Http::fake([
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

        $service = app(PenandaanAreaOperasiService::class);
        $pdfBinary = '%PDF-fake-content-record-1';
        $result = $service->uploadPdf('2026/08/19/000001', $pdfBinary, $this->record1Id);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['is_replaced']);
        $this->assertStringContainsString("PENANDAAN_OPERASI_{$sanitizedNoRawat}_{$this->record1Id}_", $result['pdf_url']);

        // Old file for record1 must be removed
        $this->assertDatabaseMissing('berkas_digital_perawatan', [
            'no_rawat' => '2026/08/19/000001',
            'kode' => 'A05',
            'lokasi_file' => "pages/upload/{$prefix1}20260819080000.pdf",
        ]);

        // Old file for record2 must be PRESERVED
        $this->assertDatabaseHas('berkas_digital_perawatan', [
            'no_rawat' => '2026/08/19/000001',
            'kode' => 'A05',
            'lokasi_file' => "pages/upload/{$prefix2}20260819080000.pdf",
        ]);
    }

    public function test_generate_and_upload_orchestration_with_id(): void
    {
        Http::fake([
            'https://api.qrserver.com/*' => Http::response('fake-qr-png-binary', 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => Http::response([
                'success' => true,
                'message' => 'Berhasil disimpan',
            ], 200),
        ]);

        $service = app(PenandaanAreaOperasiService::class);
        $result = $service->generateAndUploadPdf('2026/08/19/000001', $this->record1Id);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['pdf_url']);
        $this->assertStringContainsString("PENANDAAN_OPERASI_2026-08-19-000001_{$this->record1Id}_", $result['pdf_url']);
    }

    public function test_cetak_endpoint_supports_id_param_and_latest_default(): void
    {
        $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->get(route('penandaan-area-operasi.cetak', [
                'no_rawat' => '2026/08/19/000001',
                'id' => $this->record1Id,
            ]))
            ->assertOk()
            ->assertSee('Prosedur Operasi Pertama')
            ->assertDontSee('Prosedur Operasi Kedua');

        $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->get(route('penandaan-area-operasi.cetak', [
                'no_rawat' => '2026/08/19/000001',
                'id' => $this->record2Id,
            ]))
            ->assertOk()
            ->assertSee('Prosedur Operasi Kedua')
            ->assertDontSee('Prosedur Operasi Pertama');

        // Without id, defaults to latest (Record 2)
        $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->get(route('penandaan-area-operasi.cetak', [
                'no_rawat' => '2026/08/19/000001',
            ]))
            ->assertOk()
            ->assertSee('Prosedur Operasi Kedua');
    }

    public function test_download_pdf_endpoint_supports_id_param(): void
    {
        Http::fake([
            'https://api.qrserver.com/*' => Http::response('fake-qr-png-binary', 200),
        ]);

        $response = $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->get(route('penandaan-area-operasi.download-pdf', [
                'no_rawat' => '2026/08/19/000001',
                'id' => $this->record1Id,
            ]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('PENANDAAN_OPERASI_2026-08-19-000001_' . $this->record1Id . '_', $disposition);
    }

    public function test_upload_pdf_endpoint_supports_id_param(): void
    {
        Http::fake([
            'https://api.qrserver.com/*' => Http::response('fake-qr-png-binary', 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => Http::response([
                'success' => true,
                'message' => 'Upload berhasil',
            ], 200),
        ]);

        $response = $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->post(route('penandaan-area-operasi.upload-pdf'), [
                'no_rawat' => '2026/08/19/000001',
                'id' => $this->record1Id,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Berkas penandaan area operasi berhasil disimpan dan diunggah.',
            ]);

        $this->assertStringContainsString("PENANDAAN_OPERASI_2026-08-19-000001_{$this->record1Id}_", $response->json('pdf_url'));
    }

    public function test_verifikasi_resolves_token_for_specific_record_with_id(): void
    {
        $token1 = PenandaanAreaOperasiController::generateToken('2026/08/19/000001', $this->record1Id);
        $token2 = PenandaanAreaOperasiController::generateToken('2026/08/19/000001', $this->record2Id);

        $this->get(route('penandaan-area-operasi.verifikasi', ['token' => $token1]))
            ->assertOk()
            ->assertSee('Prosedur Operasi Pertama')
            ->assertDontSee('Prosedur Operasi Kedua');

        $this->get(route('penandaan-area-operasi.verifikasi', ['token' => $token2]))
            ->assertOk()
            ->assertSee('Prosedur Operasi Kedua')
            ->assertDontSee('Prosedur Operasi Pertama');
    }

    public function test_verifikasi_resolves_legacy_token_without_id(): void
    {
        $legacyToken = PenandaanAreaOperasiController::generateToken('2026/08/19/000001');

        $this->get(route('penandaan-area-operasi.verifikasi', ['token' => $legacyToken]))
            ->assertOk()
            ->assertSee('Dokumen Terverifikasi Sah secara Digital');
    }
}
