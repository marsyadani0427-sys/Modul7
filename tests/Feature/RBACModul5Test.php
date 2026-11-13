<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RBACModul5Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Menyiapkan 3 role dan 16 permission
        $this->seed(RolePermissionSeeder::class);
    }

    // Membuat akun dengan role dan data anggota
    private function buatAkun(string $role): User
    {
        $user = User::factory()->create();

        $user->assignRole($role);

        $user->anggota()->create([
            'nama' => $user->name,
            'email' => $user->email,
            'no_telepon' => '081234567890',
        ]);

        return $user;
    }

    // Membuat buku khusus database pengujian
    private function buatBuku(string $judul): Buku
    {
        $kategori = Kategori::firstOrCreate([
            'nama' => 'Kategori Pengujian',
        ]);

        return Buku::create([
            'kategori_id' => $kategori->id,
            'judul' => $judul,
            'penulis' => 'Penulis Pengujian',
            'tahun_terbit' => 2024,
        ]);
    }

    // Membuat transaksi peminjaman pengujian
    private function buatPeminjaman(
        User $user,
        Buku $buku,
        string $status = 'menunggu'
    ): Peminjaman {
        return Peminjaman::create([
            'anggota_id' => $user->anggota->id,
            'buku_id' => $buku->id,
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_kembali' => null,
            'status' => $status,
        ]);
    }

    // TEST 1: Guest diarahkan ke login
    public function test_guest_ditolak_dari_halaman_terlindungi(): void
    {
        foreach ([
            '/admin/dashboard',
            '/petugas/dashboard',
            '/buku',
            '/riwayat-peminjaman',
        ] as $url) {
            $this->get($url)
                ->assertRedirect(route('login'));
        }
    }

    // TEST 2: Admin mengakses fitur administrasi
    public function test_admin_dapat_mengakses_fitur_administrasi(): void
    {
        $admin = $this->buatAkun('admin');

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk();

        $this->get('/admin/users')
            ->assertOk();

        $this->get('/laporan')
            ->assertOk();

        $this->get('/petugas/dashboard')
            ->assertForbidden();
    }

    // TEST 3: Petugas mengelola operasional, bukan akun
    public function test_petugas_dapat_mengelola_operasional_tetapi_bukan_akun(): void
    {
        $petugas = $this->buatAkun('petugas');

        $this->actingAs($petugas)
            ->get('/petugas/dashboard')
            ->assertOk();

        $this->get('/peminjaman/masuk')
            ->assertOk();

        $this->get('/laporan')
            ->assertOk();

        $this->get('/admin/dashboard')
            ->assertForbidden();

        $this->get('/admin/users')
            ->assertForbidden();
    }

    // TEST 4: Semua role dapat melihat buku
    public function test_semua_role_dapat_melihat_buku(): void
    {
        $buku = $this->buatBuku('Buku Pengujian RBAC');

        foreach (['admin', 'petugas', 'siswa'] as $role) {
            $user = $this->buatAkun($role);

            $this->actingAs($user)
                ->get('/buku')
                ->assertOk();

            $this->get('/buku/' . $buku->id)
                ->assertOk()
                ->assertSee('Buku Pengujian RBAC');
        }
    }

    // TEST 5: Siswa tidak dapat mengakses administrasi
    public function test_siswa_dilarang_mengakses_fitur_administrasi(): void
    {
        $siswa = $this->buatAkun('siswa');

        foreach ([
            '/admin/dashboard',
            '/petugas/dashboard',
            '/admin/users',
            '/peminjaman/masuk',
            '/laporan',
        ] as $url) {
            $this->actingAs($siswa)
                ->get($url)
                ->assertForbidden();
        }
    }

    // TEST 6: Semua role dapat mengajukan peminjaman
    public function test_semua_role_dapat_mengajukan_peminjaman(): void
    {
        $buku = $this->buatBuku('Buku Untuk Dipinjam');

        foreach (['admin', 'petugas', 'siswa'] as $role) {
            $user = $this->buatAkun($role);

            $this->actingAs($user)
                ->post('/buku/' . $buku->id . '/peminjaman')
                ->assertRedirect(
                    route('riwayat-peminjaman.index')
                );

            $this->assertDatabaseHas('peminjamans', [
                'anggota_id' => $user->anggota->id,
                'buku_id' => $buku->id,
                'status' => 'menunggu',
            ]);
        }
    }

    // TEST 7: Siswa tidak dapat memproses peminjaman
    public function test_siswa_tidak_dapat_memproses_peminjaman(): void
    {
        $siswa = $this->buatAkun('siswa');
        $buku = $this->buatBuku('Buku Persetujuan');
        $peminjaman = $this->buatPeminjaman($siswa, $buku);

        $this->actingAs($siswa)
            ->patch('/peminjaman/' . $peminjaman->id . '/setujui')
            ->assertForbidden();

        $this->patch('/peminjaman/' . $peminjaman->id . '/tolak')
            ->assertForbidden();

        $this->patch('/peminjaman/' . $peminjaman->id . '/kembalikan')
            ->assertForbidden();

        $this->assertSame(
            'menunggu',
            $peminjaman->fresh()->status
        );
    }

    // TEST 8: Petugas dapat menyetujui dan mencatat pengembalian
    public function test_petugas_dapat_menyetujui_dan_mencatat_pengembalian(): void
    {
        $siswa = $this->buatAkun('siswa');
        $petugas = $this->buatAkun('petugas');

        $buku = $this->buatBuku('Buku Operasional');
        $peminjaman = $this->buatPeminjaman($siswa, $buku);

        $this->actingAs($petugas)
            ->patch('/peminjaman/' . $peminjaman->id . '/setujui')
            ->assertRedirect();

        $this->assertSame(
            'disetujui',
            $peminjaman->fresh()->status
        );

        $this->patch(
            '/peminjaman/' . $peminjaman->id . '/kembalikan'
        )->assertRedirect();

        $peminjaman->refresh();

        $this->assertSame(
            'dikembalikan',
            $peminjaman->status
        );

        $this->assertSame(
            now()->toDateString(),
            $peminjaman->tanggal_kembali
        );
    }

    // TEST 9: Riwayat siswa tidak boleh tercampur
    public function test_siswa_hanya_dapat_melihat_riwayat_sendiri(): void
    {
        $siswaSatu = $this->buatAkun('siswa');
        $siswaDua = $this->buatAkun('siswa');

        $bukuSatu = $this->buatBuku('Buku Rahasia Siswa Satu');
        $bukuDua = $this->buatBuku('Buku Rahasia Siswa Dua');

        $this->buatPeminjaman($siswaSatu, $bukuSatu);
        $this->buatPeminjaman($siswaDua, $bukuDua);

        $this->actingAs($siswaSatu)
            ->get('/riwayat-peminjaman')
            ->assertOk()
            ->assertSee('Buku Rahasia Siswa Satu')
            ->assertDontSee('Buku Rahasia Siswa Dua');

        $this->actingAs($siswaDua)
            ->get('/riwayat-peminjaman')
            ->assertOk()
            ->assertSee('Buku Rahasia Siswa Dua')
            ->assertDontSee('Buku Rahasia Siswa Satu');
    }

    // TEST 10: Hanya Admin dapat mengubah role
    public function test_hanya_admin_dapat_mengubah_role_pengguna(): void
    {
        $admin = $this->buatAkun('admin');
        $petugas = $this->buatAkun('petugas');
        $target = $this->buatAkun('siswa');

        $url = '/admin/users/' . $target->id . '/role';

        $this->actingAs($admin)
            ->patch($url, ['role' => 'petugas'])
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue(
            $target->fresh()->hasRole('petugas')
        );

        $this->actingAs($petugas)
            ->patch($url, ['role' => 'admin'])
            ->assertForbidden();

        $this->assertFalse(
            $target->fresh()->hasRole('admin')
        );
    }

    // TEST 11: Pengguna tanpa role tidak memperoleh akses
    public function test_pengguna_tanpa_role_ditolak(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertForbidden();

        $this->get('/buku')
            ->assertForbidden();

        $this->get('/profile')
            ->assertForbidden();
    }

    // TEST 12: Semua role harus login lagi setelah logout
    public function test_semua_role_harus_login_kembali_setelah_logout(): void
    {
        foreach (['admin', 'petugas', 'siswa'] as $role) {
            $user = $this->buatAkun($role);

            $this->actingAs($user)
                ->post('/logout')
                ->assertRedirect('/');

            $this->assertGuest();

            $this->get('/profile')
                ->assertRedirect(route('login'));
        }
    }
}
