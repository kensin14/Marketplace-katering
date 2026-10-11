<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MerchantProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $merchant = DB::table('member')
            ->where('id_member', $request->session()->get('member_id'))
            ->first();

        abort_unless($merchant, 404);

        return view('merchant.profile', ['merchant' => $merchant]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'alamat_member' => ['required', 'string', 'max:1000'],
            'telepon' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        DB::table('member')
            ->where('id_member', $request->session()->get('member_id'))
            ->where('role', 'merchant')
            ->update([
                'company_name' => $data['company_name'],
                'nm_member' => $data['company_name'],
                'alamat_member' => $data['alamat_member'],
                'telepon' => $data['telepon'],
                'email' => $data['email'],
                'description' => $data['description'],
            ]);

        return redirect()->route('merchant.profile.edit')->with('status', 'Profil katering berhasil diperbarui.');
    }
}
