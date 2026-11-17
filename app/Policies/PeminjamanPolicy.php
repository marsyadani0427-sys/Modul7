<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Peminjaman;

class PeminjamanPolicy
{
    /**
     * Menentukan siapa yang boleh melihat detail peminjaman.
     */
    public function view(User $user, Peminjaman $peminjaman): bool
    {
        // Admin dan petugas boleh melihat semua peminjaman
        if ($user->hasAnyRole(['admin', 'petugas'])) {
            return true;
        }

        // Siswa hanya boleh melihat peminjaman miliknya sendiri
        if ($user->hasRole('siswa')) {
            return $peminjaman->anggota?->user?->is($user) ?? false;
        }

        return false;
    }
}
