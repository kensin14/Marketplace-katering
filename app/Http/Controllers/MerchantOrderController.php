<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MerchantOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = DB::table('orders')
            ->join('member as customer', 'customer.id_member', '=', 'orders.customer_id')
            ->where('orders.merchant_id', $request->session()->get('member_id'))
            ->where('orders.hidden_from_history', false)
            ->select('orders.*', 'customer.nm_member as customer_name')
            ->orderByDesc('orders.created_at')
            ->simplePaginate(25)
            ->withQueryString();

        return view('merchant.orders', ['orders' => $orders]);
    }

    public function updateStatus(Request $request, int $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:accepted,rejected'],
        ]);

        DB::transaction(function () use ($request, $order, $data): void {
            $record = DB::table('orders')
                ->where('id', $order)
                ->where('merchant_id', $request->session()->get('member_id'))
                ->lockForUpdate()
                ->first();

            if (!$record) {
                abort(404);
            }
            if ($record->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Status pesanan ini sudah diperbarui.']);
            }

            $items = DB::table('order_items')->where('order_id', $order)->get();

            if ($data['status'] === 'accepted') {
                if ($items->isEmpty()) {
                    throw new \RuntimeException('Pesanan tidak memiliki item untuk dicatat sebagai penjualan.');
                }

                $products = DB::table('barang')
                    ->whereIn('id', $items->pluck('product_id'))
                    ->where('id_member', $record->merchant_id)
                    ->get(['id', 'id_barang'])
                    ->keyBy('id');
                $saleDate = now()->locale('id')->translatedFormat('j F Y, H:i');
                $sales = [];

                foreach ($items as $item) {
                    $product = $products->get($item->product_id);
                    if (!$product) {
                        throw new \RuntimeException('Produk pesanan tidak ditemukan untuk dicatat sebagai penjualan.');
                    }

                    $sales[] = [
                        'order_item_id' => $item->id,
                        'id_barang' => $product->id_barang,
                        'id_member' => $record->merchant_id,
                        'jumlah' => $item->quantity,
                        'total' => $item->line_total,
                        'tanggal_input' => $saleDate,
                    ];
                }

                DB::table('penjualan')->insert($sales);
            } elseif ($data['status'] === 'rejected') {
                foreach ($items as $item) {
                    DB::table('barang')->where('id', $item->product_id)
                        ->increment('stok', $item->quantity);
                }
            }

            DB::table('orders')->where('id', $order)->update([
                'status' => $data['status'],
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('merchant.orders.index')->with(
            'status',
            $data['status'] === 'accepted' ? 'Pesanan diterima.' : 'Pesanan ditolak dan stok menu dikembalikan.'
        );
    }
}
