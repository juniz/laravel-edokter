<?php

namespace Tests\Feature;

use App\Services\PenandaanAreaOperasiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PenandaanAreaOperasiServiceTest extends TestCase
{
    private $createdFixtureTables = [];

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

        DB::table('penandaan_area_operasi')->insert([
            'no_rawat' => '2026/08/19/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'Patient One',
            'tgl_lahir' => '1990-01-02',
            'prosedur_operasi' => 'Laparatomi Eksplorasi',
            'operator_id' => 'D001',
            'nama_operator' => 'Dr. Operator Spesialis',
            'tanggal_penandaan' => '2026-08-19 09:00:00',
            'gambar_penandaan_base64' => json_encode([
                'body_front' => 'data:image/png;base64,' . base64_encode('fake-png-data'),
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

    public function test_it_generates_valid_pdf_binary(): void
    {
        Http::fake([
            'https://api.qrserver.com/*' => Http::response('fake-qr-png-binary', 200),
        ]);

        $service = app(PenandaanAreaOperasiService::class);
        $pdfBinary = $service->generatePdf('2026/08/19/000001');

        $this->assertNotEmpty($pdfBinary);
        $this->assertStringStartsWith('%PDF-', $pdfBinary);
    }

    public function test_it_throws_exception_when_generating_pdf_for_unknown_visit(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Data penandaan area operasi tidak ditemukan');

        $service = app(PenandaanAreaOperasiService::class);
        $service->generatePdf('2026/99/99/999999');
    }

    public function test_it_uploads_pdf_successfully_to_webapps(): void
    {
        Http::fake([
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => Http::response([
                'success' => true,
                'message' => 'Upload berhasil',
            ], 200),
        ]);

        $service = app(PenandaanAreaOperasiService::class);
        $pdfBinary = '%PDF-fake-content';
        $result = $service->uploadPdf('2026/08/19/000001', $pdfBinary);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['pdf_url']);
        $this->assertStringContainsString('https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/pages/upload/PENANDAAN_OPERASI_2026-08-19-000001_', $result['pdf_url']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php'
                && $request['kd_berkas'] === 'A05'
                && $request['no_rawat'] === '2026/08/19/000001'
                && ! empty($request['file'])
                && str_starts_with($request['nama_file'], 'PENANDAAN_OPERASI_2026-08-19-000001_');
        });
    }

    public function test_it_handles_webapps_upload_failure_gracefully(): void
    {
        Http::fake([
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => Http::response([
                'success' => false,
                'message' => 'Disk full or permission denied',
            ], 200),
        ]);

        $service = app(PenandaanAreaOperasiService::class);
        $result = $service->uploadPdf('2026/08/19/000001', '%PDF-fake-content');

        $this->assertFalse($result['success']);
        $this->assertSame('Disk full or permission denied', $result['message']);
        $this->assertNull($result['pdf_url']);
    }

    public function test_generate_and_upload_orchestration(): void
    {
        Http::fake([
            'https://api.qrserver.com/*' => Http::response('fake-qr-png-binary', 200),
            'https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php' => Http::response([
                'success' => true,
                'message' => 'Berhasil disimpan',
            ], 200),
        ]);

        $service = app(PenandaanAreaOperasiService::class);
        $result = $service->generateAndUploadPdf('2026/08/19/000001');

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['pdf_url']);
    }
}
