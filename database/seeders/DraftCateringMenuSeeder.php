<?php

namespace Database\Seeders;

use App\Support\DraftCateringMenus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DraftCateringMenuSeeder extends Seeder
{
    public function run(): void
    {
        $draftMenus = app(DraftCateringMenus::class);
        $merchants = DB::table('member')->where('role', 'merchant')->orderBy('id_member')->pluck('id_member');

        foreach ($merchants as $merchantId) {
            $draftMenus->seedForMerchant((int) $merchantId);
        }
    }
}
