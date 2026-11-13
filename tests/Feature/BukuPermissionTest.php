<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BukuPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function buatPengguna(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function buatKategori(): Kategori
    {
        return Kategori::create([
            'nama' => 'Kategori Pengujian',
        ]);
    }

    private function dataBuku(int $kategoriId): array
    {
        return [
            'kategori_id' => $kategoriId,
            'judul' => 'Buku Praktikum Modul 5',
            'penulis' => 'Penulis Pengujian',
            'tahun_terbit' => 2024,
        ];
    }

    // TEST 1: Pengunjung yang belum login ditolak
    public function test_guest_tidak_dapat_mengakses_api_buku(): void
    {
        $this->getJson('/api/buku')
            ->assertUnauthorized();

        $this->postJson('/api/buku', [])
            ->assertUnauthorized();
    }

    // TEST 2: Admin dapat menambah, mengubah, dan menghapus buku
    public function test_admin_dapat_crud_buku(): void
    {
        $admin = $this->buatPengguna('admin');
        $kategori = $this->buatKategori();

        $this->actingAs($admin);

        // CREATE
        $response = $this->postJson(
            '/api/buku',
            $this->dataBuku($kategori->id)
        );

        $response->assertCreated()
            ->assertJsonPath(
                'data.judul',
                'Buku Praktikum Modul 5'
            );

        $id = $response->json('data.id');

        $this->assertDatabaseHas('bukus', [
            'id' => $id,
            'judul' => 'Buku Praktikum Modul 5',
        ]);

        // READ
        $this->getJson("/api/buku/{$id}")
            ->assertOk();

        // UPDATE
        $this->putJson("/api/buku/{$id}", [
            'judul' => 'Buku Modul 5 Diperbarui',
        ])
            ->assertOk()
            ->assertJsonPath(
                'data.judul',
                'Buku Modul 5 Diperbarui'
            );

        // DELETE
        $this->deleteJson("/api/buku/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('bukus', [
            'id' => $id,
        ]);
    }

    // TEST 3: Petugas juga dapat CRUD buku
    public function test_petugas_dapat_crud_buku(): void
    {
        $petugas = $this->buatPengguna('petugas');
        $kategori = $this->buatKategori();

        $this->actingAs($petugas);

        $response = $this->postJson(
            '/api/buku',
            $this->dataBuku($kategori->id)
        );

        $response->assertCreated();

        $id = $response->json('data.id');

        $this->patchJson("/api/buku/{$id}", [
            'penulis' => 'Penulis Baru',
        ])
            ->assertOk()
            ->assertJsonPath(
                'data.penulis',
                'Penulis Baru'
            );

        $this->deleteJson("/api/buku/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('bukus', [
            'id' => $id,
        ]);
    }

    // TEST 4: Siswa hanya dapat membaca, tidak dapat CRUD
    public function test_siswa_tidak_dapat_mengelola_buku(): void
    {
        $siswa = $this->buatPengguna('siswa');
        $kategori = $this->buatKategori();

        $buku = Buku::create(
            $this->dataBuku($kategori->id)
        );

        $this->actingAs($siswa);

        // Siswa boleh melihat daftar dan detail buku
        $this->getJson('/api/buku')
            ->assertOk();

        $this->getJson("/api/buku/{$buku->id}")
            ->assertOk();

        // Siswa tidak boleh menambah
        $this->postJson(
            '/api/buku',
            $this->dataBuku($kategori->id)
        )->assertForbidden();

        // Siswa tidak boleh mengubah
        $this->putJson("/api/buku/{$buku->id}", [
            'judul' => 'Diubah Oleh Siswa',
        ])->assertForbidden();

        // Siswa tidak boleh menghapus
        $this->deleteJson("/api/buku/{$buku->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('bukus', [
            'id' => $buku->id,
            'judul' => 'Buku Praktikum Modul 5',
        ]);
    }

    // TEST 5: Input buku tidak valid ditolak
    public function test_validasi_data_buku_berjalan(): void
    {
        $admin = $this->buatPengguna('admin');
        $kategori = $this->buatKategori();

        $this->actingAs($admin)
            ->postJson('/api/buku', [
                'kategori_id' => $kategori->id,
                'judul' => '',
                'penulis' => 'Penulis Pengujian',
                'tahun_terbit' => 2024,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['judul']);

        $this->assertDatabaseCount('bukus', 0);
    }

    // TEST 6: Buku dengan riwayat peminjaman tidak boleh dihapus
    public function test_buku_dengan_riwayat_tidak_dapat_dihapus(): void
    {
        $admin = $this->buatPengguna('admin');
        $siswa = $this->buatPengguna('siswa');
        $kategori = $this->buatKategori();

        $anggota = $siswa->anggota()->create([
            'nama' => $siswa->name,
            'email' => $siswa->email,
            'no_telepon' => '081234567890',
        ]);

        $buku = Buku::create(
            $this->dataBuku($kategori->id)
        );

        Peminjaman::create([
            'anggota_id' => $anggota->id,
            'buku_id' => $buku->id,
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_kembali' => null,
            'status' => 'menunggu',
        ]);

        $this->actingAs($admin)
            ->deleteJson("/api/buku/{$buku->id}")
            ->assertStatus(409);

        // Buku tetap ada karena memiliki riwayat
        $this->assertDatabaseHas('bukus', [
            'id' => $buku->id,
        ]);
    }
}
