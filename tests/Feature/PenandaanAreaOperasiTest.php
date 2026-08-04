<?php

namespace Tests\Feature;

use App\Http\Livewire\Component\PenandaanAreaOperasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class PenandaanAreaOperasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('pasien')) {
            Schema::create('pasien', function ($table) {
                $table->string('no_rkm_medis')->primary();
                $table->string('nm_pasien');
                $table->date('tgl_lahir')->nullable();
            });
        }

        if (! Schema::hasTable('reg_periksa')) {
            Schema::create('reg_periksa', function ($table) {
                $table->string('no_rawat')->primary();
                $table->string('no_rkm_medis');
            });
        }

        if (! Schema::hasTable('dokter')) {
            Schema::create('dokter', function ($table) {
                $table->string('kd_dokter')->primary();
                $table->string('nm_dokter');
            });
        }

        DB::table('pasien')->upsert([
            ['no_rkm_medis' => '000001', 'nm_pasien' => 'Patient One', 'tgl_lahir' => '1990-01-02'],
            ['no_rkm_medis' => '000002', 'nm_pasien' => 'Patient Two', 'tgl_lahir' => '1991-02-03'],
        ], ['no_rkm_medis'], ['nm_pasien', 'tgl_lahir']);
        DB::table('reg_periksa')->upsert([
            ['no_rawat' => '2026/08/04/000001', 'no_rkm_medis' => '000001'],
            ['no_rawat' => '2026/08/04/000002', 'no_rkm_medis' => '000002'],
        ], ['no_rawat'], ['no_rkm_medis']);
        DB::table('dokter')->upsert([
            'kd_dokter' => 'D001', 'nm_dokter' => 'Dr. Operator',
        ], ['kd_dokter'], ['nm_dokter']);

        session(['username' => 'D001']);
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
}
