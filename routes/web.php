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

        return view('pesanan-laundry', compact('daftarPesanan'));
    } catch (\Throwable $error) {
        report($error);

        return response(
            'Data pesanan tidak dapat dibaca. Periksa koneksi dan query.',
            500
        );
    }
})->name('pesanan.index');
