
@extends('layouts.perpustakaan')

@section('title', 'Laporan Perpustakaan')

@section('content')

<h1>Laporan Perpustakaan</h1>

<p class="subjudul">
    Rekap data buku dan peminjaman perpustakaan.
</p>

<h2>Ringkasan Perpustakaan</h2>

<div style="display: flex; flex-wrap: wrap; gap: 15px; margin: 20px 0;">

    <div class="kartu-buku">
        <strong>Total Buku</strong>
        <h2>{{ $jumlahBuku }}</h2>
    </div>

    <div class="kartu-buku">
        <strong>Total Peminjaman</strong>
        <h2>{{ $jumlahPeminjaman }}</h2>
    </div>

    <div class="kartu-buku">
        <strong>Menunggu Persetujuan</strong>
        <h2>{{ $jumlahMenunggu }}</h2>
    </div>

    <div class="kartu-buku">
        <strong>Sudah Dikembalikan</strong>
        <h2>{{ $jumlahDikembalikan }}</h2>
    </div>

</div>

<h2>Rekap Buku per Kategori</h2>

<div style="overflow-x: auto; margin: 20px 0 30px;">
    <table border="1" cellpadding="10"
           cellspacing="0"
           style="width: 100%; border-collapse: collapse;">

        <thead>
            <tr>
                <th>No</th>
                <th>Kategori</th>
                <th>Jumlah Buku</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($bukuPerKategori as $kategori)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $kategori->nama }}</td>
                    <td>{{ $kategori->jumlah_buku }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">
                        Belum ada data kategori.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>
</div>

<h2>Rekap Peminjaman per Buku</h2>

<div style="overflow-x: auto; margin: 20px 0 30px;">
    <table border="1" cellpadding="10"
           cellspacing="0"
           style="width: 100%; border-collapse: collapse;">

        <thead>
            <tr>
                <th>No</th>
                <th>Judul Buku</th>
                <th>Jumlah Peminjaman</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($peminjamanPerBuku as $buku)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $buku->judul }}</td>
                    <td>{{ $buku->peminjamans_count }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">
                        Belum ada data buku.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>
</div>

@endsection
