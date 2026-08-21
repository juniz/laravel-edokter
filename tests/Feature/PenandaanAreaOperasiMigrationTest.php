<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PenandaanAreaOperasiMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireTestingEnvironment();

        // Run create table migration
        $createMigration = require database_path('migrations/2026_08_04_000000_create_penandaan_area_operasi_table.php');
        $createMigration->up();

        // Run remove unique migration if exists
        $migrationFile = database_path('migrations/2026_08_21_000000_remove_unique_from_penandaan_area_operasi.php');
        if (file_exists($migrationFile)) {
            $removeUniqueMigration = require $migrationFile;
            $removeUniqueMigration->up();
        }
    }

    protected function tearDown(): void
    {
        try {
            $this->requireTestingEnvironment();
            Schema::dropIfExists('penandaan_area_operasi');
        } finally {
            parent::tearDown();
        }
    }

    private function requireTestingEnvironment(): void
    {
        if (! app()->environment('testing')) {
            throw new \LogicException('PenandaanAreaOperasiMigrationTest fixtures may only run in the testing environment.');
        }
    }

    public function test_it_allows_multiple_records_for_the_same_no_rawat(): void
    {
        $noRawat = '2026/08/21/000001';

        $id1 = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => $noRawat,
            'no_rm' => '123456',
            'nama_pasien' => 'PASIEN MULTI TEST',
            'tgl_lahir' => '1990-01-01',
            'prosedur_operasi' => 'Prosedur Operasi 1',
            'operator_id' => 'D001',
            'nama_operator' => 'dr. Bedah Satu',
            'tanggal_penandaan' => '2026-08-21 08:00:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==']),
            'format_gambar' => 'image/png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id2 = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => $noRawat,
            'no_rm' => '123456',
            'nama_pasien' => 'PASIEN MULTI TEST',
            'tgl_lahir' => '1990-01-01',
            'prosedur_operasi' => 'Prosedur Operasi 2',
            'operator_id' => 'D001',
            'nama_operator' => 'dr. Bedah Satu',
            'tanggal_penandaan' => '2026-08-21 14:00:00',
            'gambar_penandaan_base64' => json_encode(['body_back' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==']),
            'format_gambar' => 'image/png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertNotEquals($id1, $id2);
        $records = DB::table('penandaan_area_operasi')->where('no_rawat', $noRawat)->get();
        $this->assertCount(2, $records);
    }

    public function test_down_migration_restores_unique_constraint(): void
    {
        $removeUniqueMigration = require database_path('migrations/2026_08_21_000000_remove_unique_from_penandaan_area_operasi.php');
        $removeUniqueMigration->down();

        $noRawat = '2026/08/21/000002';
        DB::table('penandaan_area_operasi')->insert([
            'no_rawat' => $noRawat,
            'no_rm' => '123456',
            'nama_pasien' => 'PASIEN ROLLBACK TEST',
            'tgl_lahir' => '1990-01-01',
            'prosedur_operasi' => 'Prosedur Operasi 1',
            'operator_id' => 'D001',
            'nama_operator' => 'dr. Bedah Satu',
            'tanggal_penandaan' => '2026-08-21 08:00:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==']),
            'format_gambar' => 'image/png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::table('penandaan_area_operasi')->insert([
            'no_rawat' => $noRawat,
            'no_rm' => '123456',
            'nama_pasien' => 'PASIEN ROLLBACK TEST',
            'tgl_lahir' => '1990-01-01',
            'prosedur_operasi' => 'Prosedur Operasi 2',
            'operator_id' => 'D001',
            'nama_operator' => 'dr. Bedah Satu',
            'tanggal_penandaan' => '2026-08-21 14:00:00',
            'gambar_penandaan_base64' => json_encode(['body_back' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==']),
            'format_gambar' => 'image/png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
