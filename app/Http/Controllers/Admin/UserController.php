<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // Menampilkan daftar pengguna
    public function index()
    {
        $users = User::with('roles')
            ->orderBy('name')
            ->paginate(10);

        $roles = Role::where('guard_name', 'web')
            ->orderBy('name')
            ->pluck('name');

        return view('admin.users.index', compact(
            'users',
            'roles'
        ));
    }

    // Menampilkan formulir edit pengguna
    public function edit(User $user)
    {
        $user->load('anggota');

        return view('admin.users.edit', compact('user'));
    }

    // Memperbarui data akun pengguna
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255'
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'no_telepon' => [
                'required', 'string', 'max:20'
            ],
            'password' => [
                'nullable',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        DB::transaction(function () use ($user, $data) {

            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            // Password hanya diganti jika diisi
            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $user->update($userData);

            // Sinkronisasi data anggota
            $user->anggota()->updateOrCreate(
                [],
                [
                    'nama' => $data['name'],
                    'email' => $data['email'],
                    'no_telepon' => $data['no_telepon'],
                ]
            );
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    
    // Menampilkan form tambah pengguna
    public function create()
    {
        $roles = ['admin', 'petugas', 'siswa'];

        return view('admin.users.create', compact('roles'));
    }

    // Menyimpan akun pengguna baru
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'no_telepon' => [
                'required',
                'string',
                'max:20',
            ],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
            'role' => [
                'required',
                Rule::in(['admin', 'petugas', 'siswa']),
            ],
        ]);

        DB::transaction(function () use ($data) {

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            // Role ditentukan oleh Admin
            $user->assignRole($data['role']);

            // Menghubungkan akun dengan data anggota
            $user->anggota()->create([
                'nama' => $data['name'],
                'email' => $data['email'],
                'no_telepon' => $data['no_telepon'],
            ]);

        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }


    // Mengubah role pengguna
    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')
                    ->where('guard_name', 'web'),
            ],
        ]);

        // Mencegah Admin menurunkan role akunnya sendiri
        if (
            $request->user()->is($user) &&
            $validated['role'] !== 'admin'
        ) {
            return back()->withErrors([
                'role' => 'Anda tidak dapat mengubah role admin akun sendiri.',
            ]);
        }

        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Role pengguna berhasil diperbarui.');
    }
    
    // Menghapus akun pengguna
    public function destroy(Request $request, User $user)
    {
        // Admin tidak boleh menghapus akunnya sendiri
        if ($request->user()->is($user)) {
            return back()->withErrors([
                'delete' => 'Anda tidak dapat menghapus akun sendiri.',
            ]);
        }

        // Admin terakhir tidak boleh dihapus
        if (
            $user->hasRole('admin') &&
            User::role('admin')->count() <= 1
        ) {
            return back()->withErrors([
                'delete' => 'Admin terakhir tidak boleh dihapus.',
            ]);
        }

        $anggota = $user->anggota;

        // Lindungi seluruh riwayat peminjaman
        if ($anggota && $anggota->peminjamans()->exists()) {
            return back()->withErrors([
                'delete' => 'Pengguna memiliki riwayat peminjaman dan tidak dapat dihapus.',
            ]);
        }

        DB::transaction(function () use ($user, $anggota) {

            // Lepaskan role dan permission pengguna
            $user->syncRoles([]);
            $user->syncPermissions([]);

            // Hapus data anggota yang tidak memiliki peminjaman
            if ($anggota) {
                $anggota->delete();
            }

            // Hapus akun pengguna
            $user->delete();
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

}
