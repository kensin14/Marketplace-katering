<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori', function (Blueprint $table): void {
            $table->increments('id_kategori');
            $table->string('nama_kategori')->unique();
            $table->string('tgl_input');
        });

        Schema::create('member', function (Blueprint $table): void {
            $table->increments('id_member');
            $table->string('nm_member');
            $table->text('alamat_member');
            $table->string('telepon');
            $table->string('email');
            $table->text('gambar');
            $table->text('NIK');
            $table->string('role', 20)->default('merchant');
        });

        Schema::create('login', function (Blueprint $table): void {
            $table->increments('id_login');
            $table->string('user')->unique();
            $table->string('pass');
            $table->unsignedInteger('id_member')->index();
        });

        Schema::create('barang', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_member')->nullable()->index();
            $table->string('id_barang')->unique();
            $table->unsignedInteger('id_kategori')->index();
            $table->text('nama_barang');
            $table->string('merk');
            $table->string('harga_beli');
            $table->string('harga_jual');
            $table->string('satuan_barang');
            $table->text('stok');
            $table->string('tgl_input');
            $table->string('tgl_update')->nullable();
        });

        Schema::create('nota', function (Blueprint $table): void {
            $table->increments('id_nota');
            $table->string('id_barang');
            $table->unsignedInteger('id_member');
            $table->string('jumlah');
            $table->string('total');
            $table->string('tanggal_input');
            $table->string('periode')->nullable();
        });

        Schema::create('penjualan', function (Blueprint $table): void {
            $table->increments('id_penjualan');
            $table->string('id_barang');
            $table->unsignedInteger('id_member');
            $table->string('jumlah');
            $table->string('total');
            $table->string('tanggal_input');
        });

        Schema::create('toko', function (Blueprint $table): void {
            $table->increments('id_toko');
            $table->string('nama_toko');
            $table->text('alamat_toko');
            $table->string('tlp');
            $table->string('nama_pemilik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toko');
        Schema::dropIfExists('penjualan');
        Schema::dropIfExists('nota');
        Schema::dropIfExists('barang');
        Schema::dropIfExists('login');
        Schema::dropIfExists('member');
        Schema::dropIfExists('kategori');
    }
};
