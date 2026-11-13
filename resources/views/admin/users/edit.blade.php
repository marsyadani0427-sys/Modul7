
@extends('layouts.perpustakaan')

@section('title', 'Edit Pengguna')

@section('content')
<div class="container">

    <h1>Edit Pengguna</h1>
    <p>Perbarui data akun pengguna perpustakaan.</p>

    @if ($errors->any())
        <div style="color: red; margin-bottom: 20px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('admin.users.update', $user) }}"
        method="POST"
    >
        @csrf
        @method('PATCH')

        <div style="margin-bottom: 15px;">
            <label for="name">Nama Lengkap</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $user->name) }}"
                required
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                required
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="no_telepon">Nomor Telepon</label>
            <input
                type="text"
                id="no_telepon"
                name="no_telepon"
                value="{{ old('no_telepon', $user->anggota?->no_telepon) }}"
                required
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="password">Password Baru</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="new-password"
            >
            <p>Kosongkan jika tidak ingin mengganti password.</p>
        </div>

        <div style="margin-bottom: 20px;">
            <label for="password_confirmation">
                Konfirmasi Password Baru
            </label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                autocomplete="new-password"
            >
        </div>

        <button type="submit" class="btn">
            Simpan Perubahan
        </button>

        <a href="{{ route('admin.users.index') }}">
            Batal
        </a>

    </form>

</div>
@endsection
