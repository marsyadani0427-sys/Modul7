<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BukuResource;
use App\Models\Buku;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    public function index(Request $request)
    {
        $buku = Buku::with('kategori')
            ->withCount('peminjamans')
            ->when($request->filled('cari'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('judul', 'like', '%' . $request->cari . '%')
                      ->orWhere('penulis', 'like', '%' . $request->cari . '%');
                });
            })
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori_id', $request->kategori))
            ->when($request->filled('tahun'), fn ($q) => $q->where('tahun_terbit', $request->tahun))
            ->latest()
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return BukuResource::collection($buku);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(
            $this->aturan(),
            $this->pesan(),
            $this->atribut()
        );

        $buku = Buku::create($validated);

        return response()->json([
            'message' => 'Buku berhasil ditambahkan',
            'data'    => $buku->load('kategori'),
        ], 201);
    }


    public function show(string $id): JsonResponse
    {
        $buku = Buku::with('kategori')->find($id);

        if (! $buku) {
            return response()->json([
                'message' => 'Buku tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'data' => $buku
        ], 200);
    }


    public function update(Request $request, Buku $buku): JsonResponse
    {
        $validated = $request->validate(
            $this->aturan(sebagian: true),
            $this->pesan(),
            $this->atribut()
        );

        $buku->update($validated);

        return response()->json([
            'message' => 'Buku berhasil diperbarui',
            'data'    => $buku->load('kategori'),
        ], 200);
    }


        public function destroy(Buku $buku): JsonResponse
        {
            // Periksa apakah buku memiliki riwayat peminjaman
            if ($buku->peminjamans()->exists()) {
                return response()->json([
                    'message' => 'Buku tidak dapat dihapus karena memiliki riwayat peminjaman.'
                ], 409);
            }

            // Hanya hapus buku yang tidak memiliki riwayat peminjaman
            $buku->delete();

            return response()->json([
                'message' => 'Buku berhasil dihapus.'
            ], 200);
        }


    private function aturan(bool $sebagian = false): array
    {
        $aturan = [
            'kategori_id'  => ['required', 'exists:kategoris,id'],
            'judul'        => ['required', 'string', 'min:3', 'max:150'],
            'penulis'      => ['required', 'string', 'min:3', 'max:100'],
            'tahun_terbit' => ['required', 'integer', 'between:1900,' . now()->year],
        ];

        if ($sebagian) {
            foreach ($aturan as $kolom => $rule) {
                $aturan[$kolom] = array_merge(['sometimes'], $rule);
            }
        }

        return $aturan;
    }

    private function pesan(): array
    {
        return [
            'required'             => ':Attribute wajib diisi.',
            'string'               => ':Attribute harus berupa teks.',
            'integer'              => ':Attribute harus berupa angka bulat.',
            'kategori_id.exists'   => 'Kategori yang dipilih tidak valid.',
            'judul.string'         => 'Judul buku harus berupa teks.',
            'judul.min'            => 'Judul buku minimal :min karakter.',
            'judul.max'            => 'Judul buku maksimal :max karakter.',
            'penulis.min'          => 'Nama penulis minimal :min karakter.',
            'penulis.max'          => 'Nama penulis maksimal :max karakter.',
            'tahun_terbit.between' => 'Tahun terbit harus antara :min sampai :max.',
        ];
    }

    private function atribut(): array
    {
        return [
            'kategori_id'  => 'kategori',
            'tahun_terbit' => 'tahun terbit',
        ];
    }
}