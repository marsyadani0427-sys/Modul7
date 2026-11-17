<?php

namespace App\Http\Controllers;

// use App\Models\Buku;
// use Illuminate\Contracts\View\View;
use App\Models\Buku;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BukuController extends Controller
{
    private function dataBuku(): array
    {
        return Buku::with('kategori')
            ->orderBy('id')
            ->get()
            ->map(fn ($buku) => [
                'id'           => $buku->id,
                'judul'        => $buku->judul,
                'penulis'      => $buku->penulis,
                'tahun_terbit' => $buku->tahun_terbit,
                'kategori'     => $buku->kategori->nama,
            ])
            ->all();
    }

    public function index(): View
    {
        $daftarBuku = $this->dataBuku();

        return view('buku.index', compact('daftarBuku'));
    }

    public function show(int $id): View
    {
        $buku = collect($this->dataBuku())->firstWhere('id', $id);

        return view('buku.show')->with('buku', $buku);
    }

    // PENGUJIAN SQL INJECTION A2
public function cari(Request $request): JsonResponse
{
    $judul = $request->query('judul', '');

    // Menggunakan parameter binding untuk mencegah SQL Injection
    $hasil = DB::select(
        'SELECT * FROM bukus WHERE judul = ?',
        [$judul]
    );

    return response()->json($hasil);
}
}