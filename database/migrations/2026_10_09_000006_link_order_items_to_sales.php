<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table): void {
            $table->unsignedInteger('order_item_id')->nullable()->unique();
        });

        $acceptedItems = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('barang', function ($join): void {
                $join->on('barang.id', '=', 'order_items.product_id')
                    ->on('barang.id_member', '=', 'orders.merchant_id');
            })
            ->where('orders.status', 'accepted')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('penjualan')
                    ->whereColumn('penjualan.order_item_id', 'order_items.id');
            })
            ->select(
                'order_items.id',
                'order_items.product_id',
                'barang.id_barang',
                'orders.merchant_id',
                'order_items.quantity',
                'order_items.line_total',
                'orders.updated_at'
            )
            ->get();

        foreach ($acceptedItems as $item) {
            DB::table('penjualan')->insert([
                'order_item_id' => $item->id,
                'id_barang' => $item->id_barang ?? (string) $item->product_id,
                'id_member' => $item->merchant_id,
                'jumlah' => $item->quantity,
                'total' => $item->line_total,
                'tanggal_input' => Carbon::parse($item->updated_at)
                    ->locale('id')
                    ->translatedFormat('j F Y, H:i'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table): void {
            $table->dropUnique(['order_item_id']);
            $table->dropColumn('order_item_id');
        });
    }
};
