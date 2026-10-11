<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Livewire\Component\AwalIgd\Form;

class AwalIgdFormTest extends TestCase
{
    public function test_component_has_data_perawat_property_and_salin_method()
    {
        $component = new Form();
        $this->assertTrue(property_exists($component, 'dataPerawat'));
        $this->assertTrue(method_exists($component, 'salinDariPerawat'));
    }

    public function test_salin_dari_perawat_copies_fields_to_model()
    {
        $component = new Form();
        $component->dataPerawat = (object) [
            'keluhan_utama' => 'Nyeri perut kanan bawah',
            'rpd' => 'Gastritis',
            'rpo' => 'Antasida',
        ];

        $component->salinDariPerawat();

        $this->assertEquals('Nyeri perut kanan bawah', $component->keluhan_utama);
        $this->assertEquals('Gastritis', $component->rpd);
        $this->assertEquals('Antasida', $component->rpo);
    }
}
