<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const PAGES = [
        'dashboard' => 'Ringkasan',
        'barang' => 'Menu Masakan',
        'kategori' => 'Kategori',
        'penjualan' => 'Penjualan',
    ];

    private const COLUMNS = [
        'barang' => [
            'image_path' => 'Foto',
            'id_barang' => 'Kode menu',
            'nama_barang' => 'Nama menu',
            'description' => 'Deskripsi',
            'nama_kategori' => 'Kategori',
            'merk' => 'Label',
            'harga_jual' => 'Harga jual',
            'stok' => 'Stok',
            'satuan_barang' => 'Satuan',
            'is_catering_menu' => 'Status',
        ],
        'kategori' => [
            'nama_kategori' => 'Nama kategori',
            'jumlah_barang' => 'Jumlah produk',
            'tgl_input' => 'Tanggal dibuat',
        ],
        'penjualan' => [
            'nama_barang' => 'Produk',
            'jumlah' => 'Jumlah',
            'total' => 'Total',
            'nm_member' => 'Kasir',
            'tanggal_input' => 'Tanggal',
        ],
    ];

    public function index(Request $request): View
    {
        $page = $request->query('page', 'dashboard');
        abort_unless(is_string($page) && isset(self::PAGES[$page]), 404);

        $searchQuery = $request->query('q', '');
        $search = is_string($searchQuery) ? trim($searchQuery) : '';
        $rows = collect();
        $stats = [];
        $revenue = 0;
        $lowStock = 0;
        $incompleteMenuCount = 0;
        $editRecord = null;
        $categories = collect();
        $formAction = $request->query('action');
        $memberId = (int) $request->session()->get('member_id');
        $showForm = in_array($page, ['barang', 'kategori'], true)
            && in_array($formAction, ['create', 'edit'], true);

        if ($page === 'dashboard') {
            $stats = [
                ['label' => 'Menu masakan', 'value' => DB::table('barang')->where('id_member', $memberId)
                    ->where('is_catering_menu', true)->count(), 'page' => 'barang', 'icon' => '▦'],
                ['label' => 'Pesanan', 'value' => DB::table('orders')->where('merchant_id', $memberId)
                    ->where('hidden_from_history', false)->count(), 'page' => 'pesanan', 'icon' => '↗'],
                ['label' => 'Menunggu konfirmasi', 'value' => DB::table('orders')->where('merchant_id', $memberId)
                    ->where('hidden_from_history', false)->where('status', 'pending')->count(), 'page' => 'pesanan', 'icon' => '◷'],
                ['label' => 'Menu tersedia', 'value' => DB::table('barang')->where('id_member', $memberId)
                    ->where('is_catering_menu', true)->whereRaw('CAST(stok AS UNSIGNED) > 0')->count(),
                    'page' => 'barang', 'icon' => '✓'],
            ];
            $incompleteMenuCount = DB::table('barang')->where('id_member', $memberId)
                ->where('is_catering_menu', true)
                ->whereRaw('CAST(stok AS UNSIGNED) = 0')->count();
            $lowStock = DB::table('barang')->where('id_member', $memberId)
                ->where('is_catering_menu', true)
                ->whereRaw('CAST(stok AS UNSIGNED) BETWEEN 1 AND 5')->count();
            $orderStats = DB::table('orders')->where('merchant_id', $memberId)
                ->where('hidden_from_history', false)
                ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN subtotal ELSE 0 END), 0) AS paid_total', ['accepted'])
                ->first();
            $revenue = (float) $orderStats->paid_total;
            $rows = DB::table('orders')
                ->join('member as customer', 'customer.id_member', '=', 'orders.customer_id')
                ->where('orders.merchant_id', $memberId)
                ->where('orders.hidden_from_history', false)
                ->select('orders.id', 'orders.invoice_number', 'customer.nm_member as customer_name',
                    'orders.delivery_date', 'orders.subtotal', 'orders.status', 'orders.created_at')
                ->orderByDesc('orders.created_at')
                ->limit(6)
                ->get();
        } elseif ($page === 'barang') {
            $rows = DB::table('barang')
                ->leftJoin('kategori', 'kategori.id_kategori', '=', 'barang.id_kategori')
                ->where('barang.id_member', $memberId)
                ->select('barang.id', 'barang.image_path', 'barang.id_barang', 'barang.nama_barang',
                    'barang.description', 'kategori.nama_kategori',
                    'barang.merk', 'barang.harga_jual', 'barang.stok', 'barang.satuan_barang',
                    'barang.is_catering_menu')
                ->when($search !== '', function (Builder $query) use ($search): void {
                    $term = '%' . $search . '%';
                    $query->where(function (Builder $query) use ($term): void {
                        $query->where('barang.id_barang', 'like', $term)
                            ->orWhere('barang.nama_barang', 'like', $term)
                            ->orWhere('kategori.nama_kategori', 'like', $term);
                    });
                })
                ->orderByDesc('barang.id')->limit(100)->get();
        } elseif ($page === 'kategori') {
            $rows = DB::table('kategori')
                ->leftJoin('barang', function ($join) use ($memberId): void {
                    $join->on('barang.id_kategori', '=', 'kategori.id_kategori')
                        ->where('barang.id_member', '=', $memberId);
                })
                ->select('kategori.id_kategori', 'kategori.nama_kategori', 'kategori.tgl_input')
                ->selectRaw('COUNT(barang.id) AS jumlah_barang')
                ->when($search !== '', fn (Builder $query) => $query->where('kategori.nama_kategori', 'like', '%' . $search . '%'))
                ->groupBy('kategori.id_kategori', 'kategori.nama_kategori', 'kategori.tgl_input')
                ->orderByDesc('kategori.id_kategori')->limit(100)->get();
        } elseif ($page === 'penjualan') {
            $rows = $this->salesQuery($memberId)
                ->when($search !== '', function (Builder $query) use ($search): void {
                    $term = '%' . $search . '%';
                    $query->where(function (Builder $query) use ($term): void {
                        $query->whereRaw('COALESCE(barang.nama_barang, order_items.product_name, penjualan.id_barang) like ?', [$term])
                            ->orWhere('seller.nm_member', 'like', $term)
                            ->orWhere('penjualan.tanggal_input', 'like', $term);
                    });
                })
                ->orderByDesc('penjualan.id_penjualan')->limit(100)->get();
        }

        if ($showForm && $page === 'barang') {
            $categories = DB::table('kategori')->orderBy('nama_kategori')->get();
        }

        if ($showForm && $formAction === 'edit') {
            $id = filter_var($request->query('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id !== false) {
                $editRecord = match ($page) {
                    'barang' => DB::table('barang')->where('id', $id)->where('id_member', $memberId)->first(),
                    'kategori' => DB::table('kategori')->where('id_kategori', $id)->first(),
                    default => null,
                };
            }
            if (!$editRecord) {
                $showForm = false;
                session()->flash('error', 'Data yang akan diedit tidak ditemukan.');
            }
        }

        return view('dashboard.index', [
            'page' => $page,
            'pages' => self::PAGES,
            'columns' => self::COLUMNS,
            'search' => $search,
            'rows' => $rows,
            'stats' => $stats,
            'revenue' => $revenue,
            'lowStock' => $lowStock,
            'incompleteMenuCount' => $incompleteMenuCount,
            'member' => DB::table('member')->where('id_member', $request->session()->get('member_id'))->first(),
            'showForm' => $showForm,
            'formAction' => $formAction,
            'editRecord' => $editRecord,
            'categories' => $categories,
        ]);
    }

    private function salesQuery(?int $memberId = null): Builder
    {
        return DB::table('penjualan')
            ->leftJoin('barang', function ($join): void {
                $join->on('barang.id_barang', '=', 'penjualan.id_barang')
                    ->on('barang.id_member', '=', 'penjualan.id_member');
            })
            ->leftJoin('order_items', 'order_items.id', '=', 'penjualan.order_item_id')
            ->join('member as seller', 'seller.id_member', '=', 'penjualan.id_member')
            ->when($memberId !== null, fn (Builder $query) => $query->where('penjualan.id_member', $memberId))
            ->select('penjualan.id_penjualan')
            ->selectRaw('COALESCE(barang.nama_barang, order_items.product_name, penjualan.id_barang) AS nama_barang')
            ->addSelect('penjualan.jumlah',
                'penjualan.total', 'seller.nm_member', 'penjualan.tanggal_input');
    }
}
