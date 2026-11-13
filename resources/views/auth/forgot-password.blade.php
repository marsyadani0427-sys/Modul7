
@extends('layouts.perpustakaan')

@section('title', 'Lupa Password')

@section('content')

<div class="auth-container">

    <div class="auth-card">

        <div class="auth-heading">

            <h1>Lupa Password?</h1>

            <p>
                Masukkan alamat email yang terdaftar
                di Perpustakaan Kita.

                Kami akan mengirimkan tautan untuk
                mengatur ulang password akun Anda.
            </p>

        </div>

        {{-- PESAN BERHASIL --}}
        @if (session('status'))
            <div class="auth-success">
                {{ session('status') }}
            </div>
        @endif

        {{-- FORM LUPA PASSWORD --}}
        <form
            action="{{ route('password.email') }}"
            method="POST"
        >

            @csrf

            {{-- EMAIL --}}
            <div class="auth-group">

                <label for="email">
                    Alamat Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Masukkan email terdaftar"
                    autocomplete="email"
                    required
                    autofocus
                >

                @error('email')
                    <div class="auth-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            {{-- TOMBOL KIRIM --}}
            <button
                type="submit"
                class="auth-button"
            >
                Kirim Link Reset Password
            </button>

        </form>

        {{-- KEMBALI KE LOGIN --}}
        <div class="auth-footer">

            <p>
                Sudah ingat password?

                <a href="{{ route('login') }}">
                    Kembali ke Login
                </a>
            </p>

        </div>

    </div>

</div>

@endsection
