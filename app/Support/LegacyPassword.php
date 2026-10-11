<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LegacyPassword
{
    public static function canStoreHash(): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return true;
        }

        $column = DB::selectOne("SHOW COLUMNS FROM `login` WHERE Field = 'pass'");
        if (!$column || !preg_match('/^(?:var)?char\((\d+)\)/i', $column->Type, $matches)) {
            return false;
        }

        return (int) $matches[1] >= 60;
    }

    public static function requireHashStorage(): void
    {
        if (!self::canStoreHash()) {
            throw ValidationException::withMessages([
                'password' => 'Kolom login.pass harus bertipe VARCHAR(255). Jalankan perintah ALTER TABLE pada README terlebih dahulu.',
            ]);
        }
    }
}
