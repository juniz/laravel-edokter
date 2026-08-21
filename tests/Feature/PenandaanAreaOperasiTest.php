<?php

namespace Tests\Feature;

use App\Http\Livewire\Component\PenandaanAreaOperasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class PenandaanAreaOperasiTest extends TestCase
{
    private const VALID_PNG_BASE64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    private $createdFixtureTables = [];

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
        $this->createFixtureTable('template_hasil_ekg', function ($table) {
            $table->id();
            $table->string('nama_template')->nullable();
            $table->text('template')->nullable();
            $table->timestamps();
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

        DB::table('penjab')->insert([
            'kd_pj' => 'BPJ',
            'png_jawab' => 'BPJS Kesehatan',
        ]);
        DB::table('poliklinik')->insert([
            'kd_poli' => 'POL01',
            'nm_poli' => 'Poli Bedah',
        ]);
        DB::table('pasien')->insert([
            ['no_rkm_medis' => '000001', 'nm_pasien' => 'Patient One', 'tgl_lahir' => '1990-01-02', 'jk' => 'L', 'kd_pj' => 'BPJ', 'kd_kel' => null, 'kd_kec' => null, 'kd_kab' => null, 'kd_prop' => null],
            ['no_rkm_medis' => '000002', 'nm_pasien' => 'Patient Two', 'tgl_lahir' => '1991-02-03', 'jk' => 'P', 'kd_pj' => 'BPJ', 'kd_kel' => null, 'kd_kec' => null, 'kd_kab' => null, 'kd_prop' => null],
        ]);
        DB::table('reg_periksa')->insert([
            ['no_rawat' => '2026/08/04/000001', 'no_rkm_medis' => '000001', 'umurdaftar' => '36', 'sttsumur' => 'Th', 'tgl_registrasi' => '2026-08-04', 'kd_pj' => 'BPJ', 'kd_poli' => 'POL01', 'kd_dokter' => 'D001'],
            ['no_rawat' => '2026/08/04/000002', 'no_rkm_medis' => '000002', 'umurdaftar' => '35', 'sttsumur' => 'Th', 'tgl_registrasi' => '2026-08-04', 'kd_pj' => 'BPJ', 'kd_poli' => 'POL01', 'kd_dokter' => 'D001'],
        ]);
        DB::table('dokter')->insert([
            'kd_dokter' => 'D001', 'nm_dokter' => 'Dr. Operator', 'status' => '1',
        ]);
        DB::table('setting')->insert([
            'nama_instansi' => 'RUMAH SAKIT BHAYANGKARA TK. III NGANJUK',
            'alamat_instansi' => 'Jl. Langsep No. 1',
            'kabupaten' => 'NGANJUK',
            'propinsi' => 'JAWA TIMUR',
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
            throw new \LogicException('PenandaanAreaOperasiTest fixtures may only run in the testing environment.');
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

    public function test_penandaan_table_has_required_columns_and_unique_visit(): void
    {
        $this->assertTrue(Schema::hasTable('penandaan_area_operasi'));
        $columns = Schema::getColumnListing('penandaan_area_operasi');

        $this->assertContains('gambar_penandaan_base64', $columns);
        $this->assertContains('nama_operator', $columns);
    }

    public function test_it_persists_a_valid_marking_payload_and_uploads_pdf(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/*' => Http::response([
                'success' => true,
                'message' => 'Upload berhasil',
            ], 200),
        ]);

        $payload = ['body_front' => self::VALID_PNG_BASE64];

        $this->withSession(['username' => 'D001']);

        Livewire::test(PenandaanAreaOperasi::class, [
            'noRawat' => '2026/08/04/000001', 'noRm' => '000001',
        ])
            ->set('prosedurOperasi', 'Appendectomy')
            ->set('gambarPenandaanBase64', $payload)
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('alert', function ($eventName, $data) {
                return ($data['type'] ?? '') === 'success'
                    && str_contains($data['message'] ?? '', 'Penandaan area operasi berhasil disimpan dan diunggah');
            });

        $this->assertDatabaseHas('penandaan_area_operasi', [
            'no_rawat' => '2026/08/04/000001',
            'gambar_penandaan_base64' => json_encode($payload),
            'nama_pasien' => 'Patient One',
            'nama_operator' => 'Dr. Operator',
        ]);
    }

    public function test_it_saves_marking_but_warns_when_upload_fails(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/*' => Http::response([
                'success' => false,
                'message' => 'Koneksi server gagal',
            ], 200),
        ]);

        $payload = ['body_front' => self::VALID_PNG_BASE64];

        $this->withSession(['username' => 'D001']);

        Livewire::test(PenandaanAreaOperasi::class, [
            'noRawat' => '2026/08/04/000001', 'noRm' => '000001',
        ])
            ->set('prosedurOperasi', 'Appendectomy')
            ->set('gambarPenandaanBase64', $payload)
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('alert', function ($eventName, $data) {
                return ($data['type'] ?? '') === 'warning'
                    && str_contains($data['message'] ?? '', 'gagal diunggah otomatis');
            });

        $this->assertDatabaseHas('penandaan_area_operasi', [
            'no_rawat' => '2026/08/04/000001',
            'gambar_penandaan_base64' => json_encode($payload),
        ]);
    }

    public function test_it_rejects_an_invalid_diagram_data_url(): void
    {
        Livewire::test(PenandaanAreaOperasi::class, [
            'noRawat' => '2026/08/04/000001', 'noRm' => '000001',
        ])
            ->set('prosedurOperasi', 'Appendectomy')
            ->set('gambarPenandaanBase64', ['body_front' => 'not-a-data-url'])
            ->call('simpan')
            ->assertHasErrors(['gambarPenandaanBase64']);
    }

    public function test_it_rejects_a_data_url_with_empty_decoded_png_bytes(): void
    {
        Livewire::test(PenandaanAreaOperasi::class, [
            'noRawat' => '2026/08/04/000001', 'noRm' => '000001',
        ])
            ->set('prosedurOperasi', 'Appendectomy')
            ->set('gambarPenandaanBase64', ['body_front' => 'data:image/png;base64,'])
            ->call('simpan')
            ->assertHasErrors(['gambarPenandaanBase64']);
    }

    public function test_it_requires_a_logged_in_operator(): void
    {
        session()->forget('username');

        Livewire::test(PenandaanAreaOperasi::class, [
            'noRawat' => '2026/08/04/000001', 'noRm' => '000001',
        ])
            ->set('prosedurOperasi', 'Appendectomy')
            ->set('gambarPenandaanBase64', ['body_front' => self::VALID_PNG_BASE64])
            ->call('simpan')
            ->assertHasErrors(['operator']);
    }

    public function test_it_updates_the_existing_marking(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/*' => Http::response([
                'success' => true,
                'message' => 'Upload berhasil',
            ], 200),
        ]);

        $firstPayload = ['body_front' => self::VALID_PNG_BASE64];
        $updatedPayload = ['body_back' => self::VALID_PNG_BASE64];

        $first = Livewire::test(PenandaanAreaOperasi::class, [
            'noRawat' => '2026/08/04/000001', 'noRm' => '000001',
        ]);
        $first->set('prosedurOperasi', 'First procedure')->set('gambarPenandaanBase64', $firstPayload)->call('simpan');

        $saved = DB::table('penandaan_area_operasi')->where('no_rawat', '2026/08/04/000001')->first();

        Livewire::test(PenandaanAreaOperasi::class, [
            'noRawat' => '2026/08/04/000001', 'noRm' => '000001',
        ])
            ->call('edit', $saved->id)
            ->set('prosedurOperasi', 'Updated procedure')
            ->set('gambarPenandaanBase64', $updatedPayload)
            ->call('update')
            ->assertHasNoErrors();

        $this->assertSame(1, DB::table('penandaan_area_operasi')->where('no_rawat', '2026/08/04/000001')->count());
        $this->assertDatabaseHas('penandaan_area_operasi', [
            'id' => $saved->id,
            'no_rawat' => '2026/08/04/000001',
            'prosedur_operasi' => 'Updated procedure',
            'gambar_penandaan_base64' => json_encode($updatedPayload),
        ]);
    }

    public function test_it_keeps_markings_isolated_by_visit_and_reloads_a_saved_payload(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/*' => Http::response([
                'success' => true,
                'message' => 'Upload berhasil',
            ], 200),
        ]);

        $firstPayload = ['body_front' => self::VALID_PNG_BASE64];
        $secondPayload = ['body_back' => self::VALID_PNG_BASE64];

        foreach ([
            ['2026/08/04/000001', '000001', 'First procedure', $firstPayload],
            ['2026/08/04/000002', '000002', 'Second procedure', $secondPayload],
        ] as [$noRawat, $noRm, $prosedur, $payload]) {
            Livewire::test(PenandaanAreaOperasi::class, compact('noRawat', 'noRm'))
                ->set('prosedurOperasi', $prosedur)
                ->set('gambarPenandaanBase64', $payload)
                ->call('simpan')
                ->assertHasNoErrors();
        }

        $this->assertSame(2, DB::table('penandaan_area_operasi')->count());
        $this->assertDatabaseHas('penandaan_area_operasi', [
            'no_rawat' => '2026/08/04/000001', 'gambar_penandaan_base64' => json_encode($firstPayload),
        ]);
        $this->assertDatabaseHas('penandaan_area_operasi', [
            'no_rawat' => '2026/08/04/000002', 'gambar_penandaan_base64' => json_encode($secondPayload),
        ]);

        $firstSaved = DB::table('penandaan_area_operasi')->where('no_rawat', '2026/08/04/000001')->first();

        Livewire::test(PenandaanAreaOperasi::class, [
            'noRawat' => '2026/08/04/000001', 'noRm' => '000001',
        ])
            ->call('edit', $firstSaved->id)
            ->assertEmitted('loadPenandaanCanvases', $firstPayload);
    }

    public function test_print_endpoint_displays_the_saved_marking(): void
    {
        $image = self::VALID_PNG_BASE64;

        DB::table('penandaan_area_operasi')->insert([
            'no_rawat' => '2026/08/04/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'Patient One',
            'tgl_lahir' => '1990-01-02',
            'prosedur_operasi' => 'Appendectomy',
            'operator_id' => 'D001',
            'nama_operator' => 'Dr. Operator',
            'tanggal_penandaan' => '2026-08-04 10:30:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => $image]),
            'format_gambar' => 'image/png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->get(route('penandaan-area-operasi.cetak', ['no_rawat' => '2026/08/04/000001']))
            ->assertOk()
            ->assertSee('FORMULIR PENANDAAN AREA OPERASI')
            ->assertSee('Patient One')
            ->assertSee('Appendectomy')
            ->assertSee('Dr. Operator')
            ->assertSee('data:image/png;base64,', false);
    }

    public function test_controller_upload_pdf_endpoint_success(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/*' => Http::response([
                'success' => true,
                'message' => 'Upload berhasil',
            ], 200),
        ]);

        $image = self::VALID_PNG_BASE64;

        DB::table('penandaan_area_operasi')->insert([
            'no_rawat' => '2026/08/04/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'Patient One',
            'tgl_lahir' => '1990-01-02',
            'prosedur_operasi' => 'Appendectomy',
            'operator_id' => 'D001',
            'nama_operator' => 'Dr. Operator',
            'tanggal_penandaan' => '2026-08-04 10:30:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => $image]),
            'format_gambar' => 'image/png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->post(route('penandaan-area-operasi.upload-pdf'), [
                'no_rawat' => '2026/08/04/000001',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Berkas penandaan area operasi berhasil disimpan dan diunggah.',
            ])
            ->assertJsonStructure(['success', 'message', 'pdf_url']);
    }

    public function test_controller_upload_pdf_endpoint_failure(): void
    {
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://api.qrserver.com/*' => Http::response($pngBinary, 200),
            'https://simrs.rsbhayangkaranganjuk.com/*' => Http::response([
                'success' => false,
                'message' => 'Server error on upload',
            ], 200),
        ]);

        $image = self::VALID_PNG_BASE64;

        DB::table('penandaan_area_operasi')->insert([
            'no_rawat' => '2026/08/04/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'Patient One',
            'tgl_lahir' => '1990-01-02',
            'prosedur_operasi' => 'Appendectomy',
            'operator_id' => 'D001',
            'nama_operator' => 'Dr. Operator',
            'tanggal_penandaan' => '2026-08-04 10:30:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => $image]),
            'format_gambar' => 'image/png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession(['username' => 'D001', 'password' => 'secret'])
            ->post(route('penandaan-area-operasi.upload-pdf'), [
                'no_rawat' => '2026/08/04/000001',
            ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Server error on upload',
                'pdf_url' => null,
            ]);
    }

    public function test_controller_upload_pdf_endpoint_missing_param(): void
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

    public function test_examination_views_mount_the_marking_component_with_the_current_visit(): void
    {
        $ralanContent = file_get_contents(resource_path('views/ralan/pemeriksaan-ralan.blade.php'));
        $ranapContent = file_get_contents(resource_path('views/ranap/pemeriksaan-ranap.blade.php'));

        $this->assertStringContainsString('<livewire:component.penandaan-area-operasi', $ralanContent);
        $this->assertStringContainsString('<livewire:component.penandaan-area-operasi', $ranapContent);
    }
}
