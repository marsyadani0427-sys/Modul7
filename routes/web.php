<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\BukuController as ApiBukuController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\RiwayatPeminjamanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Admin\UserController;


Route::get('/peminjaman/{id}', [
    PeminjamanController::class,
    'show'
])
->middleware('auth')
->whereNumber('id')
->name('peminjaman.show');

// HALAMAN UTAMA

Route::get('/', function () {
    return view('beranda');
})->name('beranda');


// DAFTAR DAN DETAIL BUKU

Route::get('/buku', [
    BukuController::class,
    'index'
])
->middleware([
    'auth',
    'permission:lihat daftar buku'
])
->name('buku.index');

Route::get('/buku/{id}', [
    BukuController::class,
    'show'
])
->middleware([
    'auth',
    'permission:lihat daftar buku'
])
->name('buku.show');

// PENGUJIAN CSRF A3

Route::delete('/buku/{buku}', [
    ApiBukuController::class,
    'destroy'
])
->middleware('auth')
->name('buku.hapus.get');

// // PENGUJIAN SQL INJECTION A2

Route::get('/buku-cari', [
    BukuController::class,
    'cari'
])
->middleware('auth')
->name('buku.cari');

// PENGAJUAN PEMINJAMAN BUKU

Route::post('/buku/{buku}/peminjaman', [
    PeminjamanController::class,
    'store'
])
->middleware([
    'auth',
    'permission:mengajukan peminjaman buku'
])
->name('buku.peminjaman.store');


// DASHBOARD UTAMA

Route::get('/dashboard', function () {

    $user = auth()->user();

    if ($user->hasRole('admin')) {
        return redirect()->route('dashboard.admin');
    }

    if ($user->hasRole('petugas')) {
        return redirect()->route('dashboard.petugas');
    }

    if ($user->hasRole('siswa')) {
        return redirect()->route('buku.index');
    }

    abort(403, 'Akun belum memiliki role.');

})
->middleware('auth')
->name('dashboard');


// DASHBOARD ADMIN

Route::get('/admin/dashboard', function () {
    return view('dashboard.admin');
})
->middleware([
    'auth',
    'permission:akses dashboard admin'
])
->name('dashboard.admin');


// DASHBOARD PETUGAS

Route::get('/petugas/dashboard', function () {
    return view('dashboard.petugas');
})
->middleware([
    'auth',
    'permission:akses dashboard petugas'
])
->name('dashboard.petugas');


// RIWAYAT DAN STATUS PEMINJAMAN SENDIRI

Route::get('/riwayat-peminjaman', [
    RiwayatPeminjamanController::class,
    'index'
])
->middleware([
    'auth',
    'permission:melihat riwayat peminjaman sendiri',
    'permission:melihat status peminjaman sendiri'
])
->name('riwayat-peminjaman.index');


// PENGELOLAAN PEMINJAMAN

Route::middleware([
    'auth',
    'permission:kelola peminjaman'
])
->prefix('peminjaman')
->name('peminjaman.')
->group(function () {

    // Menampilkan daftar peminjaman
    Route::get('/masuk', [
        PeminjamanController::class,
        'index'
    ])->name('persetujuan.index');

    // Menyetujui peminjaman
    Route::patch('/{peminjaman}/setujui', [
        PeminjamanController::class,
        'setujui'
    ])
    ->middleware(
        'permission:memproses persetujuan peminjaman'
    )
    ->name('persetujuan.setujui');

    // Menolak peminjaman
    Route::patch('/{peminjaman}/tolak', [
        PeminjamanController::class,
        'tolak'
    ])
    ->middleware(
        'permission:memproses persetujuan peminjaman'
    )
    ->name('persetujuan.tolak');

});


// PENGELOLAAN PENGEMBALIAN

Route::patch('/peminjaman/{peminjaman}/kembalikan', [
    PeminjamanController::class,
    'catatPengembalian'
])
->middleware([
    'auth',
    'permission:kelola pengembalian',
    'permission:mencatat pengembalian buku'
])
->name('peminjaman.pengembalian.catat');


// PENGELOLAAN PENGGUNA - KHUSUS ADMIN

Route::middleware([
    'auth',
    'permission:kelola data pengguna'
])
->prefix('admin')
->name('admin.')
->group(function () {

    // Daftar pengguna
    Route::get('/users', [
        UserController::class,
        'index'
    ])->name('users.index');

    // Form tambah pengguna
    Route::get('/users/create', [
        UserController::class,
        'create'
    ])->name('users.create');

    // Simpan pengguna baru
    Route::post('/users', [
        UserController::class,
        'store'
    ])->name('users.store');

    // Form edit pengguna
    Route::get('/users/{user}/edit', [
        UserController::class,
        'edit'
    ])->name('users.edit');

    // Update pengguna
    Route::patch('/users/{user}', [
        UserController::class,
        'update'
    ])->name('users.update');

    // Update role pengguna
    Route::patch('/users/{user}/role', [
        UserController::class,
        'updateRole'
    ])
    ->middleware('permission:kelola role pengguna')
    ->name('users.update-role');

    // Hapus pengguna
    Route::delete('/users/{user}', [
        UserController::class,
        'destroy'
    ])->name('users.destroy');

});


// LAPORAN PERPUSTAKAAN

Route::get('/laporan', [
    LaporanController::class,
    'index'
])
->middleware([
    'auth',
    'permission:lihat laporan perpustakaan'
])
->name('laporan.index');


// PROFIL PENGGUNA

Route::middleware('auth')->group(function () {

    // Melihat profil sendiri
    Route::get('/profile', [
        ProfileController::class,
        'edit'
    ])
    ->middleware('permission:lihat profil sendiri')
    ->name('profile.edit');

    // Mengubah profil sendiri
    Route::patch('/profile', [
        ProfileController::class,
        'update'
    ])
    ->middleware('permission:ubah profil sendiri')
    ->name('profile.update');

});


// AUTENTIKASI LARAVEL BREEZE

require __DIR__.'/auth.php';
