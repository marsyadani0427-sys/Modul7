@extends('layouts.perpustakaan')

@section('title', 'Detail Peminjaman')

@section('content')

<div class="container">

    <h2>Detail Peminjaman</h2>

    <p>
        <strong>ID Peminjaman:</strong>
        {{ $peminjaman->id }}
    </p>

    <p>
        <strong>Nama Peminjam:</strong>
        {{ $peminjaman->anggota?->user?->name ?? '-' }}
    </p>

    <p>
        <strong>Judul Buku:</strong>
        {{ $peminjaman->buku?->judul ?? '-' }}
    </p>

    <p>
        <strong>Tanggal Pinjam:</strong>
        {{ $peminjaman->tanggal_pinjam }}
    </p>

    <p>
        <strong>Tanggal Kembali:</strong>
        {{ $peminjaman->tanggal_kembali ?? '-' }}
    </p>

    <p>
        <strong>Status:</strong>
        {{ $peminjaman->status }}
    </p>

    <a href="{{ route('riwayat-peminjaman.index') }}">
        Kembali
    </a>

</div>

@endsection