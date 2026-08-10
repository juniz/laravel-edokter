# Task 2 Report: Create printable template `resources/views/prints/odontogram.blade.php`

## Summary of Changes
- Created 1:1 A4 printable Blade view `resources/views/prints/odontogram.blade.php`.
- Implemented Kop header with hospital logo support, titles (`POLRI DAERAH JAWA TIMUR`, `BIDANG KEDOKTERAN DAN KESEHATAN`, `RUMAH SAKIT BHAYANGKARA TK. III NGANJUK`), document box top right (`RM. 06.a / Odontogram`).
- Implemented Form Title: `FORMULIR PEMERIKSAAN ODONTOGRAM ({$jenisKelaminTitle})`.
- Implemented Patient Metadata Table (`meta-table`) with barcode styling and barcode text `*{$regData->no_rkm_medis}*`.
- Implemented 6 counter stat cards (Total Periksa, Tanggal, Karies, Sisa Akar, Tumpatan, Fraktur Mahkota).
- Implemented 1:1 Odontogram Chart Grid (Maxilla, Mandibula, Gigi Desidui Atas & Bawah) with background images (`posterior.png`, `anterior.png`) and tooth condition image overlays.
- Implemented Legend Bar and History Table listing all records in `$listPemeriksaan`.
- Implemented Statement Declaration Box.
- Implemented Signature Footer with TTE container, QRCode generation script (`#qrcode`), metadata, and auto-print trigger (`window.print()`).

## Verification
- Ran `php -l resources/views/prints/odontogram.blade.php` - syntax check passed without errors.
- Ran `php artisan view:cache` - Blade compilation successful.
- Git committed with message: "feat: create 1:1 A4 Odontogram print template with hospital Kop and TTE footer".

## Status
DONE
