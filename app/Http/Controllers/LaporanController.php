<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(): View
    {
        // Total buku perpustakaan
        $jumlahBuku = Buku::count();

        // Total seluruh transaksi peminjaman
        $jumlahPeminjaman = Peminjaman::count();

        // Peminjaman menunggu persetujuan
        $jumlahMenunggu = Peminjaman::where(
            'status',
            'menunggu'
        )->count();

        // Peminjaman yang sudah dikembalikan
        $jumlahDikembalikan = Peminjaman::where(
            'status',
            'dikembalikan'
        )->count();

        // Rekap buku berdasarkan kategori
        $bukuPerKategori = DB::table('kategoris')
            ->leftJoin(
                'bukus',
                'bukus.kategori_id',
                '=',
                'kategoris.id'
            )
            ->select('kategoris.nama')
            ->selectRaw('COUNT(bukus.id) as jumlah_buku')
            ->groupBy(
                'kategoris.id',
                'kategoris.nama'
            )
            ->orderByDesc('jumlah_buku')
            ->get();

        // Rekap peminjaman berdasarkan buku
        $peminjamanPerBuku = Buku::query()
            ->select('id', 'judul')
            ->withCount('peminjamans')
            ->orderByDesc('peminjamans_count')
            ->get();

        return view('laporan.index', compact(
            'jumlahBuku',
            'jumlahPeminjaman',
            'jumlahMenunggu',
            'jumlahDikembalikan',
            'bukuPerKategori',
            'peminjamanPerBuku'
        ));
    }
}
