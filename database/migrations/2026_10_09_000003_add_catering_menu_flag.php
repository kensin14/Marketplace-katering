<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table): void {
            $table->boolean('is_catering_menu')->default(false)->after('id_member');
            $table->index('is_catering_menu');
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table): void {
            $table->dropIndex(['is_catering_menu']);
            $table->dropColumn('is_catering_menu');
        });
    }
};
