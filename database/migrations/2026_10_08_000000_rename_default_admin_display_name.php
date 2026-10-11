<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OWNER_NAME = 'Pemilik Marketplace Katring';

    private const LEGACY_NAMES = ['Administrator', 'admin kasir'];

    public function up(): void
    {
        $ownerId = DB::table('login')->where('user', 'admin')->value('id_member');

        if (!$ownerId) {
            return;
        }

        DB::table('member')
            ->where('id_member', $ownerId)
            ->whereIn('nm_member', self::LEGACY_NAMES)
            ->update(['nm_member' => self::OWNER_NAME]);

        DB::table('member')
            ->where('id_member', $ownerId)
            ->whereIn('company_name', self::LEGACY_NAMES)
            ->update(['company_name' => self::OWNER_NAME]);
    }

    public function down(): void
    {
        $ownerId = DB::table('login')->where('user', 'admin')->value('id_member');

        if (!$ownerId) {
            return;
        }

        DB::table('member')
            ->where('id_member', $ownerId)
            ->where('nm_member', self::OWNER_NAME)
            ->update(['nm_member' => 'Administrator']);

        DB::table('member')
            ->where('id_member', $ownerId)
            ->where('company_name', self::OWNER_NAME)
            ->update(['company_name' => 'Administrator']);
    }
};
