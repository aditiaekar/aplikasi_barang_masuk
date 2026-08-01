<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockOutRequest;
use App\Models\StockOut;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;
class PdfController extends Controller
{
    public function stock_out_invoice(Request $request) {
        $validated = $request->validate([
            'ids' => ['required','array'],
            'ids.*' => ['integer','exists:stock_out_requests,id']
        ]);

        // check if status is not approved
        $stockOutReq = StockOutRequest::whereIn('id',$validated['ids'])
            ->pluck('status','id');

        foreach ($validated['ids'] as $index => $idx) {
            $statusReq = ($stockOutReq[$idx]);
            if ($statusReq !== 'approved') {
                throw ValidationException::withMessages([
                    "error" => "Status yang bisa di print hanya Approved.",
                ]);
            }
        }

        $requests = StockOut::with([
            'warehouse',
            'items.item',
            'items.unit',
            'items.stockOutItemLayers',
            ])->whereIn('id',$validated['ids'])
            ->orderBy('ems_number','asc')
        ->get();
        $pdf = Pdf::loadView('pdf.stock-out-invoice',[
            'requests' => $requests,
        ])->setPaper('a4','portrait');

        return $pdf->stream('stock-out-invoice.pdf');
    }
}
