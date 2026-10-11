<?php

namespace App\Http\Controllers;

use App\Support\DraftCateringMenus;
use App\Support\LegacyPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('member_id')) {
            return $this->redirectForRole($request);
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'user' => ['required', 'string', 'max:255'],
            'pass' => ['required', 'string'],
        ]);

        $account = DB::table('login')
            ->join('member', 'member.id_member', '=', 'login.id_member')
            ->where('login.user', $credentials['user'])
            ->select('login.id_login', 'login.pass', 'member.id_member', 'member.nm_member', 'member.role')
            ->first();

        $storedPassword = $account ? (string) $account->pass : '';
        $isLegacyHash = preg_match('/^[a-f0-9]{32}$/i', $storedPassword) === 1;
        $passwordIsValid = $account && ($isLegacyHash
            ? hash_equals(strtolower($storedPassword), md5($credentials['pass']))
            : Hash::check($credentials['pass'], $storedPassword));

        if (!$passwordIsValid) {
            return back()
                ->withErrors(['user' => 'Username atau password salah.'])
                ->onlyInput('user');
        }

        if ($isLegacyHash && LegacyPassword::canStoreHash()) {
            DB::table('login')
                ->where('id_login', $account->id_login)
                ->update(['pass' => Hash::make($credentials['pass'])]);
        }

        $request->session()->regenerate();
        $request->session()->put('member_id', $account->id_member);

        return redirect()->intended($this->roleUrl($account->role));
    }

    public function showRegister(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('member_id')) {
            return $this->redirectForRole($request);
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'user' => ['required', 'string', 'max:255', Rule::unique('login', 'user')],
            'email' => ['required', 'email', 'max:255'],
            'alamat_member' => ['required', 'string', 'max:1000'],
            'telepon' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'role' => ['required', Rule::in(['merchant', 'customer'])],
            'pass' => ['required', 'string', 'min:6', 'same:confirm_pass'],
            'confirm_pass' => ['required', 'string'],
        ]);

        LegacyPassword::requireHashStorage();

        DB::transaction(function () use ($data): void {
            $memberId = DB::table('member')->insertGetId([
                'nm_member' => $data['name'],
                'company_name' => $data['name'],
                'alamat_member' => $data['alamat_member'],
                'telepon' => $data['telepon'] ?? '',
                'email' => $data['email'],
                'gambar' => '',
                'NIK' => '',
                'role' => $data['role'],
                'description' => $data['role'] === 'merchant' ? ($data['description'] ?? '') : null,
            ], 'id_member');

            DB::table('login')->insert([
                'user' => $data['user'],
                'pass' => Hash::make($data['pass']),
                'id_member' => $memberId,
            ]);

            if ($data['role'] === 'merchant') {
                app(DraftCateringMenus::class)->seedForMerchant($memberId);
            }
        });

        return redirect()->route('login')->with('status', 'Pendaftaran berhasil. Silakan masuk dengan akun baru Anda.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah keluar.');
    }

    private function redirectForRole(Request $request): RedirectResponse
    {
        $role = DB::table('member')
            ->where('id_member', $request->session()->get('member_id'))
            ->value('role');

        return redirect($this->roleUrl($role));
    }

    private function roleUrl(?string $role): string
    {
        return $role === 'customer'
            ? route('customer.dashboard')
            : route('merchant.dashboard');
    }
}
