<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        // Permission dan role yang diizinkan
        $aturan = [
            'akses dashboard admin' => ['admin'],
            'akses dashboard petugas' => ['petugas'],

            'lihat daftar buku' => ['admin', 'petugas', 'siswa'],
            'kelola data buku' => ['admin', 'petugas'],

            'kelola data pengguna' => ['admin'],
            'kelola role pengguna' => ['admin'],

            'kelola peminjaman' => ['admin', 'petugas'],
            'kelola pengembalian' => ['admin', 'petugas'],

            'mengajukan peminjaman buku' => ['admin', 'petugas', 'siswa'],
            'memproses persetujuan peminjaman' => ['admin', 'petugas'],
            'mencatat pengembalian buku' => ['admin', 'petugas'],

            'melihat status peminjaman sendiri' => ['admin', 'petugas', 'siswa'],
            'melihat riwayat peminjaman sendiri' => ['admin', 'petugas', 'siswa'],

            'lihat laporan perpustakaan' => ['admin', 'petugas'],

            'lihat profil sendiri' => ['admin', 'petugas', 'siswa'],
            'ubah profil sendiri' => ['admin', 'petugas', 'siswa'],
        ];

        // Simpan semua permission
        foreach (array_keys($aturan) as $nama) {
            Permission::firstOrCreate([
                'name' => $nama,
                'guard_name' => 'web',
            ]);
        }

        // Buat role dan tetapkan permission
        foreach (['admin', 'petugas', 'siswa'] as $namaRole) {
            $role = Role::firstOrCreate([
                'name' => $namaRole,
                'guard_name' => 'web',
            ]);

            $izin = array_keys(array_filter(
                $aturan,
                fn ($daftarRole) => in_array(
                    $namaRole,
                    $daftarRole,
                    true
                )
            ));

            $role->syncPermissions($izin);
        }

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
