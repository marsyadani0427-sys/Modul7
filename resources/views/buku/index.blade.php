@extends('layouts.perpustakaan')

@section('title', 'Daftar Buku')

@section('content')
   @php
    $daftarKategori = \App\Models\Kategori::orderBy('nama')->get();
@endphp

<h2>Daftar Buku</h2>

<div id="pesan" style="display:none; margin-bottom:12px; padding:10px;"></div>
@can('kelola-buku')
<form id="form-buku" novalidate style="margin-bottom:20px;">
    <input type="hidden" id="buku-id">

    <div style="margin-bottom:8px;">
        <label for="kategori_id">Kategori</label><br>
        <select id="kategori_id">
            <option value="">-- Pilih kategori --</option>
            @foreach ($daftarKategori as $kategori)
                <option value="{{ $kategori->id }}">{{ $kategori->nama }}</option>
            @endforeach
        </select>
    </div>

    <div style="margin-bottom:8px;">
        <label for="judul">Judul</label><br>
        <input type="text" id="judul" style="width:300px;">
    </div>

    <div style="margin-bottom:8px;">
        <label for="penulis">Penulis</label><br>
        <input type="text" id="penulis" style="width:300px;">
    </div>

    <div style="margin-bottom:8px;">
        <label for="tahun_terbit">Tahun Terbit</label><br>
        <input type="number" id="tahun_terbit" style="width:120px;">
    </div>

    <button type="submit" id="tombol-simpan">Simpan</button>
    <button type="button" id="tombol-batal" style="display:none;">Batal</button>
</form>
@endcan
<table style="width:100%; border-collapse:collapse;" border="1" cellpadding="8">
    <thead>
        <tr>
            <th>No</th>
            <th>Kategori</th>
            <th>Judul</th>
            <th>Penulis</th>
            <th>Tahun Terbit</th>
           @can('kelola-buku')
                <th>Aksi</th>
            @endcan
        </tr>
    </thead>
   
<!-- <tbody id="isi-tabel">
    <tr>
        <td colspan="{{ auth()->user()->can('kelola-buku') ? 6 : 5 }}">
            Memuat data...
        </td>
    </tr>
</tbody> -->

    <tbody id="isi-tabel">

    @forelse ($daftarBuku as $i => $buku)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $buku['kategori'] }}</td>

            {{-- CELAH XSS A1 --}}
            <td>{{ $buku['judul'] }}</td>

            <td>{{ $buku['penulis'] }}</td>
            <td>{{ $buku['tahun_terbit'] }}</td>

            @can('kelola-buku')
                <td>
                    <button type="button"
                        onclick="mulaiUbah({{ $buku['id'] }})">
                        Ubah
                    </button>

                    <button type="button"
                        onclick="hapusBuku({{ $buku['id'] }})">
                        Hapus
                    </button>
                </td>
            @endcan
        </tr>
    @empty
        <tr>
            <td colspan="{{ auth()->user()->can('kelola-buku') ? 6 : 5 }}">
                Belum ada data buku.
            </td>
        </tr>
    @endforelse

</tbody>
</table>

<form id="form-hapus-buku" method="POST" hidden>
    @csrf
    @method('DELETE')
</form>

<script>
    const API_URL = '/api/buku';

    // Memeriksa permission pengguna yang sedang login
    const BOLEH_KELOLA_BUKU = @json(auth()->user()->can('kelola-buku'));

    // Siswa memiliki 5 kolom, Admin/Petugas memiliki 6 kolom
    const JUMLAH_KOLOM = BOLEH_KELOLA_BUKU ? 6 : 5;
    const form = document.getElementById('form-buku');
    const inputId = document.getElementById('buku-id');
    const inputKategori = document.getElementById('kategori_id');
    const inputJudul = document.getElementById('judul');
    const inputPenulis = document.getElementById('penulis');
    const inputTahun = document.getElementById('tahun_terbit');
    const tombolSimpan = document.getElementById('tombol-simpan');
    const tombolBatal = document.getElementById('tombol-batal');
    const isiTabel = document.getElementById('isi-tabel');
    const kotakPesan = document.getElementById('pesan');

    let daftarBuku = [];

    function tampilkanPesan(teks, sukses = true) {
        kotakPesan.textContent = teks;
        kotakPesan.style.display = 'block';
        kotakPesan.style.color = sukses ? '#0a6b2d' : '#b00020';
    }

    function esc(nilai) {
        const div = document.createElement('div');
        div.textContent = nilai ?? '';
        return div.innerHTML;
    }

    async function kirimRequest(url, metode = 'GET', data = null) {
        const opsi = {
            method: metode,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
        };

        if (data !== null) {
            opsi.headers['Content-Type'] = 'application/json';
            opsi.body = JSON.stringify(data);
        }

        let response;
        try {
            response = await fetch(url, opsi);
        } catch (error) {
            throw new Error('API tidak dapat diakses. Periksa koneksi atau pastikan server menyala.');
        }

        const hasil = await response.json().catch(() => ({}));

        if (!response.ok) {
            if (response.status === 422 && hasil.errors) {
                throw new Error('Validasi gagal: ' + Object.values(hasil.errors).flat().join(' '));
            }
            if (response.status === 404) {
                throw new Error('Data buku tidak ditemukan (mungkin sudah dihapus).');
            }
            throw new Error(hasil.message || 'Server membalas dengan status ' + response.status);
        }

        return hasil;
    }

