<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->boolean('hidden_from_history')->default(false);
        });

        DB::table('orders')
            ->whereIn('status', ['accepted', 'rejected'])
            ->update(['hidden_from_history' => true]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('hidden_from_history');
        });
    }
};
