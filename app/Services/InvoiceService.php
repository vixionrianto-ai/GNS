<?php

namespace App\Services;

use App\Models\Pembayaran;

class InvoiceService
{
    /**
     * Generate the next payment invoice number for the current month.
     * The unique database constraint is the final guard against duplicates.
     */
    public function generate(): string
    {
        $prefix = 'INV-' . now()->format('Ym') . '-';

        $lastInvoice = Pembayaran::query()
            ->where('invoice_no', 'like', $prefix . '%')
            ->orderByDesc('invoice_no')
            ->first(['invoice_no']);

        $number = $lastInvoice
            ? ((int) substr((string) $lastInvoice->invoice_no, -6) + 1)
            : 1;

        return $prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
