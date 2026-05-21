<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StockMutationReportExport;
use App\Exports\StockReportExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function exportStocks()
    {
        return Excel::download(
            new StockReportExport(),
            'laporan-stok-barang.xlsx'
        );
    }

    public function exportStockMutations()
    {
        return Excel::download(
            new StockMutationReportExport(),
            'laporan-mutasi-stok.xlsx'
        );
    }
}