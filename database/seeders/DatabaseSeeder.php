<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = DB::table('login')->where('user', 'admin')->value('id_member');

        if ($ownerId) {
            $legacyNames = ['Administrator', 'admin kasir'];

            DB::table('member')
                ->where('id_member', $ownerId)
                ->whereIn('nm_member', $legacyNames)
                ->update(['nm_member' => 'Pemilik Marketplace Katring']);

            DB::table('member')
                ->where('id_member', $ownerId)
                ->whereIn('company_name', $legacyNames)
                ->update(['company_name' => 'Pemilik Marketplace Katring']);
        }

        $this->call(DraftCateringMenuSeeder::class);
    }
}
