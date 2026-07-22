<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockOutRequest;
use App\Models\StockOut;
use Barryvdh\DomPDF\Facade\Pdf;
class PdfController extends Controller
{
    public function stock_out_invoice(Request $request) {
        $validated = $request->validate([
            'ids' => ['required','array'],
            'ids.*' => ['integer','exists:stock_out_requests,id']
        ]);

        $requests = StockOutRequest::with([
            'warehouse',
            'requestedBy',
            'stockOut.items.item',
            'stockOut.items.unit',
            'stockOut.items.stockOutItemLayers',
        ])->whereIn('id',$validated['ids'])
        ->get();
        $pdf = Pdf::loadView('pdf.stock-out-invoice',[
            'requests' => $requests,
        ])->setPaper('a4','portrait');

        return $pdf->stream('stock-out-invoice.pdf');
    }
}
