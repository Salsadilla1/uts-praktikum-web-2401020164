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

Route::get('/pesanan-laundry', function () {
    try {
        $pdo = DB::connection()->getPdo();

        $pernyataan = $pdo->prepare(
            'SELECT p.id, p.kode_pesanan, p.nama_pelanggan, p.nomor_telepon,
                    p.berat_kg, p.jenis_layanan_id, j.nama_layanan
             FROM pesanan_laundry AS p
             JOIN jenis_layanan AS j
               ON j.id = p.jenis_layanan_id
             ORDER BY p.kode_pesanan'
        );
        $pernyataan->execute();
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
            'daftarLayanan'
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
