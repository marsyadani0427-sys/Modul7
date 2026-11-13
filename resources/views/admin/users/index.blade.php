@extends('layouts.perpustakaan')

@section('title', 'Kelola Pengguna')

@section('content')
<div class="container">

    <h1>Kelola Pengguna</h1>
    <p>Halaman pengelolaan akun dan role pengguna.</p>
    <div style="margin: 20px 0;">
        <a href="{{ route('admin.users.create') }}"
        class="btn">
            + Tambah Pengguna
        </a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role Saat Ini</th>
                <th>Ubah Role</th>
                <th>Edit Akun</th>
                <th>Hapus Akun</th>
            </tr>
        </thead>

        <tbody>
            @forelse($users as $index => $user)
                <tr>
                    <td>{{ $users->firstItem() + $index }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>

                    <td>
                        {{ $user->roles->pluck('name')->join(', ') }}
                    </td>

                    <td>
                        <form action="{{ route('admin.users.update-role', $user) }}"
                              method="POST">
                            @csrf
                            @method('PATCH')

                            <select name="role" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}"
                                        @selected($user->hasRole($role))>
                                        {{ ucfirst($role) }}
                                    </option>
                                @endforeach
                            </select>

                            
                            <button type="submit">Simpan</button>
                        </form>
                    </td>

            {{-- Kolom Edit Akun --}}
            <td>
                <a href="{{ route('admin.users.edit', $user) }}">
                    Edit
                </a>
            </td>

            {{-- Kolom Hapus Akun --}}
            <td>
                @if(auth()->id() !== $user->id)
                    <form
                        action="{{ route('admin.users.destroy', $user) }}"
                        method="POST"
                        onsubmit="return confirm('Yakin ingin menghapus akun pengguna ini?');"
                    >
                        @csrf
                        @method('DELETE')

                        <button type="submit">
                            Hapus
                        </button>
                    </form>
                @else
                    <span>-</span>
                @endif
            </td>

                </tr>
            @empty

                <tr>
                    <td colspan="7">Belum ada pengguna.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $users->links() }}
    </div>

</div>
@endsection
