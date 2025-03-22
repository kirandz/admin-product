<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function printInvoice(Sale $sale)
    {
        $pdf = PDF::loadView('invoices.sale', [
            'sale' => $sale,
        ])->setPaper([0, 0, 684, 792]); // 9.4" x 11" in points (72 points = 1 inch)

        return $pdf->stream("invoice-{$sale->invoice_number}.pdf");
    }
}
