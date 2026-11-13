<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan role dan permission pada database test
        $this->seed(RolePermissionSeeder::class);
    }

    // TEST 1: Semua role dapat melihat profil sendiri
    public function test_all_roles_can_view_their_own_profile(): void
    {
        foreach (['admin', 'petugas', 'siswa'] as $role) {

            $user = User::factory()->create();
            $user->assignRole($role);

            $this->actingAs($user)
                ->get('/profile')
                ->assertOk();
        }
    }

    // TEST 2: Siswa dapat memperbarui profil sendiri
    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $user->assignRole('siswa');

        // Membuat data anggota terkait
        $user->anggota()->create([
            'nama' => $user->name,
            'email' => $user->email,
            'no_telepon' => '081234567890',
        ]);

        $response = $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Siswa Test Update',
                'email' => 'siswaupdate@example.com',
            ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'profile-updated');

        // Periksa perubahan pada tabel users
        $user->refresh();

        $this->assertSame(
            'Siswa Test Update',
            $user->name
        );

        $this->assertSame(
            'siswaupdate@example.com',
            $user->email
        );

        // Email berubah, verifikasi harus direset
        $this->assertNull($user->email_verified_at);

        // Data anggota juga harus ikut diperbarui
        $this->assertDatabaseHas('anggotas', [
            'user_id' => $user->id,
            'nama' => 'Siswa Test Update',
            'email' => 'siswaupdate@example.com',
        ]);
    }

    // TEST 3: Verifikasi email tetap jika email tidak berubah
    public function test_email_verification_is_unchanged_when_email_is_unchanged(): void
    {
        $user = User::factory()->create();
        $user->assignRole('siswa');

        $response = $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Nama Baru',
                'email' => $user->email,
            ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull(
            $user->refresh()->email_verified_at
        );
    }

    // TEST 4: Siswa tidak dapat mengubah role sendiri
    public function test_user_cannot_change_role_through_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('siswa');

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Siswa Percobaan',
                'email' => $user->email,
                'role' => 'admin',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertTrue($user->hasRole('siswa'));
        $this->assertFalse($user->hasRole('admin'));
    }

    // TEST 5: Pengguna belum login tidak bisa membuka profil
    public function test_guest_cannot_access_profile(): void
    {
        $this->get('/profile')
            ->assertRedirect(route('login'));

        $this->patch('/profile', [
            'name' => 'Pengguna Tidak Login',
            'email' => 'guest@example.com',
        ])->assertRedirect(route('login'));
    }

    // TEST 6: Pengguna tanpa permission ditolak
    public function test_user_without_permission_cannot_access_profile(): void
    {
        // User tidak diberi role maupun permission
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/profile')
            ->assertForbidden();

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Nama Baru',
                'email' => $user->email,
            ])
            ->assertForbidden();
    }

    // TEST 7: Hapus akun sendiri dinonaktifkan
    public function test_user_cannot_delete_own_account(): void
    {
        $user = User::factory()->create();
        $user->assignRole('siswa');

        $this->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ])
            ->assertStatus(405);

        // Akun tetap tersimpan
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }
}
