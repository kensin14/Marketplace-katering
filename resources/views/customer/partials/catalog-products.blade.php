<form class="market-panel filter-grid" method="GET" action="{{ route($searchRoute) }}">
    <label class="market-field">Menu atau katering
        <input name="q" type="search" value="{{ $filters['q'] }}" placeholder="Contoh: nasi ayam">
    </label>
    <label class="market-field">Lokasi
        <input name="location" value="{{ $filters['location'] }}" placeholder="Kota atau alamat">
    </label>
    <label class="market-field">Jenis makanan
        <select name="category">
            <option value="">Semua jenis</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id_kategori }}" @selected($filters['category'] === (string) $category->id_kategori)>
                    {{ $category->nama_kategori }}
                </option>
            @endforeach
        </select>
    </label>
    <button class="market-button" type="submit">Cari katering</button>
</form>

@if ($products->isNotEmpty())
    <section class="product-grid" aria-label="Daftar menu katering">
        @foreach ($products as $product)
            <article class="product-card">
                @if ($product->image_path)
                    <img class="product-image" src="{{ asset('menu-photos/' . $product->image_path) }}"
                         alt="Foto {{ $product->nama_barang }}" loading="lazy">
                @else
                    <div class="product-image-placeholder" aria-label="Foto menu belum tersedia">
                        <strong>{{ $product->nama_barang }}</strong>
                        <span>Foto menu akan ditambahkan katering</span>
                    </div>
                @endif
                <span class="product-category">{{ $product->nama_kategori ?? 'Menu katering' }}</span>
                <h2>{{ $product->nama_barang }}</h2>
                <p class="product-merchant">{{ $product->merchant_name }}</p>
                <p class="product-location">{{ $product->merchant_location }}</p>
                @if ($product->merchant_description)<p class="product-description">{{ $product->merchant_description }}</p>@endif
                @if ($product->menu_description && !str_starts_with($product->menu_description, 'DRAF MENU.'))
                    <p class="product-description">{{ $product->menu_description }}</p>
                @endif
                @if ((int) $product->stok > 0)
                    <p class="product-stock">Tersedia {{ $product->stok }} {{ $product->satuan_barang }}</p>
                @else
                    <p class="product-stock">Belum tersedia. Katering sedang menyiapkan stok.</p>
                @endif
                <div class="product-bottom">
                    <div class="product-price">Rp {{ number_format((float) $product->harga_jual, 0, ',', '.') }}
                        <small>per {{ $product->satuan_barang }}</small>
                    </div>
                    @if ((int) $product->stok > 0)
                        <form class="add-form" method="POST" action="{{ route('customer.cart.add') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <label class="visually-hidden" for="quantity-{{ $product->id }}">Jumlah porsi</label>
                            <input id="quantity-{{ $product->id }}" name="quantity" type="number" min="1"
                                max="{{ $product->stok }}" value="1" aria-label="Jumlah porsi" required>
                            <button class="market-button" type="submit">Tambah</button>
                        </form>
                    @else
                        <button class="market-button" type="button" disabled>Belum tersedia</button>
                    @endif
                </div>
            </article>
        @endforeach
    </section>
@else
    <div class="market-panel market-empty">Belum ada menu yang cocok. Coba ubah kata kunci, kategori, atau lokasi.</div>
@endif
