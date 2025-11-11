<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SimReloadController extends Controller
{
    public function index()
    {
        return Inertia::render('SimReload/Index');
    }

    public function mobitel()
    {
        return Inertia::render('SimReload/Mobitel');
    }

    public function dialog()
    {
        return Inertia::render('SimReload/Dialog');
    }

    public function airtel()
    {
        return Inertia::render('SimReload/Airtel');
    }

    public function hutch()
    {
        return Inertia::render('SimReload/Hutch');
    }
}
