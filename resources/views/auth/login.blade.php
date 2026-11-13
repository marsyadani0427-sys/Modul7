
@extends('layouts.perpustakaan')

@section('title', 'Login')

@section('content')

<div class="auth-container">

    <div class="auth-card">

        <div class="auth-heading">
            <h1>Login Perpustakaan</h1>

            <p>
                Masuk menggunakan akun perpustakaan Anda.
            </p>
        </div>

        {{-- Pesan status dari Laravel Breeze --}}
        @if (session('status'))
            <div class="auth-success">
                {{ session('status') }}
            </div>
        @endif

        {{-- FORM LOGIN --}}
        <form action="{{ route('login') }}" method="POST">

            @csrf

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
                    autofocus
                >

                @error('email')
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
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    required
                >

                @error('password')
                    <div class="auth-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            {{-- INGAT SAYA --}}
            <div class="auth-options">

                <label class="auth-remember">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        @checked(old('remember'))
                    >
                    Ingat saya
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif

            </div>

            {{-- TOMBOL LOGIN --}}
            <button type="submit" class="auth-button">
                Masuk
            </button>

        </form>

        {{-- LINK REGISTRASI --}}
        <div class="auth-footer">

            <p>
                Belum memiliki akun?

                <a href="{{ route('register') }}">
                    Daftar sekarang
                </a>
            </p>

        </div>

    </div>

</div>

@endsection
