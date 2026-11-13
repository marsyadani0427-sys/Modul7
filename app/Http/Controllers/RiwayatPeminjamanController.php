<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RiwayatPeminjamanController extends Controller
{
    public function index()
    {
        // Mengambil anggota berdasarkan akun yang sedang login
        $anggota = auth()->user()->anggota;

        // Memastikan akun sudah terhubung dengan data anggota
        if (!$anggota) {
            abort(403, 'Akun belum terhubung dengan data anggota.');
        }

        // Mengambil hanya riwayat peminjaman milik anggota tersebut
        $peminjamans = $anggota->peminjamans()
            ->with('buku')
            ->orderByDesc('tanggal_pinjam')
            ->paginate(10);

        return view(
            'riwayat-peminjaman.index',
            compact('peminjamans')
        );
    }
}
