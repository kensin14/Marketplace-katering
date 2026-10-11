<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan penjualan - {{ config('app.name') }}</title>
    <style>
        body { color: #182230; font-family: Arial, sans-serif; margin: 32px auto; max-width: 1100px; padding: 0 20px; }
        h1 { margin-bottom: 4px; }
        .muted { color: #667085; margin-top: 0; }
        table { border-collapse: collapse; margin-top: 24px; width: 100%; }
        th, td { border: 1px solid #cbd2dc; padding: 9px; text-align: left; }
        th { background: #f1f4f8; }
        .actions { margin: 18px 0; }
        @media print { .actions { display: none; } body { margin: 0; max-width: none; } }
    </style>
</head>
<body>
    <h1>Laporan penjualan</h1>
    <p class="muted">Dibuat {{ $generatedAt->locale('id')->translatedFormat('j F Y, H:i') }}</p>
    <div class="actions"><button type="button" onclick="window.print()">Cetak / Simpan sebagai PDF</button></div>
    <table>
        <thead><tr><th>Kode</th><th>Barang</th><th>Jumlah</th><th>Total</th><th>Kasir</th><th>Tanggal</th></tr></thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->id_barang }}</td>
                    <td>{{ $row->nama_barang ?? '-' }}</td>
                    <td>{{ $row->jumlah }}</td>
                    <td>Rp {{ number_format((float) $row->total, 0, ',', '.') }}</td>
                    <td>{{ $row->nm_member ?? '-' }}</td>
                    <td>{{ $row->tanggal_input }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada transaksi.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
