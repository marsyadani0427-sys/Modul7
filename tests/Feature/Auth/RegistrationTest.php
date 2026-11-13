<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    // Menguji halaman registrasi
    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
    }

    // Menguji registrasi dan role otomatis siswa
    public function test_new_users_can_register(): void
    {
        // Siapkan role dan permission untuk database test
        $this->seed(RolePermissionSeeder::class);

        $response = $this->post('/register', [
            'name' => 'Siswa Pengujian',
            'email' => 'siswatest@example.com',
            'no_telepon' => '081234567890',
            'password' => 'PasswordTest123!',
            'password_confirmation' => 'PasswordTest123!',
        ]);

        // Registrasi berhasil dan pengguna login
        $response->assertSessionHasNoErrors();

        $this->assertAuthenticated();

        $response->assertRedirect(
            route('dashboard', absolute: false)
        );

        // Pastikan pengguna tersimpan
        $user = User::where(
            'email',
            'siswatest@example.com'
        )->firstOrFail();

        // Role otomatis harus siswa
        $this->assertTrue($user->hasRole('siswa'));

        // Password wajib tersimpan menggunakan hash
        $this->assertTrue(
            Hash::check('PasswordTest123!', $user->password)
        );

        $this->assertNotSame(
            'PasswordTest123!',
            $user->password
        );

        // Data anggota harus terhubung dengan pengguna
        $this->assertDatabaseHas('anggotas', [
            'user_id' => $user->id,
            'nama' => 'Siswa Pengujian',
            'email' => 'siswatest@example.com',
            'no_telepon' => '081234567890',
        ]);
    }

    // Pengguna tidak boleh memilih role Admin sendiri
    public function test_registration_cannot_assign_admin_role(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->post('/register', [
            'name' => 'Siswa Biasa',
            'email' => 'siswa2@example.com',
            'no_telepon' => '081234567891',
            'password' => 'PasswordTest123!',
            'password_confirmation' => 'PasswordTest123!',
            'role' => 'admin',
        ])->assertSessionHasNoErrors();

        $user = User::where(
            'email',
            'siswa2@example.com'
        )->firstOrFail();

        // Meski mengirim role admin, tetap menjadi siswa
        $this->assertTrue($user->hasRole('siswa'));

        $this->assertFalse($user->hasRole('admin'));
    }
}
