<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DeliveryDayController extends Controller
{
    public function index(): View
    {
        return view('delivery-day.index');
    }
}
