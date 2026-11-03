<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

function statusBerat($berat)
{
    if ($berat >= 10) {
        return 'Besar';
    }

    return 'Reguler';
}

function bersihkanInputPesanan(Request $request)
{
    $request->merge([
        'kode_pesanan' => strip_tags(trim((string) $request->input('kode_pesanan'))),
        'nama_pelanggan' => strip_tags(trim((string) $request->input('nama_pelanggan'))),
        'nomor_telepon' => strip_tags(trim((string) $request->input('nomor_telepon'))),
        'berat_kg' => strip_tags(trim((string) $request->input('berat_kg'))),
        'jenis_layanan_id' => strip_tags(trim((string) $request->input('jenis_layanan_id'))),
    ]);
}

function aturanPesanan()
{
    return [
        'kode_pesanan' => ['required', 'size:10', 'regex:/^[A-Za-z0-9]{10}$/'],
        'nama_pelanggan' => ['required', 'string', 'min:3', 'max:100'],
        'nomor_telepon' => ['required', 'string', 'min:10', 'max:20'],
        'berat_kg' => ['required', 'integer', 'min:1', 'max:20'],
        'jenis_layanan_id' => ['required', 'integer', 'exists:jenis_layanan,id'],
    ];
}

function pesanPesanan()
{
    return [
        'kode_pesanan.required' => 'Kode pesanan wajib diisi.',
        'kode_pesanan.size' => 'Kode pesanan harus tepat 10 karakter.',
        'kode_pesanan.regex' => 'Kode pesanan hanya boleh berisi huruf dan angka.',
        'nama_pelanggan.required' => 'Nama pelanggan wajib diisi.',
        'nama_pelanggan.min' => 'Nama pelanggan minimal 3 karakter.',
        'nama_pelanggan.max' => 'Nama pelanggan maksimal 100 karakter.',
        'nomor_telepon.required' => 'Nomor telepon wajib diisi.',
        'nomor_telepon.min' => 'Nomor telepon minimal 10 karakter.',
        'nomor_telepon.max' => 'Nomor telepon maksimal 20 karakter.',
        'berat_kg.required' => 'Berat wajib diisi.',
        'berat_kg.integer' => 'Berat harus berupa bilangan bulat.',
        'berat_kg.min' => 'Berat minimal 1 kg.',
        'berat_kg.max' => 'Berat maksimal 20 kg.',
        'jenis_layanan_id.required' => 'Jenis layanan wajib dipilih.',
        'jenis_layanan_id.integer' => 'Jenis layanan tidak valid.',
        'jenis_layanan_id.exists' => 'Jenis layanan tidak ditemukan.',
    ];
}

Route::get('/', function () {
    return redirect()->route('pesanan.index');
})->name('beranda');

Route::get('/pesanan-laundry', function (Request $request) {
    $kataKunci = trim((string) $request->query('q', ''));
    $jenisLayananId = $request->query('jenis_layanan_id', '');

    try {
        $pdo = DB::connection()->getPdo();

        $sql = 'SELECT p.id, p.kode_pesanan, p.nama_pelanggan, p.nomor_telepon,
                       p.berat_kg, p.jenis_layanan_id, j.nama_layanan
                FROM pesanan_laundry AS p
                JOIN jenis_layanan AS j
                  ON j.id = p.jenis_layanan_id
                WHERE 1 = 1';
        $parameter = [];

        if ($kataKunci !== '') {
            $sql .= ' AND (p.kode_pesanan LIKE :kata_kunci_kode
                        OR p.nama_pelanggan LIKE :kata_kunci_nama
                        OR p.nomor_telepon LIKE :kata_kunci_telepon)';
            $nilaiCari = '%' . $kataKunci . '%';
            $parameter['kata_kunci_kode'] = $nilaiCari;
            $parameter['kata_kunci_nama'] = $nilaiCari;
            $parameter['kata_kunci_telepon'] = $nilaiCari;
        }

        if ($jenisLayananId !== '') {
            $sql .= ' AND p.jenis_layanan_id = :jenis_layanan_id';
            $parameter['jenis_layanan_id'] = (int) $jenisLayananId;
        }

        $sql .= ' ORDER BY p.kode_pesanan';

        $pernyataan = $pdo->prepare($sql);
        $pernyataan->execute($parameter);
        $daftarPesanan = $pernyataan->fetchAll(\PDO::FETCH_ASSOC);

        $pernyataanLayanan = $pdo->prepare(
            'SELECT id, nama_layanan
             FROM jenis_layanan
             ORDER BY nama_layanan'
        );
        $pernyataanLayanan->execute();
        $daftarLayanan = $pernyataanLayanan->fetchAll(\PDO::FETCH_ASSOC);

        return view('pesanan-laundry', compact(
            'daftarPesanan',
            'daftarLayanan',
            'kataKunci',
            'jenisLayananId'
        ));
    } catch (\Throwable $error) {
        report($error);

        return response(
            'Data pesanan tidak dapat dibaca. Periksa koneksi dan query.',
            500
        );
    }
})->name('pesanan.index');

