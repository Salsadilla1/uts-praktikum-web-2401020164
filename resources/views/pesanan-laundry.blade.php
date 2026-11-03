<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Laundry</title>
</head>
<body>
    <h1>Pesanan Laundry</h1>

    @if (session('berhasil'))
        <p>{{ session('berhasil') }}</p>
    @endif

    @if (session('gagal'))
        <p>{{ session('gagal') }}</p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h2>Tambah Pesanan</h2>

    <form action="{{ route('pesanan.store') }}" method="POST">
        @csrf

        <p>
            Kode Pesanan<br>
            <input type="text" name="kode_pesanan" value="{{ old('kode_pesanan') }}">
        </p>

        <p>
            Nama Pelanggan<br>
            <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}">
        </p>

        <p>
            Nomor Telepon<br>
            <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon') }}">
        </p>

        <p>
            Berat (kg)<br>
            <input type="number" name="berat_kg" value="{{ old('berat_kg') }}">
        </p>

        <p>
            Jenis Layanan<br>
            <select name="jenis_layanan_id">
                <option value="">-- Pilih Jenis Layanan --</option>
                @foreach ($daftarLayanan as $layanan)
                    <option value="{{ $layanan['id'] }}"
                        @selected(old('jenis_layanan_id') == $layanan['id'])>
                        {{ $layanan['nama_layanan'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Tambah Pesanan</button>
    </form>

    <h2>Daftar Pesanan</h2>

    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Kode Pesanan</th>
            <th>Nama Pelanggan</th>
            <th>Nomor Telepon</th>
            <th>Berat (kg)</th>
            <th>Jenis Layanan</th>
            <th>Status Berat</th>
            <th>Aksi</th>
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
                <td>
                    <a href="{{ route('pesanan.edit', $pesanan['id']) }}">Ubah</a>
                    <form action="{{ route('pesanan.destroy', $pesanan['id']) }}"
                          method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">Data pesanan tidak ditemukan.</td>
            </tr>
        @endforelse
    </table>
</body>
</html>
