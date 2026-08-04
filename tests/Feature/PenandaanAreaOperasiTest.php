<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PenandaanAreaOperasiTest extends TestCase
{
    use RefreshDatabase;

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
}
