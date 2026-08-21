<?php

namespace Tests\Feature;

use App\Http\Livewire\Component\PenandaanAreaOperasi;
use App\Services\PenandaanAreaOperasiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class PenandaanAreaOperasiMultiRecordTest extends TestCase
{
    private const PNG_BASE64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    private array $createdFixtureTables = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->requireTestingEnvironment();

        // Setup doctor, patient, reg_periksa fixture tables
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

        DB::table('dokter')->insert([
            ['kd_dokter' => 'D001', 'nm_dokter' => 'dr. Bedah Satu', 'status' => '1'],
            ['kd_dokter' => 'D002', 'nm_dokter' => 'dr. Bedah Dua', 'status' => '1'],
        ]);
        DB::table('pasien')->insert(['no_rkm_medis' => '000001', 'nm_pasien' => 'PASIEN TEST', 'jk' => 'L', 'tgl_lahir' => '1995-05-15']);
        DB::table('reg_periksa')->insert(['no_rawat' => '2026/08/21/000001', 'no_rkm_medis' => '000001']);

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
            throw new \LogicException('PenandaanAreaOperasiMultiRecordTest fixtures may only run in the testing environment.');
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

    public function test_can_save_multiple_markings_for_same_no_rawat(): void
    {
        $mockService = \Mockery::mock(PenandaanAreaOperasiService::class);
        $mockService->shouldReceive('generateAndUploadPdf')->andReturn(['success' => true, 'is_replaced' => false]);
        $this->app->instance(PenandaanAreaOperasiService::class, $mockService);

        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->set('prosedurOperasi', 'Apendektomi Laparoskopi')
            ->set('gambarPenandaanBase64', ['body_front' => self::PNG_BASE64])
            ->call('simpan')
            ->assertHasNoErrors();

        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->set('prosedurOperasi', 'Herniotomi Inguinal')
            ->set('gambarPenandaanBase64', ['body_back' => self::PNG_BASE64])
            ->call('simpan')
            ->assertHasNoErrors();

        $count = DB::table('penandaan_area_operasi')->where('no_rawat', '2026/08/21/000001')->count();
        $this->assertEquals(2, $count);
    }

    public function test_can_edit_and_update_specific_marking(): void
    {
        $id = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => '2026/08/21/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'PASIEN TEST',
            'prosedur_operasi' => 'Prosedur Awal',
            'operator_id' => 'D001',
            'nama_operator' => 'dr. Bedah Satu',
            'tanggal_penandaan' => '2026-08-21 09:00:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => self::PNG_BASE64]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mockService = \Mockery::mock(PenandaanAreaOperasiService::class);
        $mockService->shouldReceive('generateAndUploadPdf')->with('2026/08/21/000001', $id)->andReturn(['success' => true]);
        $this->app->instance(PenandaanAreaOperasiService::class, $mockService);

        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->call('edit', $id)
            ->assertSet('modeEdit', true)
            ->assertSet('selectedId', $id)
            ->assertSet('prosedurOperasi', 'Prosedur Awal')
            ->set('prosedurOperasi', 'Prosedur Diperbarui')
            ->set('gambarPenandaanBase64', ['body_front' => self::PNG_BASE64])
            ->call('update')
            ->assertHasNoErrors()
            ->assertSet('modeEdit', false)
            ->assertSet('selectedId', null);

        $this->assertDatabaseHas('penandaan_area_operasi', [
            'id' => $id,
            'prosedur_operasi' => 'Prosedur Diperbarui',
        ]);
    }

    public function test_can_delete_marking(): void
    {
        $id = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => '2026/08/21/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'PASIEN TEST',
            'prosedur_operasi' => 'Prosedur Dihapus',
            'operator_id' => 'D001',
            'nama_operator' => 'dr. Bedah Satu',
            'tanggal_penandaan' => '2026-08-21 09:00:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => self::PNG_BASE64]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->call('confirmHapus', $id)
            ->call('hapus');

        $this->assertDatabaseMissing('penandaan_area_operasi', ['id' => $id]);
    }

    public function test_cannot_edit_or_delete_marking_by_another_operator(): void
    {
        $id = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => '2026/08/21/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'PASIEN TEST',
            'prosedur_operasi' => 'Prosedur Dokter Lain',
            'operator_id' => 'D002',
            'nama_operator' => 'dr. Bedah Dua',
            'tanggal_penandaan' => '2026-08-21 09:00:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => self::PNG_BASE64]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->call('edit', $id)
            ->assertSet('modeEdit', false)
            ->assertSet('selectedId', null)
            ->call('confirmHapus', $id)
            ->assertSet('selectedIdHapus', null);

        $this->assertDatabaseHas('penandaan_area_operasi', ['id' => $id]);
    }

    public function test_batal_edit_resets_form_and_emits_event(): void
    {
        $id = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => '2026/08/21/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'PASIEN TEST',
            'prosedur_operasi' => 'Prosedur Awal',
            'operator_id' => 'D001',
            'nama_operator' => 'dr. Bedah Satu',
            'tanggal_penandaan' => '2026-08-21 09:00:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => self::PNG_BASE64]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->call('edit', $id)
            ->assertSet('modeEdit', true)
            ->assertSet('selectedId', $id)
            ->call('batalEdit')
            ->assertSet('modeEdit', false)
            ->assertSet('selectedId', null)
            ->assertSet('prosedurOperasi', '')
            ->assertEmitted('resetAllCanvases');
    }

    public function test_getData_loads_markings_ordered_desc(): void
    {
        $id1 = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => '2026/08/21/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'PASIEN TEST',
            'prosedur_operasi' => 'Prosedur Pertama',
            'operator_id' => 'D001',
            'nama_operator' => 'dr. Bedah Satu',
            'tanggal_penandaan' => '2026-08-21 08:00:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => self::PNG_BASE64]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id2 = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => '2026/08/21/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'PASIEN TEST',
            'prosedur_operasi' => 'Prosedur Kedua',
            'operator_id' => 'D001',
            'nama_operator' => 'dr. Bedah Satu',
            'tanggal_penandaan' => '2026-08-21 10:00:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => self::PNG_BASE64]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $component = Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001']);

        $data = $component->get('data');
        $this->assertCount(2, $data);
        $this->assertEquals($id2, $data[0]->id);
        $this->assertEquals($id1, $data[1]->id);
    }
}
