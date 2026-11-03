<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Laundry</title>
</head>
<body>
    <h1>Pesanan Laundry</h1>

    <h2>Daftar Pesanan</h2>

    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Kode Pesanan</th>
            <th>Nama Pelanggan</th>
            <th>Nomor Telepon</th>
            <th>Berat (kg)</th>
            <th>Jenis Layanan</th>
            <th>Status Berat</th>
        </tr>
        @forelse ($daftarPesanan as $pesanan)
            <tr>
                <td>{{ $pesanan['kode_pesanan'] }}</td>
                <td>{{ $pesanan['nama_pelanggan'] }}</td>
                <td>{{ $pesanan['nomor_telepon'] }}</td>
                <td>{{ $pesanan['berat_kg'] }}</td>
                <td>{{ $pesanan['nama_layanan'] }}</td>
                <td>
                    @if (statusBerat($pesanan['berat_kg']) === 'Besar')
                        <strong>Besar</strong>
                    @else
                        Reguler
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">Data pesanan tidak ditemukan.</td>
            </tr>
        @endforelse
    </table>
</body>
</html>
