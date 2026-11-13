
@extends('layouts.perpustakaan')

@section('title', 'Riwayat Peminjaman')

@section('content')
<div class="container">

    <h1>Riwayat Peminjaman Saya</h1>

    <p>
        Berikut adalah riwayat peminjaman buku milik Anda.
    </p>

    @if($peminjamans->count() > 0)

        <table border="1" cellpadding="10"
               cellspacing="0" width="100%">

            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach($peminjamans as $index => $peminjaman)
                    <tr>
                        <td>
                            {{ $peminjamans->firstItem() + $index }}
                        </td>

                        <td>
                            {{ $peminjaman->buku?->judul ?? 'Buku tidak ditemukan' }}
                        </td>

                        <td>
                            {{ $peminjaman->tanggal_pinjam }}
                        </td>

                        <td>
                            {{ $peminjaman->tanggal_kembali ?? '-' }}
                        </td>

                       <td>
                    @switch($peminjaman->status)

                        @case('menunggu')
                            <span style="color: #d97706; font-weight: bold;">
                                Menunggu Persetujuan
                            </span>
                            @break

                        @case('disetujui')
                            <span style="color: #2563eb; font-weight: bold;">
                                Sedang Dipinjam
                            </span>
                            @break

                        @case('ditolak')
                            <span style="color: #dc2626; font-weight: bold;">
                                Ditolak
                            </span>
                            @break

                        @case('dikembalikan')
                            <span style="color: #059669; font-weight: bold;">
                                Dikembalikan
                            </span>
                            @break

                        @default
                            <span>Belum Dikembalikan</span>

                    @endswitch
                </td>
                    </tr>
                @endforeach
            </tbody>

        </table>

        <div style="margin-top: 20px;">
            {{ $peminjamans->links() }}
        </div>

    @else

        <p>
            Anda belum memiliki riwayat peminjaman buku.
        </p>
        @if(session('success'))
            <div style="
                padding: 12px;
                margin-bottom: 15px;
                background: #d1fae5;
                color: #065f46;
                border-radius: 5px;
            ">
                {{ session('success') }}
            </div>
        @endif
    @endif

</div>
@endsection
