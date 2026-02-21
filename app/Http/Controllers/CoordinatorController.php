<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CoordinatorController extends Controller
{
    public function index(): View
    {
        return view('coordinator.index');
    }
}
