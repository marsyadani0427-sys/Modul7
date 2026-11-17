<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PeminjamanController extends Controller
{
    // Menampilkan daftar pengajuan untuk Admin/Petugas
    public function index(): View
    {
        $peminjamans = Peminjaman::with([
            'anggota.user',
            'buku'
        ])
        ->orderByRaw("CASE WHEN status = 'menunggu' THEN 0 ELSE 1 END")
        ->orderByDesc('tanggal_pinjam')
        ->paginate(10);

        return view(
            'peminjaman.persetujuan',
            compact('peminjamans')
        );
    }

// A4: Menampilkan detail peminjaman (rentan IDOR)
// public function show(int $id): View
// {
//     $peminjaman = Peminjaman::with([
//         'anggota.user',
//         'buku'
//     ])->findOrFail($id);

//     return view(
//         'peminjaman.detail',
//         compact('peminjaman')
//     );
// }

// A4: Perlindungan IDOR pada detail peminjaman
// public function show(Request $request, int $id): View
// {
//     $user = $request->user();

//     $query = Peminjaman::with([
//         'anggota.user',
//         'buku'
//     ]);

//     // Admin dan petugas yang memiliki permission
//     // kelola peminjaman boleh melihat semua detail.
//     if (!$user->can('kelola peminjaman')) {

//         // Siswa hanya boleh melihat peminjaman miliknya.
//         if ($user->hasRole('siswa')) {

//             $query->whereHas('anggota.user', function ($q) use ($user) {
//                 $q->where('id', $user->id);
//             });

//         } else {
//             abort(403, 'Anda tidak memiliki akses.');
//         }
//     }

//     $peminjaman = $query->findOrFail($id);

//     return view(
//         'peminjaman.detail',
//         compact('peminjaman')
//     );
// }


// A4: Menampilkan detail peminjaman dengan Policy
public function show(int $id): View
{
    $peminjaman = Peminjaman::with([
        'anggota.user',
        'buku'
    ])->findOrFail($id);

    // Memeriksa hak akses menggunakan PeminjamanPolicy
    Gate::authorize('view', $peminjaman);

    return view(
        'peminjaman.detail',
        compact('peminjaman')
    );
}



    // Pengajuan peminjaman oleh Siswa
    public function store(
        Request $request,
        Buku $buku
    ): RedirectResponse {

        $anggota = $request->user()->anggota;

        if (!$anggota) {
            abort(403, 'Akun belum terhubung dengan data anggota.');
        }

        Peminjaman::create([
            'anggota_id' => $anggota->id,
            'buku_id' => $buku->id,
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_kembali' => null,
            'status' => 'menunggu',
        ]);

        return redirect()
            ->route('riwayat-peminjaman.index')
            ->with(
                'success',
                'Pengajuan peminjaman berhasil dikirim dan menunggu persetujuan.'
            );
    }

    // Menyetujui pengajuan
    public function setujui(
        Peminjaman $peminjaman
    ): RedirectResponse {

        return $this->ubahStatus(
            $peminjaman,
            'disetujui',
            'Pengajuan peminjaman berhasil disetujui.'
        );
    }

    // Menolak pengajuan
    public function tolak(
        Peminjaman $peminjaman
    ): RedirectResponse {

        return $this->ubahStatus(
            $peminjaman,
            'ditolak',
            'Pengajuan peminjaman berhasil ditolak.'
        );
    }

    // Mengubah status hanya jika masih menunggu
    private function ubahStatus(
        Peminjaman $peminjaman,
        string $status,
        string $pesan
    ): RedirectResponse {

        $berhasil = DB::transaction(function () use (
            $peminjaman,
            $status
        ) {

            $transaksi = Peminjaman::query()
                ->lockForUpdate()
                ->findOrFail($peminjaman->id);

            if ($transaksi->status !== 'menunggu') {
                return false;
            }

            $transaksi->status = $status;
            $transaksi->save();

            return true;
        });

        if (!$berhasil) {
            return back()->with(
                'error',
                'Pengajuan ini sudah diproses sebelumnya.'
            );
        }

        return back()->with('success', $pesan);
    }
    
    
// Mencatat pengembalian buku
public function catatPengembalian(
    Peminjaman $peminjaman
): RedirectResponse {

    $berhasil = DB::transaction(function () use ($peminjaman) {

        $transaksi = Peminjaman::query()
            ->lockForUpdate()
            ->findOrFail($peminjaman->id);

        // Hanya peminjaman yang sudah disetujui
        // yang boleh dikembalikan
        if ($transaksi->status !== 'disetujui') {
            return false;
        }

        // Mengubah status dan tanggal pengembalian
        $transaksi->status = 'dikembalikan';
        $transaksi->tanggal_kembali = now()->toDateString();

        $transaksi->save();

        return true;
    });

    if (!$berhasil) {
        return back()->with(
            'error',
            'Pengembalian hanya dapat dilakukan untuk peminjaman yang sudah disetujui.'
        );
    }

    return back()->with(
        'success',
        'Pengembalian buku berhasil dicatat.'
    );
}

}
