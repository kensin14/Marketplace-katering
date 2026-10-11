<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class DraftCateringMenus
{
    private const MENUS = [
        ['Nasi Ayam Bakar', 25000, 'nasi-ayam-bakar.jpg'],
        ['Nasi Goreng Spesial', 22000, 'nasi-goreng-spesial.jpg'],
        ['Ayam Geprek Sambal Bawang', 20000, 'ayam-geprek.jpg'],
        ['Soto Ayam', 18000, 'soto-ayam.jpg'],
        ['Nasi Rendang', 30000, 'nasi-rendang.jpg'],
    ];

    public function seedForMerchant(int $merchantId): void
    {
        DB::transaction(function () use ($merchantId): void {
            if (!DB::table('member')->where('id_member', $merchantId)->where('role', 'merchant')->exists()) {
                throw new \RuntimeException('Akun merchant tidak ditemukan untuk pembuatan menu draf.');
            }

            $categoryId = DB::table('kategori')->where('nama_kategori', 'MASAKAN')->value('id_kategori');
            if (!$categoryId) {
                $categoryId = DB::table('kategori')->insertGetId([
                    'nama_kategori' => 'MASAKAN',
                    'tgl_input' => now()->locale('id')->translatedFormat('j F Y, H:i'),
                ], 'id_kategori');
            }

            foreach (self::MENUS as $index => [$name, $draftPrice, $imagePath]) {
                $code = sprintf('MK%04d-%02d', $merchantId, $index + 1);
                $existing = DB::table('barang')->where('id_barang', $code)->first();
                if ($existing) {
                    if ((int) $existing->id_member !== $merchantId) {
                        throw new \RuntimeException('Kode menu draf sudah digunakan oleh akun merchant lain: ' . $code);
                    }
                    continue;
                }

                DB::table('barang')->insert([
                    'id_barang' => $code,
                    'id_member' => $merchantId,
                    'is_catering_menu' => true,
                    'id_kategori' => $categoryId,
                    'nama_barang' => $name,
                    'description' => 'Menu masakan katering. Merchant dapat menyesuaikan deskripsi sesuai sajian.',
                    'merk' => 'Menu katering',
                    'harga_beli' => 0,
                    'harga_jual' => $draftPrice,
                    'satuan_barang' => 'porsi',
                    'stok' => 0,
                    'tgl_input' => now()->locale('id')->translatedFormat('j F Y, H:i'),
                    'tgl_update' => null,
                    'image_path' => $imagePath,
                ]);
            }
        });
    }
}
