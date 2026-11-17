<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('admin.seed_email');
        $password = config('admin.seed_password');

        // Memastikan konfigurasi tersedia
        if (
            !is_string($email) ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            throw new RuntimeException(
                'Email admin belum dikonfigurasi dengan benar.'
            );
        }

        if (
            !is_string($password) ||
            strlen($password) < 32
        ) {
            throw new RuntimeException(
                'Password admin belum dikonfigurasi atau terlalu pendek.'
            );
        }

        // Cari akun admin berdasarkan email
        $admin = User::where('email', $email)->first();

        // Jangan mengambil alih akun pengguna non-admin
        if ($admin && !$admin->hasRole('admin')) {
            throw new RuntimeException(
                'Email tersebut sudah digunakan akun non-admin.'
            );
        }

        // Buat akun jika belum ada
        if (!$admin) {
            $admin = new User();

            $admin->forceFill([
                'name' => 'Admin Perpustakaan',
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);

            $admin->save();
        } else {
            // Perbarui password akun admin yang sudah ada
            $admin->forceFill([
                'password' => Hash::make($password),
            ])->save();
        }

        // Pastikan akun memiliki role admin
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command?->info(
            'Password akun Admin berhasil diperbarui.'
        );
    }
}
