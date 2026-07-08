<?php

namespace App\Services;

use App\Models\Pembayaran;

class InvoiceService
{
    /**
     * Generate Nomor Invoice
     */
    public function generate(): string
    {
        $prefix = 'INV-' . date('Ym') . '-';

        $lastInvoice = Pembayaran::where('invoice_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if (!$lastInvoice) {
            $number = 1;
        } else {
            $lastNumber = (int) substr($lastInvoice->invoice_no, -6);
            $number = $lastNumber + 1;
        }

        return $prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}