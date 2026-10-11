<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('invoice_number', 40)->unique();
            $table->unsignedInteger('customer_id')->index();
            $table->unsignedInteger('merchant_id')->index();
            $table->date('delivery_date');
            $table->string('delivery_address', 1000);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('order_id')->index();
            $table->unsignedInteger('product_id')->index();
            $table->string('product_name', 255);
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);
        });

        if (DB::table('barang')->whereNull('id_member')->exists()) {
            $memberId = DB::table('login')
                ->join('member', 'member.id_member', '=', 'login.id_member')
                ->where('member.role', 'merchant')
                ->orderByRaw("CASE WHEN login.user = 'admin' THEN 0 ELSE 1 END")
                ->value('member.id_member');

            if (!$memberId) {
                throw new \RuntimeException('Produk lama memerlukan akun Katering Merchant untuk menetapkan pemilik.');
            }

            DB::table('barang')->whereNull('id_member')->update(['id_member' => $memberId]);
        }

        DB::table('member')->whereNull('role')->update(['role' => 'merchant']);
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
