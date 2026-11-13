<?php

use App\Http\Controllers\Api\BukuController;
use App\Http\Controllers\Api\RekapController;
use Illuminate\Support\Facades\Route;

// Semua role yang memiliki permission dapat melihat buku
Route::middleware([
    'auth:sanctum',
    'permission:lihat daftar buku',
])->group(function () {
    Route::get('/buku', [BukuController::class, 'index'])
        ->name('api.buku.index');

    Route::get('/buku/{buku}', [BukuController::class, 'show'])
        ->name('api.buku.show');
});

// Hanya Admin dan Petugas yang dapat mengelola buku
Route::middleware([
    'auth:sanctum',
    'permission:kelola data buku',
])->group(function () {
    Route::post('/buku', [BukuController::class, 'store'])
        ->name('api.buku.store');

    Route::put('/buku/{buku}', [BukuController::class, 'update'])
        ->name('api.buku.update');

    Route::patch('/buku/{buku}', [BukuController::class, 'update']);

    Route::delete('/buku/{buku}', [BukuController::class, 'destroy'])
        ->name('api.buku.destroy');
});


 // API Laporan Perpustakaan - Khusus Admin dan Petugas
Route::middleware([
    'auth:sanctum',
    'permission:lihat laporan perpustakaan',
])->group(function () {

    // Rekap jumlah buku berdasarkan kategori
    Route::get('/rekap/buku-per-kategori', [
        RekapController::class,
        'bukuPerKategori'
    ])->name('api.rekap.buku-per-kategori');

    // Rekap jumlah peminjaman berdasarkan buku
    Route::get('/rekap/peminjaman-per-buku', [
        RekapController::class,
        'peminjamanPerBuku'
    ])->name('api.rekap.peminjaman-per-buku');

});
