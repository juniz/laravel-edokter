<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\View\Components\Ralan\PenilaianAwalMedisIGD;

class PenilaianAwalMedisIGDTest extends TestCase
{
    public function test_component_instantiation_and_methods_exist()
    {
        $component = new PenilaianAwalMedisIGD('2026/01/01/000001');
        $this->assertTrue(method_exists($component, 'getPemeriksaanRalan'));
        $this->assertTrue(method_exists($component, 'getPenilaianMedisIGD'));
        
        $this->assertCount(0, $component->getPemeriksaanRalan(null));
        $this->assertNull($component->getPenilaianMedisIGD(null));
    }
}

