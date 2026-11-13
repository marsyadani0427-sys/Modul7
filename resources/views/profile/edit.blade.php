
@extends('layouts.perpustakaan')

@section('title', 'Profil Saya')

@section('content')

<style>
    .profil-wrapper {
        max-width: 750px;
        margin: 0 auto;
    }

    .profil-card {
        background: #ffffff;
        border: 1px solid #dcd8cd;
        border-left: 6px solid #b8892b;
        padding: 25px;
        margin-bottom: 25px;
    }

    .profil-card h2 {
        color: #1f4d3a;
        margin-bottom: 10px;
    }

    .profil-card p {
        color: #6b7178;
        margin-bottom: 20px;
    }

    .profil-group {
        margin-bottom: 18px;
    }

    .profil-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 6px;
    }

    .profil-group input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #dcd8cd;
        border-radius: 4px;
        font-size: 15px;
        background: #fff;
    }

    .profil-group input:focus {
        outline: 2px solid #b8892b;
        outline-offset: 1px;
    }

    .profil-error {
        color: #b42318;
        font-size: 14px;
        margin-top: 5px;
    }

    .profil-success {
        background: #e4f3e9;
        color: #17603c;
        padding: 12px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
</style>

<div class="profil-wrapper">

    <h1>Profil Saya</h1>

    <p class="subjudul">
        Kelola informasi akun dan keamanan password Anda.
    </p>

    {{-- INFORMASI PROFIL --}}
    <div class="profil-card">

        <h2>Informasi Profil</h2>

        <p>
            Perbarui nama dan email akun Anda.
        </p>

        @if (session('status') === 'profile-updated')
            <div class="profil-success">
                Profil berhasil diperbarui.
            </div>
        @endif

        <form action="{{ route('profile.update') }}"
              method="POST">

            @csrf
            @method('PATCH')

            <div class="profil-group">
                <label for="name">Nama Lengkap</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                >

                @error('name')
                    <div class="profil-error">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="profil-group">
                <label for="email">Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                >

                @error('email')
                    <div class="profil-error">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button type="submit" class="btn">
                Simpan Perubahan
            </button>

        </form>

    </div>


    {{-- UBAH PASSWORD --}}
    <div class="profil-card">

        <h2>Ubah Password</h2>

        <p>
            Gunakan password yang kuat untuk menjaga keamanan akun.
        </p>

        @if (session('status') === 'password-updated')
            <div class="profil-success">
                Password berhasil diperbarui.
            </div>
        @endif

        <form action="{{ route('password.update') }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="profil-group">
                <label for="current_password">
                    Password Saat Ini
                </label>

                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    autocomplete="current-password"
                    required
                >

                @error('current_password', 'updatePassword')
                    <div class="profil-error">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="profil-group">
                <label for="password">
                    Password Baru
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    required
                >

                @error('password', 'updatePassword')
                    <div class="profil-error">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="profil-group">
                <label for="password_confirmation">
                    Konfirmasi Password Baru
                </label>

                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    autocomplete="new-password"
                    required
                >

                @error('password_confirmation', 'updatePassword')
                    <div class="profil-error">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button type="submit" class="btn">
                Ubah Password
            </button>

        </form>

    </div>

</div>

@endsection
