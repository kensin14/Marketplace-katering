<?php

namespace App\Http\Controllers;

use App\Support\LegacyPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ManageController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $page = $request->input('page');
        $action = $request->input('action');
        abort_unless(in_array($page, ['barang', 'kategori'], true), 404);
        abort_unless(in_array($action, ['create', 'update', 'delete'], true), 400);

        $id = $request->input('id');
        $redirect = redirect()->route('merchant.dashboard', ['page' => $page]);

        if ($action === 'delete') {
            $request->validate(['id' => ['required', 'integer', 'min:1']]);
            $this->delete($request, $page, (int) $id);

            return $redirect->with('status', $this->deletedMessage($page));
        }

        if ($page === 'barang') {
            $this->saveProduct($request, $action, $id);
        } elseif ($page === 'kategori') {
            $this->saveCategory($request, $action, $id);
        }

        return $redirect->with('status', $this->savedMessage($page, $action));
    }

    private function saveProduct(Request $request, string $action, mixed $id): void
    {
        $ignoreId = $action === 'update' && is_scalar($id) ? (int) $id : null;
        $data = $request->validate([
            'id' => [$action === 'update' ? 'required' : 'sometimes', 'integer', 'min:1'],
            'id_barang' => ['required', 'string', 'max:255', Rule::unique('barang', 'id_barang')->ignore($ignoreId, 'id')],
            'id_kategori' => ['required', 'integer', Rule::exists('kategori', 'id_kategori')],
            'nama_barang' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'merk' => ['required', 'string', 'max:255'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'satuan_barang' => ['required', 'string', 'max:255'],
            'stok' => ['required', 'integer', 'min:0'],
            'image' => [$action === 'create' ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $existing = null;
        if ($action === 'update') {
            $existing = DB::table('barang')->where('id', $id)
                ->where('id_member', $request->session()->get('member_id'))->first();
            if (!$existing) {
                throw ValidationException::withMessages(['id' => 'Produk tidak ditemukan.']);
            }
            if ($existing->id_barang !== $data['id_barang']
                && ($this->productHasHistory($existing->id_barang)
                    || DB::table('order_items')->where('product_id', $existing->id)->exists())) {
                throw ValidationException::withMessages([
                    'id_barang' => 'Kode barang tidak dapat diubah karena sudah tercatat pada riwayat transaksi atau pesanan.',
                ]);
            }
        }

        $values = [
            'id_barang' => $data['id_barang'],
            'id_kategori' => $data['id_kategori'],
            'nama_barang' => $data['nama_barang'],
            'description' => $data['description'],
            'merk' => $data['merk'],
            'harga_beli' => $data['harga_beli'],
            'harga_jual' => $data['harga_jual'],
            'satuan_barang' => $data['satuan_barang'],
            'stok' => $data['stok'],
        ];

        $newImagePath = null;
        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('', 'menu_photos');
            if (!is_string($newImagePath) || $newImagePath === '') {
                throw new \RuntimeException('Foto menu gagal disimpan.');
            }
            $values['image_path'] = $newImagePath;
        }

        try {
            if ($action === 'create') {
                $values['tgl_input'] = now()->format('d F Y, H:i');
                $values['id_member'] = $request->session()->get('member_id');
                $values['is_catering_menu'] = true;
                DB::table('barang')->insert($values);
            } else {
                $values['tgl_update'] = now()->format('d F Y, H:i');
                DB::table('barang')->where('id', $id)
                    ->where('id_member', $request->session()->get('member_id'))->update($values);
            }
        } catch (\Throwable $exception) {
            if ($newImagePath !== null) {
                Storage::disk('menu_photos')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath !== null && $existing && $existing->image_path
            && Storage::disk('menu_photos')->exists($existing->image_path)
            && !Storage::disk('menu_photos')->delete($existing->image_path)) {
            Log::warning('Foto menu lama tidak dapat dihapus setelah diganti.', ['image_path' => $existing->image_path]);
        }
    }

    private function saveCategory(Request $request, string $action, mixed $id): void
    {
        $ignoreId = $action === 'update' && is_scalar($id) ? (int) $id : null;
        $data = $request->validate([
            'id' => [$action === 'update' ? 'required' : 'sometimes', 'integer', 'min:1'],
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori', 'nama_kategori')->ignore($ignoreId, 'id_kategori'),
            ],
        ]);

        $values = ['nama_kategori' => $data['nama_kategori']];
        if ($action === 'create') {
            $values['tgl_input'] = now()->format('d F Y, H:i');
            DB::table('kategori')->insert($values);
        } else {
            if (!DB::table('kategori')->where('id_kategori', $id)->exists()) {
                throw ValidationException::withMessages(['id' => 'Kategori tidak ditemukan.']);
            }
            DB::table('kategori')->where('id_kategori', $id)->update($values);
        }
    }

    private function saveMember(Request $request, string $action, mixed $id): void
    {
        $ignoreId = $action === 'update' && is_scalar($id) ? (int) $id : null;
        $data = $request->validate([
            'id' => [$action === 'update' ? 'required' : 'sometimes', 'integer', 'min:1'],
            'nm_member' => ['required', 'string', 'max:255'],
            'user' => ['required', 'string', 'max:255', Rule::unique('login', 'user')->ignore($ignoreId, 'id_member')],
            'email' => ['required', 'email', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:255'],
            'alamat_member' => ['nullable', 'string'],
            'password' => [$action === 'create' ? 'required' : 'nullable', 'string', 'min:6'],
        ]);

        if ($action === 'create' || !empty($data['password'])) {
            LegacyPassword::requireHashStorage();
        }

        if ($action === 'create') {
            DB::transaction(function () use ($data): void {
                $memberId = DB::table('member')->insertGetId([
                    'nm_member' => $data['nm_member'],
                    'alamat_member' => $data['alamat_member'] ?? '',
                    'telepon' => $data['telepon'] ?? '',
                    'email' => $data['email'],
                    'gambar' => '',
                    'NIK' => '',
                ], 'id_member');
                DB::table('login')->insert([
                    'user' => $data['user'],
                    'pass' => Hash::make($data['password']),
                    'id_member' => $memberId,
                ]);
            });

            return;
        }

        $login = DB::table('login')->where('id_member', $id)->first();
        if (!$login || !DB::table('member')->where('id_member', $id)->exists()) {
            throw ValidationException::withMessages(['id' => 'Pengguna atau akun login tidak ditemukan.']);
        }

        DB::transaction(function () use ($data, $id, $login): void {
            DB::table('member')->where('id_member', $id)->update([
                'nm_member' => $data['nm_member'],
                'alamat_member' => $data['alamat_member'] ?? '',
                'telepon' => $data['telepon'] ?? '',
                'email' => $data['email'],
            ]);
            $loginData = ['user' => $data['user']];
            if (!empty($data['password'])) {
                $loginData['pass'] = Hash::make($data['password']);
            }
            DB::table('login')->where('id_login', $login->id_login)->update($loginData);
        });
    }

    private function delete(Request $request, string $page, int $id): void
    {
        if ($page === 'barang') {
            $product = DB::table('barang')->where('id', $id)
                ->where('id_member', $request->session()->get('member_id'))->first();
            if (!$product) {
                throw ValidationException::withMessages(['id' => 'Produk tidak ditemukan.']);
            }
            if ($this->productHasHistory($product->id_barang)) {
                throw ValidationException::withMessages(['id' => 'Produk tidak dapat dihapus karena sudah tercatat pada riwayat transaksi.']);
            }
            if (DB::table('order_items')->where('product_id', $id)->exists()) {
                throw ValidationException::withMessages(['id' => 'Menu tidak dapat dihapus karena sudah masuk ke invoice pesanan.']);
            }
            DB::table('barang')->where('id', $id)
                ->where('id_member', $request->session()->get('member_id'))->delete();
            if ($product->image_path && Storage::disk('menu_photos')->exists($product->image_path)
                && !Storage::disk('menu_photos')->delete($product->image_path)) {
                Log::warning('Foto menu tidak dapat dihapus setelah menu dihapus.', ['image_path' => $product->image_path]);
            }

            return;
        }

        if ($page === 'kategori') {
            if (DB::table('barang')->where('id_kategori', $id)->exists()) {
                throw ValidationException::withMessages(['id' => 'Kategori tidak dapat dihapus selama masih digunakan oleh produk.']);
            }
            if (!DB::table('kategori')->where('id_kategori', $id)->delete()) {
                throw ValidationException::withMessages(['id' => 'Kategori tidak ditemukan.']);
            }

            return;
        }

        if ((int) $request->session()->get('member_id') === $id) {
            throw ValidationException::withMessages(['id' => 'Akun yang sedang digunakan tidak dapat dihapus.']);
        }
        $hasHistory = DB::table('penjualan')->where('id_member', $id)->exists()
            || DB::table('nota')->where('id_member', $id)->exists();
        if ($hasHistory) {
            throw ValidationException::withMessages(['id' => 'Pengguna tidak dapat dihapus karena sudah tercatat pada riwayat transaksi.']);
        }

        DB::transaction(function () use ($id): void {
            DB::table('login')->where('id_member', $id)->delete();
            if (!DB::table('member')->where('id_member', $id)->delete()) {
                throw ValidationException::withMessages(['id' => 'Pengguna tidak ditemukan.']);
            }
        });
    }

    private function productHasHistory(string $productCode): bool
    {
        return DB::table('penjualan')->where('id_barang', $productCode)->exists()
            || DB::table('nota')->where('id_barang', $productCode)->exists();
    }

    private function savedMessage(string $page, string $action): string
    {
        $subject = ['barang' => 'Produk', 'kategori' => 'Kategori'][$page];

        return $subject . ' berhasil ' . ($action === 'create' ? 'ditambahkan.' : 'diperbarui.');
    }

    private function deletedMessage(string $page): string
    {
        return ['barang' => 'Produk', 'kategori' => 'Kategori'][$page] . ' berhasil dihapus.';
    }
}
