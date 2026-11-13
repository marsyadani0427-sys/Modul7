
@extends('layouts.perpustakaan')

@section('title', 'Persetujuan Peminjaman')

@section('content')

<div class="container">

    <h1>Persetujuan Peminjaman</h1>

    <p>
        Berikut adalah daftar pengajuan peminjaman buku dari anggota perpustakaan.
    </p>

    @if(session('success'))
        <div style="padding:12px; background:#d1fae5; color:#065f46; margin-bottom:15px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="padding:12px; background:#fee2e2; color:#991b1b; margin-bottom:15px;">
            {{ session('error') }}
        </div>
    @endif

    @if($peminjamans->count() > 0)

        <table border="1" cellpadding="10"
               cellspacing="0" width="100%">

            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

           
<tbody>

    @forelse($peminjamans as $index => $peminjaman)

        <tr>
            {{-- Nomor --}}
            <td>
                {{ $peminjamans->firstItem() + $index }}
            </td>

            {{-- Nama Anggota --}}
            <td>
                {{ $peminjaman->anggota?->nama ?? '-' }}
            </td>

            {{-- Judul Buku --}}
            <td>
                {{ $peminjaman->buku?->judul ?? 'Buku tidak ditemukan' }}
            </td>

            {{-- Tanggal Pengajuan --}}
            <td>
                {{ $peminjaman->tanggal_pinjam }}
            </td>

            {{-- Status --}}
            <td>
                @switch($peminjaman->status)

                    @case('menunggu')
                        <strong style="color:#d97706">
                            Menunggu Persetujuan
                        </strong>
                        @break

                    @case('disetujui')
                        <strong style="color:#2563eb">
                            Disetujui
                        </strong>
                        @break

                    @case('ditolak')
                        <strong style="color:#dc2626">
                            Ditolak
                        </strong>
                        @break

                    @case('dikembalikan')
                        <strong style="color:#059669">
                            Dikembalikan
                        </strong>
                        @break

                    @default
                        -
                @endswitch
            </td>

            {{-- Aksi --}}
            <td>

                @if($peminjaman->status === 'menunggu')

                    <form
                        method="POST"
                        action="{{ route('peminjaman.persetujuan.setujui', $peminjaman->id) }}"
                        style="display:inline"
                        onsubmit="return confirm('Setujui pengajuan ini?')"
                    >
                        @csrf
                        @method('PATCH')

                        <button type="submit">
                            Setujui
                        </button>
                    </form>

                    <form
                        method="POST"
                        action="{{ route('peminjaman.persetujuan.tolak', $peminjaman->id) }}"
                        style="display:inline"
                        onsubmit="return confirm('Tolak pengajuan ini?')"
                    >
                        @csrf
                        @method('PATCH')

                        <button type="submit">
                            Tolak
                        </button>
                    </form>

                @elseif($peminjaman->status === 'disetujui')

                    <form
                        method="POST"
                        action="{{ route('peminjaman.pengembalian.catat', $peminjaman->id) }}"
                        style="display:inline"
                        onsubmit="return confirm('Catat pengembalian buku ini?')"
                    >
                        @csrf
                        @method('PATCH')

                        <button type="submit">
                            Catat Pengembalian
                        </button>
                    </form>

                @else

                    -

                @endif

            </td>

        </tr>

    @empty

        <tr>
            <td colspan="6" style="text-align:center">
                Belum ada data peminjaman.
            </td>
        </tr>

    @endforelse

</tbody>


        </table>

        <div style="margin-top:20px">
            {{ $peminjamans->links() }}
        </div>

    @else

        <p>Belum ada pengajuan peminjaman.</p>

    @endif

</div>

@endsection
