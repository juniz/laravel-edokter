<?php

namespace Tests\Feature;

use App\Http\Livewire\Component\PenandaanAreaOperasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Manual Canvas acceptance checks:
 * - At 375px width, one tap on body_front creates exactly pin 1.
 * - Switching away from body_front and back preserves pin 1.
 * - Saving and reloading restores pin 1.
 * - Printing displays the saved body_front PNG.
 */
class PenandaanAreaOperasiTest extends TestCase
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
        });
        $this->createFixtureTable('reg_periksa', function ($table) {
            $table->string('no_rawat')->primary();
            $table->string('no_rkm_medis');
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

        DB::table('pasien')->insert([
            ['no_rkm_medis' => '000001', 'nm_pasien' => 'Patient One', 'tgl_lahir' => '1990-01-02'],
            ['no_rkm_medis' => '000002', 'nm_pasien' => 'Patient Two', 'tgl_lahir' => '1991-02-03'],
        ]);
        DB::table('reg_periksa')->insert([
            ['no_rawat' => '2026/08/04/000001', 'no_rkm_medis' => '000001'],
            ['no_rawat' => '2026/08/04/000002', 'no_rkm_medis' => '000002'],
        ]);
        DB::table('dokter')->insert([
            'kd_dokter' => 'D001', 'nm_dokter' => 'Dr. Operator',
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
        $indexes = DB::select(
            'SHOW INDEX FROM `penandaan_area_operasi` WHERE Key_name = ?',
            ['penandaan_area_operasi_no_rawat_unique']
        );

        $this->assertNotEmpty($indexes);
        $this->assertSame(0, (int) $indexes[0]->Non_unique);
    }

    public function test_it_persists_a_valid_marking_payload(): void
    {
        $payload = ['body_front' => 'data:image/png;base64,' . base64_encode('front')];

        $this->withSession(['username' => 'D001']);

        Livewire::test(PenandaanAreaOperasi::class, [
            'noRawat' => '2026/08/04/000001', 'noRm' => '000001',
        ])
            ->set('prosedurOperasi', 'Appendectomy')
            ->set('gambarPenandaanBase64', $payload)
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('penandaan_area_operasi', [
            'no_rawat' => '2026/08/04/000001',
            'gambar_penandaan_base64' => json_encode($payload),
            'nama_pasien' => 'Patient One',
            'nama_operator' => 'Dr. Operator',
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
            ->set('gambarPenandaanBase64', ['body_front' => 'data:image/png;base64,' . base64_encode('front')])
            ->call('simpan')
            ->assertHasErrors(['operator']);
    }

    public function test_it_updates_the_existing_visit_instead_of_creating_another_row(): void
    {
        $firstPayload = ['body_front' => 'data:image/png;base64,' . base64_encode('first')];
        $updatedPayload = ['body_back' => 'data:image/png;base64,' . base64_encode('updated')];

        $first = Livewire::test(PenandaanAreaOperasi::class, [
            'noRawat' => '2026/08/04/000001', 'noRm' => '000001',
        ]);
        $first->set('prosedurOperasi', 'First procedure')->set('gambarPenandaanBase64', $firstPayload)->call('simpan');

        Livewire::test(PenandaanAreaOperasi::class, [
            'noRawat' => '2026/08/04/000001', 'noRm' => '000001',
        ])
            ->set('prosedurOperasi', 'Updated procedure')
            ->set('gambarPenandaanBase64', $updatedPayload)
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertSame(1, DB::table('penandaan_area_operasi')->where('no_rawat', '2026/08/04/000001')->count());
        $this->assertDatabaseHas('penandaan_area_operasi', [
            'no_rawat' => '2026/08/04/000001',
            'prosedur_operasi' => 'Updated procedure',
            'gambar_penandaan_base64' => json_encode($updatedPayload),
        ]);
    }

    public function test_it_keeps_markings_isolated_by_visit_and_reloads_a_saved_payload(): void
    {
        $firstPayload = ['body_front' => 'data:image/png;base64,' . base64_encode('first')];
        $secondPayload = ['body_back' => 'data:image/png;base64,' . base64_encode('second')];

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

        Livewire::test(PenandaanAreaOperasi::class, [
            'noRawat' => '2026/08/04/000001', 'noRm' => '000001',
        ])->assertEmitted('loadPenandaanCanvases', $firstPayload);
    }

    public function test_print_endpoint_displays_the_saved_marking(): void
    {
        $image = 'data:image/png;base64,' . base64_encode('front');

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

    public function test_examination_views_mount_the_marking_component_with_the_current_visit(): void
    {
        $noRawat = '2026/08/04/000001';
        $noRm = '000001';

        $this->app['request']->query->replace([
            'no_rawat' => $noRawat,
            'no_rm' => $noRm,
        ]);

        foreach (['ralan.pemeriksaan-ralan', 'ranap.pemeriksaan-ranap'] as $view) {
            $this->view($view)
                ->assertSeeLivewire('component.penandaan-area-operasi')
                ->assertSee(str_replace('/', '\\/', $noRawat), false)
                ->assertSee($noRm);
        }
    }
}
