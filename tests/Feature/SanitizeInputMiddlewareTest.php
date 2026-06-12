<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SanitizeInputMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register a temporary endpoint for testing middleware sanitization.
        // Since the middleware is registered in the global HTTP stack,
        // it will automatically run on this request.
        Route::any('/_test/sanitize-middleware', function (Request $request) {
            return response()->json($request->all());
        });
    }

    /** @test */
    public function it_sanitizes_special_characters_in_request_inputs()
    {
        $payload = [
            'normal_text' => 'Hello World',
            'single_quote' => "Doctor's Note",
            'double_quote' => 'He said "Hello"',
            'backslash' => 'Folder\\Subfolder',
            'html_tags' => '<b>Bold Text</b> <script>alert("hack")</script>',
            'nested' => [
                'some_key' => "Patient's condition is <i>good</i>",
            ]
        ];

        $expected = [
            'normal_text' => 'Hello World',
            'single_quote' => 'Doctor`s Note', // single quote replaced with backtick
            'double_quote' => 'He said Hello',  // double quote removed
            'backslash' => 'FolderSubfolder',   // backslash removed
            'html_tags' => 'Bold Text alert(hack)', // HTML tags stripped, double quotes inside removed
            'nested' => [
                'some_key' => 'Patient`s condition is good', // recursively stripped
            ]
        ];

        $response = $this->postJson('/_test/sanitize-middleware', $payload);

        $response->assertStatus(200)
                 ->assertExactJson($expected);
    }

    /** @test */
    public function it_excludes_sensitive_fields_from_sanitization()
    {
        $payload = [
            'password' => "p'a\"s\\s<b>word</b>",
            'password_confirmation' => "p'a\"s\\s<b>word</b>",
            'token' => "t'o\"k\\e<b>n</b>",
            'api_token' => "a'p\"i\\t<b>o</b>ken",
            '_token' => "u'n\"i\\q<b>u</b>e",
            'credential' => "c'r\"e\\d<b>e</b>ntial",
            'nested' => [
                'password' => "n'e\"s\\t<b>ed</b>",
            ]
        ];

        // Exempted fields should remain completely untouched
        $expected = [
            'password' => "p'a\"s\\s<b>word</b>",
            'password_confirmation' => "p'a\"s\\s<b>word</b>",
            'token' => "t'o\"k\\e<b>n</b>",
            'api_token' => "a'p\"i\\t<b>o</b>ken",
            '_token' => "u'n\"i\\q<b>u</b>e",
            'credential' => "c'r\"e\\d<b>e</b>ntial",
            'nested' => [
                'password' => "n'e\"s\\t<b>ed</b>",
            ]
        ];

        $response = $this->postJson('/_test/sanitize-middleware', $payload);

        $response->assertStatus(200)
                 ->assertExactJson($expected);
    }
}
