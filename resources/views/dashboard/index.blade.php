@php
    $memberName = $member->nm_member ?? 'Pengguna';
    $icons = ['dashboard' => '⌂', 'barang' => '▦', 'kategori' => '◇', 'penjualan' => '↗'];
    $descriptions = [
        'dashboard' => 'Pantau kondisi toko Anda dalam satu tampilan.',
        'barang' => 'Lihat daftar produk dan informasi stok.',
        'kategori' => 'Lihat kategori dan jumlah produk di setiap kategori.',
        'penjualan' => 'Periksa riwayat transaksi penjualan.',
    ];
    $subject = ['barang' => 'Barang', 'kategori' => 'Kategori'][$page] ?? '';
    $recordId = $page === 'barang' ? 'id' : 'id_kategori';
    $getValue = static fn (string $key, mixed $default = '') => old($key, data_get($editRecord, $key, $default));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pages[$page] }} - {{ config('app.name') }}</title>
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('merchant.dashboard') }}">
                <span class="brand-icon">MK</span>
                <span><strong>{{ config('app.name') }}</strong><small>Manajemen toko</small></span>
            </a>

            <p class="nav-caption">MENU UTAMA</p>
            <nav class="main-nav" aria-label="Menu utama">
                @foreach ($pages as $key => $label)
                    <a class="nav-link{{ $page === $key ? ' active' : '' }}"
                       href="{{ route('merchant.dashboard', $key === 'dashboard' ? [] : ['page' => $key]) }}"
                       @if ($page === $key) aria-current="page" @endif>
                        <span class="nav-icon" aria-hidden="true">{{ $icons[$key] }}</span>
                        {{ $label }}
                    </a>
                @endforeach
                <a class="nav-link" href="{{ route('merchant.orders.index') }}">
                    <span class="nav-icon" aria-hidden="true">▤</span> Pesanan katering
                </a>
                <a class="nav-link{{ request()->routeIs('merchant.profile.*') ? ' active' : '' }}"
                   href="{{ route('merchant.profile.edit') }}">
                    <span class="nav-icon" aria-hidden="true">♙</span> Profil katering
                </a>
            </nav>

            <div class="sidebar-bottom">
                <div class="profile">
                    <span class="avatar">{{ mb_strtoupper(mb_substr($memberName, 0, 1)) }}</span>
                    <span class="profile-copy"><strong>{{ $memberName }}</strong><small>Katering Merchant</small></span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-link" type="submit"><span aria-hidden="true">↪</span> Keluar</button>
                </form>
            </div>
        </aside>

        <div class="main-area">
            <header class="topbar">
                <div>
                    <p class="topbar-label">MARKETPLACE KATERING <span>/</span> {{ $pages[$page] }}</p>
                    <p class="topbar-date">{{ now()->locale('id')->translatedFormat('l, j F Y') }}</p>
                </div>
            </header>

            <main class="content">
                <section class="page-heading">
                    <div>
                        <p class="eyebrow">{{ $page === 'dashboard' ? 'RINGKASAN TOKO' : 'MANAJEMEN TOKO' }}</p>
                        <h1>{{ $page === 'dashboard' ? 'Selamat datang, ' . $memberName : $pages[$page] }}</h1>
                        <p class="heading-description">{{ $descriptions[$page] }}</p>
                    </div>
                    @if ($page !== 'dashboard')
                        <div class="heading-actions">
                            @if (in_array($page, ['barang', 'kategori'], true))
                                <a class="button button-primary" href="{{ route('merchant.dashboard', ['page' => $page, 'action' => 'create']) }}">
                                    <span aria-hidden="true">＋</span> Tambah {{ $subject }}
                                </a>
                            @endif
                            @if ($page === 'penjualan')
                                <a class="button button-plain" href="{{ route('merchant.reports.excel') }}">Ekspor CSV</a>
                                <a class="button button-plain" href="{{ route('merchant.reports.print') }}" target="_blank" rel="noopener">Cetak / PDF</a>
                            @endif
                            <form class="search-form" method="GET" action="{{ route('merchant.dashboard') }}">
                                <input type="hidden" name="page" value="{{ $page }}">
                                <label class="visually-hidden" for="dashboard-search">Cari data</label>
                                <input id="dashboard-search" name="q" type="search" placeholder="Cari data..." value="{{ $search }}">
                                <button type="submit" aria-label="Cari">⌕</button>
                            </form>
                        </div>
                    @endif
                </section>

                @if (session('status'))
                    <div class="notice notice-success" role="status">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="notice notice-error" role="alert">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="notice notice-error" role="alert">{{ $errors->first() }}</div>
                @endif

                @if ($page === 'dashboard')
                    @if ($incompleteMenuCount > 0)
                        <div class="notice notice-warning" role="status">
                            {{ number_format($incompleteMenuCount) }} menu belum tersedia untuk kantor. Atur stok menu agar pemesanan dapat dilakukan.
                            <a href="{{ route('merchant.dashboard', ['page' => 'barang']) }}">Lengkapi data menu</a>.
                        </div>
                    @endif
                    <section class="stats-grid" aria-label="Statistik toko">
                        @foreach ($stats as $stat)
                            <a class="stat-card" href="{{ $stat['page'] === 'pesanan' ? route('merchant.orders.index') : route('merchant.dashboard', ['page' => $stat['page']]) }}">
                                <span class="stat-icon">{{ $stat['icon'] }}</span>
                                <span class="stat-label">{{ $stat['label'] }}</span>
                                <strong>{{ number_format($stat['value']) }}</strong>
                                <span class="stat-foot">Lihat detail <span aria-hidden="true">→</span></span>
                            </a>
                        @endforeach
                    </section>

                    <section class="insight-grid">
                        <div class="insight-card revenue-card">
                            <div>
                                <p class="card-kicker">TOTAL PESANAN DITERIMA</p>
                                <h2>Rp {{ number_format($revenue, 0, ',', '.') }}</h2>
                                <p class="muted">Nilai dari pesanan yang diterima</p>
                            </div>
                            <span class="revenue-icon" aria-hidden="true">↗</span>
                        </div>
                        <a class="insight-card stock-card" href="{{ route('merchant.dashboard', ['page' => 'barang']) }}">
                            <span class="stock-icon" aria-hidden="true">!</span>
                            <span><strong>{{ number_format($lowStock) }} menu</strong><small>Stok menipis (5 atau kurang)</small></span>
                            <span class="arrow" aria-hidden="true">→</span>
                        </a>
                    </section>

                    <section class="panel">
                        <div class="panel-heading">
                            <div>
                                <h2>Pesanan terbaru</h2>
                                <p>Permintaan katering terbaru dari kantor</p>
                            </div>
                            <a href="{{ route('merchant.orders.index') }}">Lihat semua <span aria-hidden="true">→</span></a>
                        </div>
                        @if ($rows->isNotEmpty())
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Kantor</th>
                                            <th>Tanggal kirim</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rows as $row)
                                            <tr>
                                                <td class="primary-cell">{{ $row->invoice_number }}</td>
                                                <td>{{ $row->customer_name }}</td>
                                                <td>{{ \Illuminate\Support\Carbon::parse($row->delivery_date)->locale('id')->translatedFormat('j F Y') }}</td>
                                                <td>Rp {{ number_format((float) $row->subtotal, 0, ',', '.') }}</td>
                                                <td>
                                                    {{ $row->status === 'pending' ? 'Menunggu' : ($row->status === 'accepted' ? 'Diterima' : 'Ditolak') }}
                                                </td>
                                                <td>
                                                    <a class="table-action table-action-edit"
                                                       href="{{ route('merchant.orders.invoice', $row->id) }}">Invoice</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state">Belum ada pesanan katering.</div>
                        @endif
                    </section>
                @else
                    @if ($showForm)
                        <section class="panel form-panel">
                            <div class="panel-heading">
                                <div>
                                    <h2>{{ $formAction === 'edit' ? 'Edit' : 'Tambah' }} {{ $subject }}</h2>
                                    <p>Isi data berikut lalu simpan perubahan.</p>
                                </div>
                            </div>
                            <form class="manage-form" method="POST" enctype="multipart/form-data" action="{{ route('merchant.manage') }}">
                                @csrf
                                <input type="hidden" name="page" value="{{ $page }}">
                                <input type="hidden" name="action" value="{{ $formAction === 'edit' ? 'update' : 'create' }}">
                                @if ($formAction === 'edit')
                                    <input type="hidden" name="id" value="{{ data_get($editRecord, $recordId) }}">
                                @endif

                                @if ($page === 'barang')
                                    <div class="form-grid">
                                        <label>Kode menu
                                            <input name="id_barang" maxlength="255" value="{{ $getValue('id_barang') }}" required>
                                        </label>
                                        <label>Nama menu masakan
                                            <input name="nama_barang" maxlength="255" value="{{ $getValue('nama_barang') }}" required>
                                        </label>
                                        <label class="field-wide">Deskripsi menu
                                            <textarea name="description" rows="3" maxlength="2000" required>{{ $getValue('description') }}</textarea>
                                        </label>
                                        <label>Kategori
                                            <select name="id_kategori" required>
                                                <option value="">Pilih kategori</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id_kategori }}" @selected((string) $getValue('id_kategori') === (string) $category->id_kategori)>{{ $category->nama_kategori }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label>Label menu
                                            <input name="merk" maxlength="255" value="{{ $getValue('merk') }}" required>
                                        </label>
                                        <label>Harga beli
                                            <input name="harga_beli" type="number" min="0" step="any" value="{{ $getValue('harga_beli') }}" required>
                                        </label>
                                        <label>Harga jual
                                            <input name="harga_jual" type="number" min="0" step="any" value="{{ $getValue('harga_jual') }}" required>
                                        </label>
                                        <label>Satuan
                                            <input name="satuan_barang" maxlength="255" value="{{ $getValue('satuan_barang') }}" required>
                                        </label>
                                        <label>Stok
                                            <input name="stok" type="number" min="0" step="1" value="{{ $getValue('stok', '0') }}" required>
                                        </label>
                                        <label class="field-wide">Foto menu (JPG, PNG, atau WebP; maks. 4 MB) {{ $formAction === 'edit' ? '(kosongkan jika tidak diganti)' : '' }}
                                            <input name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" @required($formAction === 'create')>
                                        </label>
                                        @if ($formAction === 'edit' && $editRecord->image_path)
                                            <div class="field-wide">
                                                <img class="menu-preview" src="{{ asset('menu-photos/' . $editRecord->image_path) }}" alt="Foto {{ $editRecord->nama_barang }}">
                                            </div>
                                        @endif
                                    </div>
                                @elseif ($page === 'kategori')
                                    <div class="form-grid form-grid-single">
                                        <label>Nama kategori
                                            <input name="nama_kategori" maxlength="255" value="{{ $getValue('nama_kategori') }}" required>
                                        </label>
                                    </div>
                                @endif

                                <div class="form-actions">
                                    <a class="button button-plain" href="{{ route('merchant.dashboard', ['page' => $page]) }}">Batal</a>
                                    <button class="button button-primary" type="submit">{{ $formAction === 'edit' ? 'Simpan perubahan' : 'Simpan' }}</button>
                                </div>
                            </form>
                        </section>
                    @endif

                    <section class="panel list-panel">
                        <div class="panel-heading">
                            <div>
                                <h2>{{ $pages[$page] }}</h2>
                                <p>{{ $page === 'penjualan' ? 'Riwayat transaksi hanya dapat dilihat.' : 'Kelola data ' . mb_strtolower($pages[$page]) . '.' }}
                                    {{ number_format($rows->count()) }} data ditampilkan{{ $rows->count() === 100 ? ' (maksimal 100)' : '' }}
                                </p>
                            </div>
                        </div>
                        @if ($rows->isNotEmpty())
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            @foreach ($columns[$page] as $label)
                                                <th>{{ $label }}</th>
                                            @endforeach
                                            @if (in_array($page, ['barang', 'kategori'], true))
                                                <th>Aksi</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rows as $row)
                                            <tr>
                                                @foreach ($columns[$page] as $key => $label)
                                                    @php
                                                        $value = $row->{$key} ?? '-';
                                                        if (in_array($key, ['harga_jual', 'total'], true)) {
                                                            $value = 'Rp ' . number_format((float) $value, 0, ',', '.');
                                                        } elseif ($key === 'stok' && (int) $value <= 5) {
                                                            $value = $value . ' (menipis)';
                                                        } elseif ($key === 'is_catering_menu') {
                                                            $value = !$row->is_catering_menu
                                                                ? 'Arsip riwayat'
                                                                : ((int) $row->stok === 0 ? 'Belum tersedia' : 'Menu aktif');
                                                        }
                                                    @endphp
                                                    <td class="{{ in_array($key, ['nama_barang', 'nama_kategori', 'nm_member'], true) ? 'primary-cell' : '' }}">
                                                        @if ($key === 'image_path')
                                                            @if ($row->image_path)
                                                                <img class="menu-thumbnail"
                                                                     src="{{ asset('menu-photos/' . $row->image_path) }}"
                                                                     alt="Foto menu">
                                                            @else
                                                                Belum ada foto
                                                            @endif
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                                @if (in_array($page, ['barang', 'kategori'], true))
                                                    <td class="action-cell">
                                                        <a class="table-action table-action-edit"
                                                           href="{{ route('merchant.dashboard', ['page' => $page, 'action' => 'edit', 'id' => $row->{$recordId}]) }}">Edit</a>
                                                        <form method="POST" action="{{ route('merchant.manage') }}" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                                            @csrf
                                                            <input type="hidden" name="page" value="{{ $page }}">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="{{ $row->{$recordId} }}">
                                                            <button class="table-action table-action-delete" type="submit">Hapus</button>
                                                        </form>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state">{{ $search !== '' ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada data untuk ditampilkan.' }}</div>
                        @endif
                    </section>
                @endif

                <footer class="app-footer">{{ config('app.name') }} <span>•</span> by kensin14 (2026)</footer>
            </main>
        </div>
    </div>
</body>
</html>
