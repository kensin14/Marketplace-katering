<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';
require __DIR__.'/merchant.php';
require __DIR__.'/customer.php';

Route::middleware('member.auth')->group(function (): void {
    Route::get('/', function () {
        $role = DB::table('member')
            ->where('id_member', session('member_id'))
            ->value('role');

        return redirect()->route($role === 'customer' ? 'customer.dashboard' : 'merchant.dashboard');
    })->name('dashboard');
});
