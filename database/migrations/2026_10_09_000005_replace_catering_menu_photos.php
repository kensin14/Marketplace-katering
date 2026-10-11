<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PHOTO_PATHS = [
        'nasi-ayam-bakar.svg' => 'nasi-ayam-bakar.jpg',
        'nasi-goreng-spesial.svg' => 'nasi-goreng-spesial.jpg',
        'ayam-geprek.svg' => 'ayam-geprek.jpg',
        'soto-ayam.svg' => 'soto-ayam.jpg',
        'nasi-rendang.svg' => 'nasi-rendang.jpg',
    ];

    public function up(): void
    {
        foreach (self::PHOTO_PATHS as $oldPath => $newPath) {
            DB::table('barang')->where('image_path', $oldPath)->update(['image_path' => $newPath]);
        }
    }

    public function down(): void
    {
        foreach (self::PHOTO_PATHS as $oldPath => $newPath) {
            DB::table('barang')->where('image_path', $newPath)->update(['image_path' => $oldPath]);
        }
    }
};
