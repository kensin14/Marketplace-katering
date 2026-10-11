<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function dashboard(Request $request): View
    {
        return view('customer.dashboard', array_merge(
            $this->catalogData($request),
            ['customer' => $this->customer($request), 'searchRoute' => 'customer.dashboard']
        ));
    }

    public function catalog(Request $request): View
    {
        return view('customer.catalog', array_merge(
            $this->catalogData($request),
            ['customer' => $this->customer($request), 'searchRoute' => 'customer.catalog']
        ));
    }

    private function catalogData(Request $request): array
    {
        $term = $request->query('q');
        $location = $request->query('location');
        $categoryId = $request->query('category');

        $products = DB::table('barang')
            ->join('member as merchant', 'merchant.id_member', '=', 'barang.id_member')
            ->leftJoin('kategori', 'kategori.id_kategori', '=', 'barang.id_kategori')
            ->where('merchant.role', 'merchant')
            ->where('barang.is_catering_menu', true)
            ->select(
                'barang.id',
                'barang.nama_barang',
                'barang.description as menu_description',
                'barang.image_path',
                'barang.merk',
                'barang.harga_jual',
                'barang.satuan_barang',
                'barang.stok',
                'merchant.id_member as merchant_id',
                'merchant.company_name as merchant_name',
                'merchant.alamat_member as merchant_location',
                'merchant.description as merchant_description',
                'kategori.id_kategori',
                'kategori.nama_kategori'
            )
            ->when(is_string($term) && trim($term) !== '', function ($query) use ($term): void {
                $search = '%' . trim($term) . '%';
                $query->where(function ($query) use ($search): void {
                    $query->where('barang.nama_barang', 'like', $search)
                        ->orWhere('barang.merk', 'like', $search)
                        ->orWhere('barang.description', 'like', $search)
                        ->orWhere('merchant.company_name', 'like', $search)
                        ->orWhere('merchant.description', 'like', $search);
                });
            })
            ->when(is_string($location) && trim($location) !== '', function ($query) use ($location): void {
                $query->where('merchant.alamat_member', 'like', '%' . trim($location) . '%');
            })
            ->when(filter_var($categoryId, FILTER_VALIDATE_INT), function ($query) use ($categoryId): void {
                $query->where('barang.id_kategori', (int) $categoryId);
            })
            ->orderBy('merchant.nm_member')
            ->orderBy('barang.nama_barang')
            ->limit(100)
            ->get();

        return [
            'products' => $products,
            'availableMenuCount' => $products->filter(function ($product): bool {
                return (int) $product->stok > 0;
            })->count(),
            'merchantCount' => $products->pluck('merchant_id')->unique()->count(),
            'categories' => DB::table('kategori')
                ->whereIn('id_kategori', DB::table('barang')
                    ->select('id_kategori')->where('is_catering_menu', true)->distinct())
                ->orderBy('nama_kategori')
                ->get(),
            'cartCount' => array_sum(session('marketplace_cart.items', [])),
            'filters' => [
                'q' => is_string($term) ? $term : '',
                'location' => is_string($location) ? $location : '',
                'category' => is_string($categoryId) ? $categoryId : '',
            ],
        ];
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $product = DB::table('barang')
            ->join('member', 'member.id_member', '=', 'barang.id_member')
            ->where('barang.id', $data['product_id'])
            ->where('member.role', 'merchant')
            ->where('barang.is_catering_menu', true)
            ->select('barang.id', 'barang.id_member', 'barang.stok')
            ->first();

        if (!$product || (int) $product->stok < (int) $data['quantity']) {
            throw ValidationException::withMessages(['product_id' => 'Menu tidak tersedia atau stoknya tidak mencukupi.']);
        }

        $cart = session('marketplace_cart', ['items' => []]);
        $cart['items'] = $cart['items'] ?? [];
        $cart['items'][$product->id] = (int) ($cart['items'][$product->id] ?? 0) + (int) $data['quantity'];
        if ($cart['items'][$product->id] > (int) $product->stok) {
            throw ValidationException::withMessages(['quantity' => 'Jumlah melebihi stok menu yang tersedia.']);
        }

        $request->session()->put('marketplace_cart', $cart);

        return redirect()->route('customer.cart')->with('status', 'Menu ditambahkan ke keranjang.');
    }

    public function cart(Request $request): View
    {
        return view('customer.cart', $this->cartData($request));
    }

    public function removeFromCart(Request $request, int $product): RedirectResponse
    {
        $cart = session('marketplace_cart', ['items' => []]);
        unset($cart['items'][$product]);

        if (empty($cart['items'])) {
            $request->session()->forget('marketplace_cart');
        } else {
            $request->session()->put('marketplace_cart', $cart);
        }

        return redirect()->route('customer.cart');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'delivery_date' => ['required', 'date', 'after_or_equal:today'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $cart = session('marketplace_cart', ['items' => []]);
        if (empty($cart['items'])) {
            throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong.']);
        }

        $orderIds = DB::transaction(function () use ($request, $data, $cart): array {
            $products = DB::table('barang')
                ->join('member', 'member.id_member', '=', 'barang.id_member')
                ->whereIn('barang.id', array_keys($cart['items']))
                ->where('member.role', 'merchant')
                ->where('barang.is_catering_menu', true)
                ->select('barang.*')
                ->orderBy('barang.id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($cart['items'])) {
                throw ValidationException::withMessages(['cart' => 'Salah satu menu dalam keranjang sudah tidak tersedia. Perbarui keranjang sebelum memesan.']);
            }

            $linesByMerchant = [];
            foreach ($cart['items'] as $productId => $quantity) {
                $product = $products->get($productId);
                if (!$product || (int) $product->stok < (int) $quantity) {
                    throw ValidationException::withMessages(['cart' => 'Stok salah satu menu berubah. Perbarui keranjang sebelum memesan.']);
                }

                $linesByMerchant[$product->id_member][] = [
                    'product' => $product,
                    'quantity' => (int) $quantity,
                    'unit_price' => (float) $product->harga_jual,
                    'line_total' => (float) $product->harga_jual * (int) $quantity,
                ];
            }

            $orderIds = [];
            foreach ($linesByMerchant as $merchantId => $lines) {
                $subtotal = array_sum(array_column($lines, 'line_total'));
                $orderId = DB::table('orders')->insertGetId([
                    'invoice_number' => 'INV-' . now()->format('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(3))),
                    'customer_id' => $request->session()->get('member_id'),
                    'merchant_id' => $merchantId,
                    'delivery_date' => $data['delivery_date'],
                    'delivery_address' => $data['delivery_address'],
                    'notes' => $data['notes'] ?? null,
                    'status' => 'pending',
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($lines as $line) {
                    DB::table('order_items')->insert([
                        'order_id' => $orderId,
                        'product_id' => $line['product']->id,
                        'product_name' => $line['product']->nama_barang,
                        'unit_price' => $line['unit_price'],
                        'quantity' => $line['quantity'],
                        'line_total' => $line['line_total'],
                    ]);
                    DB::table('barang')->where('id', $line['product']->id)
                        ->update(['stok' => (int) $line['product']->stok - $line['quantity']]);
                }

                $orderIds[] = $orderId;
            }

            return $orderIds;
        });

        $request->session()->forget('marketplace_cart');

        return redirect()->route('customer.orders')
            ->with('status', count($orderIds) . ' pesanan berhasil dibuat. Setiap merchant memiliki invoice terpisah.');
    }

    public function orders(Request $request): View
    {
        $orders = DB::table('orders')
            ->join('member as merchant', 'merchant.id_member', '=', 'orders.merchant_id')
            ->where('orders.customer_id', $request->session()->get('member_id'))
            ->where('orders.hidden_from_history', false)
            ->select('orders.*', 'merchant.nm_member as merchant_name')
            ->orderByDesc('orders.created_at')
            ->simplePaginate(25)
            ->withQueryString();

        return view('customer.orders', [
            'orders' => $orders,
            'customer' => $this->customer($request),
            'cartCount' => array_sum(session('marketplace_cart.items', [])),
        ]);
    }

    private function cartData(Request $request): array
    {
        $cart = session('marketplace_cart', ['items' => []]);
        $cart['items'] = $cart['items'] ?? [];
        $products = collect();

        if (!empty($cart['items'])) {
            $products = DB::table('barang')
                ->join('member as merchant', 'merchant.id_member', '=', 'barang.id_member')
                ->whereIn('id', array_keys($cart['items']))
                ->where('merchant.role', 'merchant')
                ->where('is_catering_menu', true)
                ->select('barang.*', 'merchant.nm_member as merchant_name',
                    'merchant.alamat_member as merchant_address', 'merchant.id_member as merchant_id')
                ->get()
                ->map(function ($product) use ($cart) {
                    $product->quantity = (int) $cart['items'][$product->id];
                    $product->line_total = (float) $product->harga_jual * $product->quantity;

                    return $product;
                });

            if ($products->count() !== count($cart['items'])) {
                $request->session()->forget('marketplace_cart');
                throw ValidationException::withMessages([
                    'cart' => 'Menu dalam keranjang sudah tidak tersedia. Keranjang telah dikosongkan; silakan pilih menu kembali.',
                ]);
            }
        }

        return [
            'products' => $products,
            'merchantGroups' => $products->groupBy('merchant_id'),
            'subtotal' => $products->sum('line_total'),
            'customer' => $this->customer($request),
            'cartCount' => array_sum($cart['items']),
        ];
    }

    private function customer(Request $request): object
    {
        return DB::table('member')->where('id_member', $request->session()->get('member_id'))->first();
    }
}
