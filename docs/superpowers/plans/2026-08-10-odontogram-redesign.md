# Odontogram Redesign & Interactive Animations Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign the dental examination Odontogram view in `odontogram.blade.php` and `Odontogram.php` with direct click selection, interactive condition pills, statistics counter bar, and CSS micro-animations.

**Architecture:** Update `Odontogram.php` component to provide helper counts and event listeners, then overhaul `odontogram.blade.php` with responsive tooth grid layout, interactive JS events for direct tooth selection, pill buttons for conditions, CSS animations (`pulse`, `scale`, `glow`, `ripple`), and styled history table.

**Tech Stack:** Laravel, Livewire, Blade, Select2, SweetAlert2, CSS3 Keyframes & Flexbox/Grid.

## Global Constraints

- Preserve all existing database operations (`pemeriksaan_odontogram` insert/delete) and data structure (`gg_xx`, `value`, `catatan`).
- Preserve Select2 fallback compatibility.
- Ensure all tooth numbers (18-11, 21-28, 48-41, 31-38, 55-51, 61-65, 85-81, 71-75) are correctly clickable.

---

### Task 1: Update Livewire Component `Odontogram.php` for Live Sync & Quick Actions

**Files:**
- Modify: `app/Http/Livewire/Ralan/Odontogram.php`

**Interfaces:**
- Produces: `selectGigiFromGrid($toothId)` method, helper stats counts (`totalPemeriksaan`, `kariesCount`, `tanggalCount`, `tumpatanCount`, `akarCount`)

- [ ] **Step 1: Update `Odontogram.php` with quick selection methods and statistics**

In `app/Http/Livewire/Ralan/Odontogram.php`, update the class to include listeners for direct tooth click and condition selection:
```php
<?php

namespace App\Http\Livewire\Ralan;

use App\Traits\SwalResponse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Odontogram extends Component
{
    use SwalResponse;
    public $isCollapsed = true, $gigi, $penyakit, $catatan, $listPemeriksaanOdontogram = [], $noRawat, $noRm, $swal = 'swal:odontogram';
    
    protected $rules = [
        'gigi' => 'required',
        'penyakit' => 'required',
        'catatan' => 'nullable',
    ];

    protected $messages = [
        'gigi.required' => 'Gigi tidak boleh kosong',
        'penyakit.required' => 'Hasil pemeriksaan tidak boleh kosong',
    ];

    protected $listeners = ['hapusOdontogram', 'selectGigiDirect' => 'setGigi', 'selectPenyakitDirect' => 'setPenyakit'];

    public function mount($noRawat, $noRm)
    {
        $this->noRawat = $noRawat;
        $this->noRm = $noRm;
        $this->getPemeriksaanOdontogram();
    }

    public function hydrate()
    {
        $this->getPemeriksaanOdontogram();
    }

    public function render()
    {
        return view('livewire.ralan.odontogram');
    }

    public function collapsed()
    {
        $this->isCollapsed = !$this->isCollapsed;
    }

    public function setGigi($value)
    {
        $this->gigi = $value;
    }

    public function setPenyakit($value)
    {
        $this->penyakit = $value;
    }

    public function getPemeriksaanOdontogram()
    {
        $this->listPemeriksaanOdontogram = DB::table('pemeriksaan_odontogram')
                                                ->where('no_rkm_medis', $this->noRm)
                                                ->get();
    }

    public function save()
    {
        $this->validate();

        try{
            DB::beginTransaction();
            DB::table('pemeriksaan_odontogram')
                ->insert([
                    'no_rawat' => $this->noRawat,
                    'no_rkm_medis' => $this->noRm,
                    'tgl_perawatan' => date('Y-m-d'),
                    'jam_rawat' => date('H:i:s'),
                    'gg_xx' => $this->gigi,
                    'value' => $this->penyakit,
                    'catatan' => $this->catatan ?? '-',
                ]);
            $this->getPemeriksaanOdontogram();
            DB::commit();
            $this->dispatchBrowserEvent($this->swal, $this->toastResponse('Pemeriksaan Odontogram berhasil disimpan'));
            $this->reset(['gigi', 'penyakit', 'catatan']);
            $this->dispatchBrowserEvent('odontogram:saved');
        }catch(\Illuminate\Database\QueryException $ex){
            DB::rollBack();
            $this->dispatchBrowserEvent($this->swal, $this->toastResponse($ex->getMessage() ?? 'Pemeriksaan Odontogram gagal disimpan', 'error'));
        }
    }

    public function delete($id, $tgl)
    {
        $this->dispatchBrowserEvent($this->swal.':confirm', $this->swalConfirmDialog('hapusOdontogram', [$id, $tgl]));
    }

    public function hapusOdontogram($id)
    {
        try{
            DB::beginTransaction();
            DB::table('pemeriksaan_odontogram')
                ->where('no_rawat', $this->noRawat)
                ->where('gg_xx', $id)
                ->delete();

            $this->getPemeriksaanOdontogram();
            DB::commit();
            $this->dispatchBrowserEvent($this->swal, $this->toastResponse('Pemeriksaan Odontogram berhasil dihapus'));
            $this->dispatchBrowserEvent('odontogram:deleted');
        }catch(\Illuminate\Database\QueryException $ex){
            DB::rollBack();
            $this->dispatchBrowserEvent($this->swal, $this->toastResponse($ex->getMessage() ?? 'Pemeriksaan Odontogram gagal dihapus', 'error'));
        }
    }
}
```

- [ ] **Step 2: Commit Task 1**

```bash
git add app/Http/Livewire/Ralan/Odontogram.php
git commit -m "feat: add quick select listeners and optional validation rules in Odontogram.php"
```

---

### Task 2: Overhaul Odontogram View `odontogram.blade.php` with Animations & Interactive Controls

**Files:**
- Modify: `resources/views/livewire/ralan/odontogram.blade.php`

**Interfaces:**
- Consumes: `$listPemeriksaanOdontogram`, `$gigi`, `$penyakit`, `$catatan`

- [ ] **Step 1: Redesign `odontogram.blade.php` layout and interactivity**

Update `resources/views/livewire/ralan/odontogram.blade.php` with:
- Summary Statistics Badges at top (Total, Karies, Tanggal, Sisa Akar, Tumpatan).
- Interactive Grid with click handler (`onclick="selectTooth('gg_XX')"`) on each tooth cell.
- CSS Micro-Animations (`.tooth-cell`, `.tooth-selected`, `.pulse-glow`, `.pill-btn`).
- Quick Condition Selector Pills (Tanggal, Karies, Sisa Akar, Tumpatan, Fraktur Mahkota).
- Styled form and enhanced history table.

- [ ] **Step 2: Commit Task 2**

```bash
git add resources/views/livewire/ralan/odontogram.blade.php
git commit -m "feat: redesign odontogram view with interactive click selection, condition pills, and CSS animations"
```
