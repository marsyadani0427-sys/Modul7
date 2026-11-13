
@extends('layouts.perpustakaan')

@section('title', 'Reset Password')

@section('content')

<div class="auth-container">

    <div class="auth-card">

        <div class="auth-heading">

            <h1>Reset Password</h1>

            <p>
                Buat password baru untuk akun
                Perpustakaan Kita Anda.

                Gunakan password yang kuat
                untuk menjaga keamanan akun.
            </p>

        </div>

        {{-- PESAN STATUS --}}
        @if (session('status'))
            <div class="auth-success">
                {{ session('status') }}
            </div>
        @endif

        {{-- FORM RESET PASSWORD --}}
        <form
            action="{{ route('password.store') }}"
            method="POST"
        >

            @csrf

            {{-- TOKEN RESET PASSWORD --}}
            <input
                type="hidden"
                name="token"
                value="{{ $request->route('token') }}"
            >

            {{-- EMAIL --}}
            <div class="auth-group">

                <label for="email">
                    Alamat Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    placeholder="Masukkan email akun"
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

            {{-- PASSWORD BARU --}}
            <div class="auth-group">

                <label for="password">
                    Password Baru
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password baru"
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
                    Konfirmasi Password Baru
                </label>

                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Ulangi password baru"
                    autocomplete="new-password"
                    required
                >

                @error('password_confirmation')
                    <div class="auth-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            {{-- TOMBOL RESET --}}
            <button
                type="submit"
                class="auth-button"
            >
                Simpan Password Baru
            </button>

        </form>

        {{-- KEMBALI KE LOGIN --}}
        <div class="auth-footer">

            <p>
                Ingin kembali ke halaman Login?

                <a href="{{ route('login') }}">
                    Kembali ke Login
                </a>
            </p>

        </div>

    </div>

</div>

@endsection
