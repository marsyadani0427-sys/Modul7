
@extends('layouts.perpustakaan')

@section('title', 'Tambah Pengguna')

@section('content')
<div class="container">

    <h1>Tambah Pengguna</h1>
    <p>Tambahkan akun baru dan tentukan role pengguna.</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Gagal menambahkan pengguna.</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}"
          method="POST">

        @csrf

        <div style="margin-bottom: 15px;">
            <label for="name">Nama Lengkap</label>
            <input type="text"
                   id="name"
                   name="name"
                   value="{{ old('name') }}"
                   required>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="email">Email</label>
            <input type="email"
                   id="email"
                   name="email"
                   value="{{ old('email') }}"
                   required>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="no_telepon">Nomor Telepon</label>
            <input type="text"
                   id="no_telepon"
                   name="no_telepon"
                   value="{{ old('no_telepon') }}"
                   required>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="password">Password</label>
            <input type="password"
                   id="password"
                   name="password"
                   required>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="password_confirmation">
                Konfirmasi Password
            </label>

            <input type="password"
                   id="password_confirmation"
                   name="password_confirmation"
                   required>
        </div>

        <div style="margin-bottom: 20px;">
            <label for="role">Role Pengguna</label>

            <select id="role" name="role" required>
                <option value="">-- Pilih Role --</option>

                @foreach ($roles as $role)
                    <option value="{{ $role }}"
                        @selected(old('role') === $role)>
                        {{ ucfirst($role) }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit">
            Simpan Pengguna
        </button>

        <a href="{{ route('admin.users.index') }}">
            Batal
        </a>

    </form>

</div>
@endsection
