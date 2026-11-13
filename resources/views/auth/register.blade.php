
@extends('layouts.perpustakaan')

@section('title', 'Registrasi')

@section('content')

<div class="auth-container">

    <div class="auth-card">

        <div class="auth-heading">

            <h1>Registrasi Akun</h1>

            <p>
                Buat akun untuk mengakses layanan
                Perpustakaan Kita.
            </p>

        </div>

        {{-- FORM REGISTRASI --}}
        <form
            action="{{ route('register') }}"
            method="POST"
        >

            @csrf

            {{-- NAMA LENGKAP --}}
            <div class="auth-group">

                <label for="name">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    placeholder="Masukkan nama lengkap"
                    autocomplete="name"
                    required
                    autofocus
                >

                @error('name')
                    <div class="auth-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            {{-- EMAIL --}}
            <div class="auth-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    placeholder="Masukkan email"
                    autocomplete="username"
                    required
                >

                @error('email')
                    <div class="auth-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            {{-- NOMOR TELEPON --}}
            <div class="auth-group">

                <label for="no_telepon">
                    Nomor Telepon
                </label>

                <input
                    type="tel"
                    name="no_telepon"
                    id="no_telepon"
                    value="{{ old('no_telepon') }}"
                    placeholder="Masukkan nomor telepon"
                    autocomplete="tel"
                    required
                >

                @error('no_telepon')
                    <div class="auth-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            {{-- PASSWORD --}}
            <div class="auth-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Buat password"
                    autocomplete="new-password"
                    required
                >

                @error('password')
                    <div class="auth-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            {{-- KONFIRMASI PASSWORD --}}
            <div class="auth-group">

                <label for="password_confirmation">
                    Konfirmasi Password
                </label>

                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    placeholder="Ulangi password"
                    autocomplete="new-password"
                    required
                >

                @error('password_confirmation')
                    <div class="auth-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            {{-- INFORMASI ROLE --}}
            <div class="auth-info">
                Akun yang didaftarkan akan otomatis
                mendapatkan role Siswa.
            </div>

            {{-- TOMBOL REGISTRASI --}}
            <button type="submit" class="auth-button">
                Daftar Sekarang
            </button>

        </form>

        {{-- LINK LOGIN --}}
        <div class="auth-footer">

            <p>
                Sudah memiliki akun?

                <a href="{{ route('login') }}">
                    Login di sini
                </a>
            </p>

        </div>

    </div>

</div>

@endsection
