<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Penandaan Area Operasi</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 14mm;
        }

        * { box-sizing: border-box; }
        body { color: #111; font-family: Arial, sans-serif; font-size: 11pt; margin: 0; }
        h1 { font-size: 16pt; margin: 0 0 14px; text-align: center; }
        h2 { border-bottom: 1px solid #111; font-size: 12pt; margin: 18px 0 8px; padding-bottom: 4px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #111; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { width: 30%; }
        .images { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .image { break-inside: avoid; page-break-inside: avoid; }
        .image img { display: block; height: auto; max-width: 100%; width: 100%; }

        @media print {
            .no-print { display: none; }
            .image { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <h1>FORMULIR PENANDAAN AREA OPERASI</h1>

    <h2>Data Pasien</h2>
    <table>
        <tr><th>No. Rawat</th><td>{{ $marking->no_rawat }}</td></tr>
        <tr><th>No. RM</th><td>{{ $marking->no_rm }}</td></tr>
        <tr><th>Nama Pasien</th><td>{{ $marking->nama_pasien }}</td></tr>
        <tr><th>Tanggal Lahir</th><td>{{ $marking->tgl_lahir }}</td></tr>
    </table>

    <h2>Prosedur Operasi</h2>
    <p>{{ $marking->prosedur_operasi }}</p>

    <h2>Petugas Penandaan</h2>
    <table>
        <tr><th>Nama Operator</th><td>{{ $marking->nama_operator }}</td></tr>
        <tr><th>Tanggal/Jam Penandaan</th><td>{{ $marking->tanggal_penandaan }}</td></tr>
    </table>

    <h2>Diagram Penandaan</h2>
    <div class="images">
        @foreach ($images as $key => $image)
            <figure class="image">
                <figcaption>{{ str_replace('_', ' ', $key) }}</figcaption>
                <img src="{{ $image }}" alt="Diagram penandaan {{ str_replace('_', ' ', $key) }}">
            </figure>
        @endforeach
    </div>
</body>
</html>
