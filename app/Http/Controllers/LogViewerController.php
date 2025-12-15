<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class LogViewerController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('log-viewer/Index');
    }
}
