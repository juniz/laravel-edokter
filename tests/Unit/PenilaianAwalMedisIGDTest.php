<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\View\Components\Ralan\PenilaianAwalMedisIGD;

class PenilaianAwalMedisIGDTest extends TestCase
{
    public function test_component_instantiation_and_get_pemeriksaan_ralan_method_exists()
    {
        $component = new PenilaianAwalMedisIGD('2026/01/01/000001');
        $this->assertTrue(method_exists($component, 'getPemeriksaanRalan'));
        $data = $component->getPemeriksaanRalan(null);
        $this->assertCount(0, $data);
    }
}