Route::post('/pesanan-laundry', function (Request $request) {
    bersihkanInputPesanan($request);

    $data = $request->validate(
        aturanPesanan(),
        pesanPesanan()
    );

    try {
        $pdo = DB::connection()->getPdo();

        $cek = $pdo->prepare(
            'SELECT COUNT(*) FROM pesanan_laundry
             WHERE kode_pesanan = :kode_pesanan
                OR nomor_telepon = :nomor_telepon'
        );
        $cek->execute([
            'kode_pesanan' => $data['kode_pesanan'],
            'nomor_telepon' => $data['nomor_telepon'],
        ]);

        if ((int) $cek->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'Kode pesanan atau nomor telepon sudah digunakan.'
            );
        }

        $simpan = $pdo->prepare(
            'INSERT INTO pesanan_laundry
                (kode_pesanan, nama_pelanggan, nomor_telepon,
                 berat_kg, jenis_layanan_id, created_at, updated_at)
             VALUES
                (:kode_pesanan, :nama_pelanggan, :nomor_telepon,
                 :berat_kg, :jenis_layanan_id, NOW(), NOW())'
        );
        $simpan->execute([
            'kode_pesanan' => $data['kode_pesanan'],
            'nama_pelanggan' => $data['nama_pelanggan'],
            'nomor_telepon' => $data['nomor_telepon'],
            'berat_kg' => $data['berat_kg'],
            'jenis_layanan_id' => $data['jenis_layanan_id'],
        ]);

        return redirect()->route('pesanan.index')->with(
            'berhasil',
            'Pesanan berhasil ditambahkan.'
        );
    } catch (\Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Pesanan gagal disimpan. Periksa koneksi dan data.'
        );
    }
})->name('pesanan.store');

Route::get('/pesanan-laundry/{id}/edit', function (int $id) {
    try {
        $pdo = DB::connection()->getPdo();

        $ambil = $pdo->prepare(
            'SELECT id, kode_pesanan, nama_pelanggan, nomor_telepon,
                    berat_kg, jenis_layanan_id
             FROM pesanan_laundry
             WHERE id = :id'
        );
        $ambil->execute(['id' => $id]);
        $pesanan = $ambil->fetch(\PDO::FETCH_ASSOC);

        if (!$pesanan) {
            return redirect()->route('pesanan.index')->with(
                'gagal',
                'Pesanan tidak ditemukan.'
            );
        }

        $ambilLayanan = $pdo->prepare(
            'SELECT id, nama_layanan
             FROM jenis_layanan
             ORDER BY nama_layanan'
        );
        $ambilLayanan->execute();
        $daftarLayanan = $ambilLayanan->fetchAll(\PDO::FETCH_ASSOC);

        return view('pesanan-laundry-edit', compact(
            'pesanan',
            'daftarLayanan'
        ));
    } catch (\Throwable $error) {
        report($error);

        return redirect()->route('pesanan.index')->with(
            'gagal',
            'Data pesanan gagal dibuka untuk diubah.'
        );
    }
})->name('pesanan.edit');

Route::put('/pesanan-laundry/{id}', function (Request $request, int $id) {
    bersihkanInputPesanan($request);

    $data = $request->validate(
        aturanPesanan(),
        pesanPesanan()
    );

    try {
        $pdo = DB::connection()->getPdo();

        $cekId = $pdo->prepare(
            'SELECT COUNT(*) FROM pesanan_laundry WHERE id = :id'
        );
        $cekId->execute(['id' => $id]);

        if ((int) $cekId->fetchColumn() === 0) {
            return redirect()->route('pesanan.index')->with(
                'gagal',
                'Pesanan tidak ditemukan.'
            );
        }

        $cek = $pdo->prepare(
            'SELECT COUNT(*) FROM pesanan_laundry
             WHERE (kode_pesanan = :kode_pesanan
                OR nomor_telepon = :nomor_telepon)
               AND id <> :id'
        );
        $cek->execute([
            'kode_pesanan' => $data['kode_pesanan'],
            'nomor_telepon' => $data['nomor_telepon'],
            'id' => $id,
        ]);

        if ((int) $cek->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'Kode pesanan atau nomor telepon sudah digunakan pesanan lain.'
            );
        }

        $ubah = $pdo->prepare(
            'UPDATE pesanan_laundry
             SET kode_pesanan = :kode_pesanan,
                 nama_pelanggan = :nama_pelanggan,
                 nomor_telepon = :nomor_telepon,
                 berat_kg = :berat_kg,
                 jenis_layanan_id = :jenis_layanan_id,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $ubah->execute([
            'kode_pesanan' => $data['kode_pesanan'],
            'nama_pelanggan' => $data['nama_pelanggan'],
            'nomor_telepon' => $data['nomor_telepon'],
            'berat_kg' => $data['berat_kg'],
            'jenis_layanan_id' => $data['jenis_layanan_id'],
            'id' => $id,
        ]);

        return redirect()->route('pesanan.index')->with(
            'berhasil',
            'Pesanan berhasil diperbarui.'
        );
    } catch (\Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Pesanan gagal diperbarui. Periksa koneksi dan data.'
        );
    }
})->name('pesanan.update');

Route::delete('/pesanan-laundry/{id}', function (int $id) {
    try {
        $pdo = DB::connection()->getPdo();

        $hapus = $pdo->prepare(
            'DELETE FROM pesanan_laundry WHERE id = :id'
        );
        $hapus->execute(['id' => $id]);

        if ($hapus->rowCount() === 0) {
            return redirect()->route('pesanan.index')->with(
                'gagal',
                'Pesanan tidak ditemukan.'
            );
        }

        return redirect()->route('pesanan.index')->with(
            'berhasil',
            'Pesanan berhasil dihapus.'
        );
    } catch (\Throwable $error) {
        report($error);

        return redirect()->route('pesanan.index')->with(
            'gagal',
            'Pesanan gagal dihapus. Periksa koneksi dan data.'
        );
    }
})->name('pesanan.destroy');
