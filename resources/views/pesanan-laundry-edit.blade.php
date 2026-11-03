<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Pesanan Laundry</title>
</head>
<body>
    <h1>Ubah Pesanan Laundry</h1>

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

    <form action="{{ route('pesanan.update', $pesanan['id']) }}" method="POST">
        @csrf
        @method('PUT')

        <p>
            Kode Pesanan<br>
            <input type="text" name="kode_pesanan"
                   value="{{ old('kode_pesanan', $pesanan['kode_pesanan']) }}">
        </p>

        <p>
            Nama Pelanggan<br>
            <input type="text" name="nama_pelanggan"
                   value="{{ old('nama_pelanggan', $pesanan['nama_pelanggan']) }}">
        </p>

        <p>
            Nomor Telepon<br>
            <input type="text" name="nomor_telepon"
                   value="{{ old('nomor_telepon', $pesanan['nomor_telepon']) }}">
        </p>

        <p>
            Berat (kg)<br>
            <input type="number" name="berat_kg"
                   value="{{ old('berat_kg', $pesanan['berat_kg']) }}">
        </p>

        <p>
            Jenis Layanan<br>
            <select name="jenis_layanan_id">
                @foreach ($daftarLayanan as $layanan)
                    <option value="{{ $layanan['id'] }}"
                        @selected(old('jenis_layanan_id', $pesanan['jenis_layanan_id']) == $layanan['id'])>
                        {{ $layanan['nama_layanan'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Simpan Perubahan</button>
        <a href="{{ route('pesanan.index') }}">Kembali</a>
    </form>
</body>
</html>
