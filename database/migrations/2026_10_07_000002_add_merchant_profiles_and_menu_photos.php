<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member', function (Blueprint $table): void {
            $table->string('company_name')->nullable();
            $table->text('description')->nullable();
        });

        Schema::table('barang', function (Blueprint $table): void {
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
        });

        DB::table('member')->whereNull('company_name')->update([
            'company_name' => DB::raw('nm_member'),
        ]);
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table): void {
            $table->dropColumn(['description', 'image_path']);
        });

        Schema::table('member', function (Blueprint $table): void {
            $table->dropColumn(['company_name', 'description']);
        });
    }
};
