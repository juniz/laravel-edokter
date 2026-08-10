# Task 3 Report: Add Print Button Link to Odontogram Card Header

## Implementation Details
- Modified `resources/views/livewire/ralan/odontogram.blade.php`.
- Added print button link pointing to `route('odontogram.cetak', ['no_rawat' => $noRawat])` with `target="_blank"`, print icon (`fas fa-print fa-lg`), and tooltip `Cetak Odontogram` inside the card tools container next to the collapse button.
- Preserved existing card tool classes (`btn btn-tool text-white`) and layout (`d-flex align-items-center`).

## Verification
- Code syntax checked with `php -l resources/views/livewire/ralan/odontogram.blade.php` (passed cleanly).
- Git commit created: `feat: add print button link in Odontogram card header` (hash `169324d`).

## Status
DONE
