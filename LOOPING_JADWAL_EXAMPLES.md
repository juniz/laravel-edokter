# Contoh Looping Data Jadwal Pegawai (h1-h31)

Dokumen ini berisi berbagai cara untuk looping data dari tabel `jadwal_pegawai` untuk kolom `h1` sampai `h31`.

## 1. Dynamic Property Access (Paling Sederhana)

```php
// Menggunakan dynamic property access
for ($i = 1; $i <= 31; $i++) {
    $columnName = 'h' . $i;
    $shiftValue = $jadwal->$columnName ?? 'Tidak ada data';
    echo "Hari {$i}: {$shiftValue}\n";
}
```

## 2. Array Access

```php
// Konversi object ke array terlebih dahulu
$jadwalArray = (array) $jadwal;
for ($i = 1; $i <= 31; $i++) {
    $columnName = 'h' . $i;
    $shiftValue = $jadwalArray[$columnName] ?? 'Tidak ada data';
    echo "Hari {$i}: {$shiftValue}\n";
}
```

## 3. Menggunakan get_object_vars()

```php
// Menggunakan get_object_vars() untuk mendapatkan properties
$jadwalVars = get_object_vars($jadwal);
for ($i = 1; $i <= 31; $i++) {
    $columnName = 'h' . $i;
    $shiftValue = $jadwalVars[$columnName] ?? 'Tidak ada data';
    echo "Hari {$i}: {$shiftValue}\n";
}
```

## 4. Menggunakan range() untuk Efisiensi

```php
// Menggunakan range() untuk lebih efisien
foreach (range(1, 31) as $day) {
    $columnName = 'h' . $day;
    $shiftValue = $jadwal->$columnName ?? 'Tidak ada data';
    echo "Hari {$day}: {$shiftValue}\n";
}
```

## 5. Filter Hanya Data yang Ada

```php
// Menampilkan hanya data yang ada (tidak kosong)
$jadwalArray = (array) $jadwal;
$jadwalData = array_filter($jadwalArray, function($key) {
    return strpos($key, 'h') === 0 && is_numeric(substr($key, 1));
}, ARRAY_FILTER_USE_KEY);

foreach ($jadwalData as $key => $value) {
    $hari = substr($key, 1); // Ambil angka dari h1, h2, dst
    echo "Hari {$hari}: {$value}\n";
}
```

## 6. Menggunakan Collection (Laravel)

```php
use Illuminate\Support\Collection;

// Jika data sudah dalam bentuk Collection
$jadwalCollection = collect($jadwal);
for ($i = 1; $i <= 31; $i++) {
    $columnName = 'h' . $i;
    $shiftValue = $jadwalCollection->get($columnName, 'Tidak ada data');
    echo "Hari {$i}: {$shiftValue}\n";
}
```

## 7. Menggunakan Reflection (Advanced)

```php
// Menggunakan Reflection untuk akses properties
$reflection = new ReflectionObject($jadwal);
$properties = $reflection->getProperties();

foreach ($properties as $property) {
    $propertyName = $property->getName();
    if (preg_match('/^h(\d+)$/', $propertyName, $matches)) {
        $hari = $matches[1];
        $property->setAccessible(true);
        $value = $property->getValue($jadwal);
        echo "Hari {$hari}: {$value}\n";
    }
}
```

## 8. Menggunakan Database Query Langsung

```php
// Query langsung dari database dengan kolom dinamis
$columns = [];
for ($i = 1; $i <= 31; $i++) {
    $columns[] = "h{$i}";
}

$jadwal = DB::table('jadwal_pegawai')
    ->select(array_merge(['id', 'tahun', 'bulan'], $columns))
    ->where('id', $id)
    ->where('tahun', $tahun)
    ->where('bulan', $bulan)
    ->first();

// Kemudian loop seperti biasa
for ($i = 1; $i <= 31; $i++) {
    $columnName = 'h' . $i;
    $shiftValue = $jadwal->$columnName ?? 'Tidak ada data';
    echo "Hari {$i}: {$shiftValue}\n";
}
```

## 9. Menggunakan Helper Function

```php
// Buat helper function untuk reusability
function getJadwalByDay($jadwal, $day) {
    $columnName = 'h' . $day;
    return $jadwal->$columnName ?? 'Tidak ada data';
}

// Penggunaan
for ($i = 1; $i <= 31; $i++) {
    $shiftValue = getJadwalByDay($jadwal, $i);
    echo "Hari {$i}: {$shiftValue}\n";
}
```

## 10. Menggunakan Array Map

```php
// Menggunakan array_map untuk transformasi data
$days = range(1, 31);
$jadwalData = array_map(function($day) use ($jadwal) {
    $columnName = 'h' . $day;
    return [
        'hari' => $day,
        'shift' => $jadwal->$columnName ?? 'Tidak ada data'
    ];
}, $days);

foreach ($jadwalData as $data) {
    echo "Hari {$data['hari']}: {$data['shift']}\n";
}
```

## Rekomendasi

- **Untuk penggunaan sederhana**: Gunakan **Dynamic Property Access** (Cara 1)
- **Untuk performa terbaik**: Gunakan **range()** (Cara 4)
- **Untuk data yang mungkin tidak lengkap**: Gunakan **Array Access** dengan pengecekan (Cara 2)
- **Untuk filtering data**: Gunakan **array_filter** (Cara 5)

## Catatan Penting

1. Pastikan data `$jadwal` tidak null sebelum melakukan looping
2. Gunakan null coalescing operator (`??`) untuk menangani data yang mungkin kosong
3. Pertimbangkan performa jika data sangat besar
4. Gunakan method yang sesuai dengan kebutuhan aplikasi Anda





























