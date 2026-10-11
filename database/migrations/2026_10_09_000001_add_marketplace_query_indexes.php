<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->index(['merchant_id', 'created_at'], 'orders_merchant_created_index');
            $table->index(['customer_id', 'created_at'], 'orders_customer_created_index');
        });

        Schema::table('penjualan', function (Blueprint $table): void {
            $table->index(['id_member', 'id_penjualan'], 'penjualan_member_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table): void {
            $table->dropIndex('penjualan_member_id_index');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_merchant_created_index');
            $table->dropIndex('orders_customer_created_index');
        });
    }
};
