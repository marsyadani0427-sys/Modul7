
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>
        @yield('title', 'Beranda') | Perpustakaan Kita
    </title>

    @vite('resources/css/app.css')
</head>

<body>

    {{-- HEADER --}}
    <header class="site-header">
        <div class="container">

            <a href="{{ route('beranda') }}"
               class="brand">
                Perpustakaan Kita
            </a>

            <p class="tagline">
                Katalog buku sederhana untuk semua pembaca
            </p>

        </div>
    </header>

    {{-- NAVBAR --}}
    <nav class="navbar" aria-label="Navigasi utama">
        <div class="container">

            <a href="{{ route('beranda') }}"
               class="{{ request()->routeIs('beranda') ? 'active' : '' }}">
                Beranda
            </a>

            @auth

                @can('lihat daftar buku')
                    <a href="{{ route('buku.index') }}"
                       class="{{ request()->routeIs('buku.*') ? 'active' : '' }}">
                        Daftar Buku
                    </a>
                @endcan

                {{-- MENU ADMIN --}}
                @can('akses dashboard admin')
                    <a href="{{ route('dashboard.admin') }}"
                       class="{{ request()->routeIs('dashboard.admin') ? 'active' : '' }}">
                        Dashboard Admin
                    </a>
                @endcan

                {{-- MENU PETUGAS --}}
                @can('akses dashboard petugas')
                    <a href="{{ route('dashboard.petugas') }}"
                       class="{{ request()->routeIs('dashboard.petugas') ? 'active' : '' }}">
                        Dashboard Petugas
                    </a>
                @endcan

                {{-- PEMINJAMAN --}}
                @can('memproses persetujuan peminjaman')
                    <a href="{{ route('peminjaman.persetujuan.index') }}"
                       class="{{ request()->routeIs('peminjaman.persetujuan.*') ? 'active' : '' }}">
                        Persetujuan Peminjaman
                    </a>
                @endcan

                {{-- LAPORAN --}}
                @can('lihat laporan perpustakaan')
                    <a href="{{ route('laporan.index') }}"
                       class="{{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                        Laporan Perpustakaan
                    </a>
                @endcan

                {{-- KELOLA PENGGUNA --}}
                @can('kelola data pengguna')
                    <a href="{{ route('admin.users.index') }}"
                       class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        Kelola Pengguna
                    </a>
                @endcan

                {{-- RIWAYAT SENDIRI --}}
                @can('melihat riwayat peminjaman sendiri')
                    <a href="{{ route('riwayat-peminjaman.index') }}"
                       class="{{ request()->routeIs('riwayat-peminjaman.*') ? 'active' : '' }}">
                        Riwayat Peminjaman Saya
                    </a>
                @endcan

                {{-- PROFIL SENDIRI --}}
                @can('lihat profil sendiri')
                    <a href="{{ route('profile.edit') }}"
                       class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        Profil Saya
                    </a>
                @endcan

                {{-- TOMBOL LOGOUT UNTUK SEMUA ROLE --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="logout-form"
                >
                    @csrf

                    <button
                        type="submit"
                        class="logout-button"
                    >
                        Logout
                    </button>
                </form>

            @else

                <a href="{{ route('login') }}">
                    Login
                </a>

                <a href="{{ route('register') }}">
                    Register
                </a>

            @endauth

        </div>
    </nav>

    {{-- KONTEN --}}
    <main class="container content">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="site-footer">
        <div class="container">
            &copy; {{ date('Y') }} Perpustakaan Kita
        </div>
    </footer>

</body>
</html>
