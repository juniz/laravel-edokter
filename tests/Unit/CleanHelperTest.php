<?php

namespace Tests\Unit;

use Tests\TestCase;

class CleanHelperTest extends TestCase
{
    /** @test */
    public function it_ensures_clean_function_is_always_defined()
    {
        $this->assertTrue(function_exists('clean'), 'clean() helper must be defined');
    }

    /** @test */
    public function it_handles_null_and_empty_clean_input()
    {
        $this->assertEquals('', clean(null));
        $this->assertEquals('', clean(''));
    }

    /** @test */
    public function it_cleans_dirty_html()
    {
        $dirty = '<p>Hello <script>alert("xss")</script>world</p>';
        $cleaned = clean($dirty);

        $this->assertStringNotContainsString('<script>', $cleaned);
        $this->assertStringContainsString('Hello', $cleaned);
    }
}
