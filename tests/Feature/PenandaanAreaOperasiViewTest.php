<?php

namespace Tests\Feature;

use App\Http\Livewire\Component\PenandaanAreaOperasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class PenandaanAreaOperasiViewTest extends TestCase
{
    private const PNG_BASE64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

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
            throw new \LogicException('PenandaanAreaOperasiViewTest fixtures may only run in the testing environment.');
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

    public function test_view_renders_datetime_input_field(): void
    {
        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->assertSeeHtml('id="tanggal-penandaan"')
            ->assertSeeHtml('type="datetime-local"')
            ->assertSeeHtml('wire:model.defer="tanggalPenandaan"')
            ->assertSee('Tanggal & Jam Penandaan', false);
    }

    public function test_view_renders_empty_history_table_when_no_records(): void
    {
        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->assertSee('Riwayat Penandaan Area Operasi')
            ->assertSee('Tanggal Penandaan')
            ->assertSee('Prosedur Operasi')
            ->assertSee('Dokter Operator')
            ->assertSee('Aksi')
            ->assertSee('Belum ada riwayat penandaan area operasi.');
    }

    public function test_view_renders_history_records_with_action_buttons(): void
    {
        $id = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => '2026/08/21/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'PASIEN TEST',
            'prosedur_operasi' => 'Apendektomi Laparoskopi',
            'operator_id' => 'D001',
            'nama_operator' => 'dr. Bedah Satu',
            'tanggal_penandaan' => '2026-08-21 09:30:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => self::PNG_BASE64]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cetakUrl = e(route('penandaan-area-operasi.cetak', ['no_rawat' => '2026/08/21/000001', 'id' => $id]));
        $pdfUrl = e(route('penandaan-area-operasi.download-pdf', ['no_rawat' => '2026/08/21/000001', 'id' => $id]));

        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->assertSee('21-08-2026 09:30')
            ->assertSee('Apendektomi Laparoskopi')
            ->assertSee('dr. Bedah Satu')
            ->assertSeeHtml('wire:click="edit(' . $id . ')"')
            ->assertSeeHtml('wire:click="confirmHapus(' . $id . ')"')
            ->assertSeeHtml('href="' . $cetakUrl . '"')
            ->assertSeeHtml('href="' . $pdfUrl . '"');
    }

    public function test_view_renders_create_mode_buttons_when_not_in_edit_mode(): void
    {
        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->assertSeeHtml('id="simpan-penandaan"')
            ->assertDontSeeHtml('id="ubah-penandaan"')
            ->assertDontSeeHtml('wire:click="batalEdit"');
    }

    public function test_view_renders_edit_mode_buttons_when_in_edit_mode(): void
    {
        $id = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => '2026/08/21/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'PASIEN TEST',
            'prosedur_operasi' => 'Prosedur Uji',
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
            ->assertSeeHtml('id="ubah-penandaan"')
            ->assertSee('Ubah Penandaan')
            ->assertSeeHtml('wire:click="batalEdit"')
            ->assertSee('Batal')
            ->assertDontSeeHtml('id="simpan-penandaan"');
    }

    public function test_view_renders_correct_instructions_for_red_circle_markers(): void
    {
        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->assertSee('Ketuk bagian tubuh untuk menambahkan penanda lingkaran merah.')
            ->assertDontSee('Ketuk bagian tubuh untuk menambahkan penanda merah bernomor.');
    }
}

