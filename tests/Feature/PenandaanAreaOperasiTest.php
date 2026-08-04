<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->assertTrue(collect(Schema::getIndexes('penandaan_area_operasi'))
            ->contains(fn (array $index) => $index['unique'] && $index['columns'] === ['no_rawat']));
    }
}
