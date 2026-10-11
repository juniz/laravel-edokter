<?php

namespace Tests\Unit;

use Tests\TestCase;
use Livewire\Livewire;
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
        $component->dataPerawat = [
            'keluhan_utama' => 'Nyeri perut kanan bawah',
            'rpd' => 'Gastritis',
            'rpo' => 'Antasida',
        ];

        $component->salinDariPerawat();

        $this->assertEquals('Nyeri perut kanan bawah', $component->keluhan_utama);
        $this->assertEquals('Gastritis', $component->rpd);
        $this->assertEquals('Antasida', $component->rpo);
    }

    public function test_salin_dari_perawat_handles_null_safely()
    {
        $component = new Form();
        $component->dataPerawat = null;
        $component->keluhan_utama = 'Keluhan awal';

        $component->salinDariPerawat();

        $this->assertEquals('Keluhan awal', $component->keluhan_utama);
    }

    public function test_livewire_component_lifecycle_does_not_throw_serialization_exception()
    {
        Livewire::test(Form::class)
            ->set('no_rawat', '2026/10/11/000027')
            ->call('salinDariPerawat')
            ->assertStatus(200);
    }
}
