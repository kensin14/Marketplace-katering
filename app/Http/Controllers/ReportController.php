<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function excel(Request $request): StreamedResponse
    {
        $memberId = (int) $request->session()->get('member_id');

        return response()->streamDownload(function () use ($memberId): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Tidak dapat membuka aliran ekspor laporan.');
            }

            fputcsv($output, ['Kode barang', 'Nama barang', 'Jumlah', 'Total', 'Kasir', 'Tanggal']);
            foreach ($this->salesQuery($memberId)->orderByDesc('penjualan.id_penjualan')->cursor() as $sale) {
                fputcsv($output, [
                    $sale->id_barang,
                    $sale->nama_barang,
                    $sale->jumlah,
                    $sale->total,
                    $sale->nm_member,
                    $this->formatDate($sale->tanggal_input),
                ]);
            }
            fclose($output);
        }, 'data-laporan-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function print(Request $request): View
    {
        $rows = $this->salesQuery((int) $request->session()->get('member_id'))
            ->orderByDesc('penjualan.id_penjualan')->get();
        foreach ($rows as $row) {
            $row->tanggal_input = $this->formatDate($row->tanggal_input);
        }

        return view('reports.print', [
            'rows' => $rows,
            'generatedAt' => now(),
        ]);
    }

    private function formatDate(string $date): string
    {
        try {
            $parsedDate = Carbon::createFromLocaleFormat('j F Y, H:i', 'id', $date);
        } catch (\Carbon\Exceptions\InvalidFormatException) {
            try {
                $parsedDate = Carbon::createFromLocaleFormat('j F Y', 'id', $date);
            } catch (\Carbon\Exceptions\InvalidFormatException) {
                $parsedDate = Carbon::parse($date);
            }
        }

        return $parsedDate->locale('id')->translatedFormat('j F Y, H:i');
    }

    private function salesQuery(int $memberId): Builder
    {
        return DB::table('penjualan')
            ->leftJoin('barang', function ($join): void {
                $join->on('barang.id_barang', '=', 'penjualan.id_barang')
                    ->on('barang.id_member', '=', 'penjualan.id_member');
            })
            ->leftJoin('order_items', 'order_items.id', '=', 'penjualan.order_item_id')
            ->join('member as seller', 'seller.id_member', '=', 'penjualan.id_member')
            ->where('penjualan.id_member', $memberId)
            ->select('penjualan.id_barang')
            ->selectRaw('COALESCE(barang.nama_barang, order_items.product_name, penjualan.id_barang) AS nama_barang')
            ->addSelect('penjualan.jumlah',
                'penjualan.total', 'seller.nm_member', 'penjualan.tanggal_input');
    }
}
