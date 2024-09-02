<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Incoming\Answer;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Str;


class BotManController extends Controller
{
    public function bot()
    {
        $botman = app('botman');
        $botman->hears('{message}', function ($botman, $message) {

            $result = Gemini::geminiPro()->generateContent($message);

            $hasil = $result->text();
            // dd($hasil);
            $botman->reply(Str::markdown($hasil));
        });
        $botman->listen();
    }
}
