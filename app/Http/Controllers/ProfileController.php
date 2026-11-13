<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MENAMPILKAN PROFIL SENDIRI
    |--------------------------------------------------------------------------
    */

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MEMPERBARUI PROFIL SENDIRI
    |--------------------------------------------------------------------------
    */

    public function update(
        ProfileUpdateRequest $request
    ): RedirectResponse {

        // Mengambil pengguna yang sedang login
        $user = $request->user();

        // Mengambil data yang sudah divalidasi
        $data = $request->validated();

        DB::transaction(function () use ($user, $data) {

            // Memperbarui data akun
            $user->fill($data);

            // Reset verifikasi jika email berubah
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            // Sinkronisasi data anggota milik akun ini
            $user->anggota()->update([
                'nama' => $user->name,
                'email' => $user->email,
            ]);

        });

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated');
    }
}
