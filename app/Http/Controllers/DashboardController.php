<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Halaman dashboard utama (placeholder Step 1).
     * Akan dilengkapi KPI & grafik di Step 5.
     */
    public function index(): View
    {
        return view('dashboard.index');
    }
}