function tampilkanTabel(daftar) {
    if (daftar.length === 0) {
        isiTabel.innerHTML = `
            <tr>
                <td colspan="${JUMLAH_KOLOM}">
                    Belum ada data buku.
                </td>
            </tr>
        `;
        return;
    }

    isiTabel.innerHTML = daftar.map((buku, i) => {

        const kolomAksi = BOLEH_KELOLA_BUKU
            ? `
                <td>
                    <button type="button"
                        onclick="mulaiUbah(${buku.id})">
                        Ubah
                    </button>

                    <button type="button"
                        onclick="hapusBuku(${buku.id})">
                        Hapus
                    </button>
                </td>
            `
            : '';

        return `
            <tr>
                <td>${i + 1}</td>
                <td>${esc(buku.kategori)}</td>
                <td>${esc(buku.judul)}</td>
                <td>${esc(buku.penulis)}</td>
                <td>${esc(buku.tahun)}</td>
                ${kolomAksi}
            </tr>
        `;
    }).join('');
}
//yg lama
//   async function ambilBuku() {
//     try {
//         const hasil = await kirimRequest(API_URL);

//         if (!Array.isArray(hasil.data)) {
//             throw new Error('Format data dari API tidak sesuai.');
//         }

//         daftarBuku = hasil.data;

//         tampilkanTabel(daftarBuku);

//     } catch (error) {

//         isiTabel.innerHTML = `
//             <tr>
//                 <td colspan="${JUMLAH_KOLOM}">
//                     Data gagal dimuat.
//                 </td>
//             </tr>
//         `;

//         tampilkanPesan(
//             'Gagal mengambil data buku. ' + error.message,
//             false
//         );
//     }
// }

//yg baru
async function ambilBuku(tampilkan = true) {
    try {
        const hasil = await kirimRequest(API_URL);

        if (!Array.isArray(hasil.data)) {
            throw new Error('Format data dari API tidak sesuai.');
        }

        daftarBuku = hasil.data;

        if (tampilkan) {
            tampilkanTabel(daftarBuku);
        }

    } catch (error) {

        if (tampilkan) {
            isiTabel.innerHTML = `
                <tr>
                    <td colspan="${JUMLAH_KOLOM}">
                        Data gagal dimuat.
                    </td>
                </tr>
            `;
        }

        tampilkanPesan(
            'Gagal mengambil data buku. ' + error.message,
            false
        );
    }
}

    function resetForm() {
        form.reset();
        inputId.value = '';
        tombolSimpan.textContent = 'Simpan';
        tombolBatal.style.display = 'none';
    }

    function mulaiUbah(id) {
        const buku = daftarBuku.find((item) => item.id === id);

        if (!buku) {
            tampilkanPesan('Data buku tidak ditemukan di tabel.', false);
            return;
        }

        inputId.value = buku.id;
        inputKategori.value = buku.kategori_id;
        inputJudul.value = buku.judul;
        inputPenulis.value = buku.penulis;
        inputTahun.value = buku.tahun;

        tombolSimpan.textContent = 'Perbarui';
        tombolBatal.style.display = 'inline';
        form.scrollIntoView({ behavior: 'smooth' });
    }

    // async function hapusBuku(id) {
    //     const buku = daftarBuku.find((item) => item.id === id);
    //     const judul = buku ? buku.judul : 'ini';

    //     if (!confirm(`Hapus buku "${judul}"?`)) {
    //         return;
    //     }

    //     try {
    //         const hasil = await kirimRequest(`${API_URL}/${id}`, 'DELETE');

    //         if (inputId.value === String(id)) {
    //             resetForm();
    //         }

    //         tampilkanPesan(hasil.message || 'Buku berhasil dihapus.', true);
    //         await ambilBuku();
    //     } catch (error) {
    //         tampilkanPesan(error.message, false);
    //     }
    // }

    const formHapus = document.getElementById('form-hapus-buku');

async function hapusBuku(id) {
    const buku = daftarBuku.find(item => item.id === id);
    const judul = buku ? buku.judul : 'ini';

    if (!confirm(`Hapus buku "${judul}"?`)) {
        return;
    }

    try {
        formHapus.action = `/buku/${id}`;

        const response = await fetch(formHapus.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: new FormData(formHapus)
        });

        const hasil = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(
                hasil.message || `Gagal menghapus buku (${response.status})`
            );
        }

        if (inputId.value === String(id)) {
            resetForm();
        }

        tampilkanPesan(
            hasil.message || 'Buku berhasil dihapus.',
            true
        );

        await ambilBuku();

    } catch (error) {
        tampilkanPesan(error.message, false);
    }
}

  // Event form hanya dipasang jika form tersedia
if (form) {

    form.addEventListener('submit', async (event) => {

        event.preventDefault();
        kotakPesan.style.display = 'none';

        const data = {
            kategori_id: inputKategori.value,
            judul: inputJudul.value,
            penulis: inputPenulis.value,
            tahun_terbit: inputTahun.value,
        };

        const sedangUbah = inputId.value !== '';

        const url = sedangUbah
            ? `${API_URL}/${inputId.value}`
            : API_URL;

        const metode = sedangUbah ? 'PUT' : 'POST';

        try {

            const hasil = await kirimRequest(url, metode, data);

            resetForm();

            tampilkanPesan(
                hasil.message || 'Data berhasil disimpan.',
                true
            );

            await ambilBuku();

        } catch (error) {

            tampilkanPesan(error.message, false);

        }
    });

}

// Event tombol batal hanya dipasang jika tersedia
if (tombolBatal) {

    tombolBatal.addEventListener('click', () => {

        resetForm();

        kotakPesan.style.display = 'none';

    });

}
//yg lama
    // ambilBuku();

    //yg baru
    ambilBuku(false);
</script>
@endsection
