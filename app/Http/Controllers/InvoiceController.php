<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function customer(Request $request, int $order): View
    {
        $invoice = DB::table('orders')
            ->join('member as customer', 'customer.id_member', '=', 'orders.customer_id')
            ->join('member as merchant', 'merchant.id_member', '=', 'orders.merchant_id')
            ->where('orders.id', $order)
            ->where('orders.customer_id', $request->session()->get('member_id'))
            ->select('orders.*', 'customer.nm_member as customer_name', 'customer.email as customer_email',
                'merchant.company_name as merchant_name', 'merchant.email as merchant_email',
                'merchant.telepon as merchant_phone', 'merchant.alamat_member as merchant_address')
            ->first();

        abort_unless($invoice, 404);

        return $this->viewInvoice($invoice);
    }

    public function merchant(Request $request, int $order): View
    {
        $invoice = DB::table('orders')
            ->join('member as customer', 'customer.id_member', '=', 'orders.customer_id')
            ->join('member as merchant', 'merchant.id_member', '=', 'orders.merchant_id')
            ->where('orders.id', $order)
            ->where('orders.merchant_id', $request->session()->get('member_id'))
            ->select('orders.*', 'customer.nm_member as customer_name', 'customer.email as customer_email',
                'merchant.company_name as merchant_name', 'merchant.email as merchant_email',
                'merchant.telepon as merchant_phone', 'merchant.alamat_member as merchant_address')
            ->first();

        abort_unless($invoice, 404);

        return $this->viewInvoice($invoice);
    }

    private function viewInvoice(object $invoice): View
    {
        return view('orders.invoice', [
            'invoice' => $invoice,
            'items' => DB::table('order_items')->where('order_id', $invoice->id)->get(),
            'generatedAt' => now(),
        ]);
    }
}
