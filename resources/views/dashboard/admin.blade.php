
@extends('layouts.perpustakaan')

@section('title', 'Dashboard Admin')

@section('content')

<h1>Dashboard Admin</h1>

<p class="subjudul">
    Selamat datang, {{ auth()->user()->name }}.
    Pilih menu untuk mengelola perpustakaan.
</p>

<div class="grid-buku">

    @can('kelola data buku')
        <div class="kartu-buku">
            <h2 class="kartu-judul">
                Kelola Buku
            </h2>

            <p class="kartu-meta">
                Tambah, ubah, hapus, dan lihat
                koleksi buku perpustakaan.
            </p>

            <a href="{{ route('buku.index') }}"
               class="btn">
                Kelola Buku
            </a>
        </div>
    @endcan

    @can('kelola data pengguna')
        <div class="kartu-buku">
            <h2 class="kartu-judul">
                Kelola Pengguna
            </h2>

            <p class="kartu-meta">
                Mengelola akun pengguna
                dan mengatur role.
            </p>

            <a href="{{ route('admin.users.index') }}"
               class="btn">
                Kelola Pengguna
            </a>
        </div>
    @endcan

    @can('memproses persetujuan peminjaman')
        <div class="kartu-buku">
            <h2 class="kartu-judul">
                Peminjaman & Pengembalian
            </h2>

            <p class="kartu-meta">
                Memeriksa pengajuan, menyetujui
                peminjaman, dan mencatat
                pengembalian buku.
            </p>

            <a href="{{ route('peminjaman.persetujuan.index') }}"
               class="btn">
                Kelola Peminjaman
            </a>
        </div>
    @endcan

    @can('lihat laporan perpustakaan')
        <div class="kartu-buku">
            <h2 class="kartu-judul">
                Laporan Perpustakaan
            </h2>

            <p class="kartu-meta">
                Melihat statistik buku,
                peminjaman, dan pengembalian.
            </p>

            <a href="{{ route('laporan.index') }}"
               class="btn">
                Lihat Laporan
            </a>
        </div>
    @endcan

</div>

@endsection
