<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_barang' => 0,
            'barang_masuk_hari_ini' => 0,
            'total_supplier' => 0,
            'laporan_bulan_ini' => 0,
        ];

        return view('admin.dashboard', compact('stats'));
    }
}