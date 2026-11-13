
@extends('layouts.perpustakaan')

@section('title', $buku ? $buku['judul'] : 'Buku tidak ditemukan')

@section('content')

    @if (session('success'))
        <div style="padding:12px; margin-bottom:15px; background:#d1fae5; color:#065f46;">
            {{ session('success') }}
        </div>
    @endif

    @if ($buku)
        <article class="detail-buku">

            <span class="badge">
                {{ $buku['kategori'] }}
            </span>

            <h1>{{ $buku['judul'] }}</h1>

            <dl class="detail-list">
                <dt>ID Buku</dt>
                <dd>{{ $buku['id'] }}</dd>

                <dt>Penulis</dt>
                <dd>{{ $buku['penulis'] }}</dd>

                <dt>Tahun terbit</dt>
                <dd>{{ $buku['tahun_terbit'] }}</dd>

                <dt>Kategori</dt>
                <dd>{{ $buku['kategori'] }}</dd>
            </dl>

            @can('mengajukan peminjaman buku')
                <form
                    method="POST"
                    action="{{ route('buku.peminjaman.store', $buku['id']) }}"
                    style="margin:20px 0;"
                    onsubmit="return confirm('Ajukan peminjaman buku ini?')"
                >
                    @csrf

                    <button type="submit">
                        Ajukan Peminjaman
                    </button>
                </form>
            @endcan

            <a href="{{ route('buku.index') }}" class="btn">
                Kembali ke daftar buku
            </a>

        </article>
    @else
        <div class="tidak-ditemukan">
            <h1>Buku tidak ditemukan</h1>

            <p>
                Buku dengan ID tersebut tidak ada di katalog.
                Periksa kembali alamatnya atau pilih buku dari daftar.
            </p>

            <a href="{{ route('buku.index') }}" class="btn">
                Lihat daftar buku
            </a>
        </div>
    @endif

@endsection
