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

    /** @test */
    public function it_sanitizes_livewire_payloads_selectively_without_corrupting_metadata()
    {
        $payload = [
            'fingerprint' => [
                'name' => "ralan.resume",
                'id' => "some'id\"with\\special<b>chars</b>"
            ],
            'serverMemo' => [
                'data' => [
                    'keluhan' => "original'data\"with\\special<b>tags</b>"
                ],
                'checksum' => "checksum'value\"here"
            ],
            'updates' => [
                [
                    'type' => 'syncInput',
                    'payload' => [
                        'name' => 'keluhan',
                        'value' => "patient's keluhan <script>alert('xss')</script>"
                    ]
                ],
                [
                    'type' => 'syncInput',
                    'payload' => [
                        'name' => 'password',
                        'value' => "p'a\"s\\s<b>word</b>"
                    ]
                ],
                [
                    'type' => 'callMethod',
                    'payload' => [
                        'method' => 'savePassword',
                        'params' => [
                            "p'a\"s\\s<b>word</b>"
                        ]
                    ]
                ]
            ]
        ];

        $expected = [
            'fingerprint' => [
                'name' => "ralan.resume",
                'id' => "some'id\"with\\special<b>chars</b>" // Untouched for checksum
            ],
            'serverMemo' => [
                'data' => [
                    'keluhan' => "original'data\"with\\special<b>tags</b>" // Untouched for checksum
                ],
                'checksum' => "checksum'value\"here" // Untouched for checksum
            ],
            'updates' => [
                [
                    'type' => 'syncInput',
                    'payload' => [
                        'name' => 'keluhan',
                        'value' => "patient`s keluhan alert(`xss`)" // Sanitized
                    ]
                ],
                [
                    'type' => 'syncInput',
                    'payload' => [
                        'name' => 'password',
                        'value' => "p'a\"s\\s<b>word</b>" // Exempted syncInput
                    ]
                ],
                [
                    'type' => 'callMethod',
                    'payload' => [
                        'method' => 'savePassword',
                        'params' => [
                            "p'a\"s\\s<b>word</b>" // Exempted method call params
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->withHeaders(['X-Livewire' => 'true'])
                         ->postJson('/_test/sanitize-middleware', $payload);

        $response->assertStatus(200)
                 ->assertExactJson($expected);
    }
}
