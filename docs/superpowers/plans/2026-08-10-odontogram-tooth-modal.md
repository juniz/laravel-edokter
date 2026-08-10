# Tooth Click Input Modal Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Open a Bootstrap Modal (`#modalInputOdontogram`) when a tooth cell in the Odontogram chart is clicked, allowing quick entry/edit of dental examination findings and auto-closing on save.

**Architecture:** Embed `#modalInputOdontogram` in `odontogram.blade.php`, update JS `selectTooth` / `openToothModal` to trigger `$('#modalInputOdontogram').modal('show')`, set `$gigi` Livewire model, and listen to `odontogram:saved` browser event to trigger `$('#modalInputOdontogram').modal('hide')`.

**Tech Stack:** Laravel, Livewire, Blade, Bootstrap 4 Modal, Select2, SweetAlert2.

## Global Constraints

- Preserve all existing database operations and Select2 behavior.
- Ensure modal is responsive and clean on mobile & desktop screens.

---

### Task 1: Create `#modalInputOdontogram` Bootstrap Modal & JS Trigger in `odontogram.blade.php`

**Files:**
- Modify: `resources/views/livewire/ralan/odontogram.blade.php`

**Interfaces:**
- Produces: `#modalInputOdontogram` modal element, `openToothModal(toothId, label)` JS function, `odontogram:saved` listener

- [ ] **Step 1: Add Bootstrap Modal `#modalInputOdontogram` markup in `odontogram.blade.php`**

Add the modal structure to `resources/views/livewire/ralan/odontogram.blade.php` containing header, quick condition pills, condition select dropdown, notes textarea, and form submit:
```blade
<!-- Modal Input Pemeriksaan Odontogram -->
<div class="modal fade" id="modalInputOdontogram" tabindex="-1" role="dialog" aria-labelledby="modalInputOdontogramLabel" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold" id="modalInputOdontogramLabel">
                    <i class="fas fa-tooth mr-2"></i> Input Pemeriksaan <span id="modalToothTitle" class="badge badge-light text-info ml-1">Gigi</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form wire:submit.prevent="save">
                <div class="modal-body">
                    <input type="hidden" wire:model.defer="gigi" id="modal_gigi_input">
                    
                    <div class="form-group">
                        <label class="font-weight-bold text-dark mb-1">
                            <i class="fas fa-hand-pointer text-info mr-1"></i> Opsi Cepat Hasil Pemeriksaan
                        </label>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <button type="button" onclick="selectCondition('Tanggal')" class="btn btn-sm btn-outline-danger condition-pill rounded-pill mr-1 mb-1" data-condition="Tanggal">
                                <i class="fas fa-times-circle mr-1"></i> Tanggal
                            </button>
                            <button type="button" onclick="selectCondition('Karies')" class="btn btn-sm btn-outline-warning text-dark condition-pill rounded-pill mr-1 mb-1" data-condition="Karies">
                                <i class="fas fa-bug mr-1"></i> Karies
                            </button>
                            <button type="button" onclick="selectCondition('Akar')" class="btn btn-sm btn-outline-dark condition-pill rounded-pill mr-1 mb-1" data-condition="Akar">
                                <i class="fas fa-spa mr-1"></i> Sisa Akar
                            </button>
                            <button type="button" onclick="selectCondition('Tumpat')" class="btn btn-sm btn-outline-info condition-pill rounded-pill mr-1 mb-1" data-condition="Tumpat">
                                <i class="fas fa-fill-drip mr-1"></i> Tumpatan
                            </button>
                            <button type="button" onclick="selectCondition('Fraktur Mahkota')" class="btn btn-sm btn-outline-secondary condition-pill rounded-pill mr-1 mb-1" data-condition="Fraktur Mahkota">
                                <i class="fas fa-heart-broken mr-1"></i> Fraktur Mahkota
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="penyakit_modal" class="font-weight-bold text-dark">Hasil Pemeriksaan <span class="text-danger">*</span></label>
                        <div wire:ignore>
                            <select class="form-control formpemeriksaan" wire:model.defer="penyakit" id="penyakit_modal" required>
                                <option value="">Pilih Hasil Pemeriksaan</option>
                                <option value="Tanggal">Tanggal (Hilang)</option>
                                <option value="Karies">Karies</option>
                                <option value="Akar">Sisa Akar</option>
                                <option value="Tumpat">Tumpatan</option>
                                <option value="Fraktur Mahkota">Fraktur Mahkota</option>
                            </select>
                        </div>
                        @error('penyakit') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="catatan_modal" class="font-weight-bold text-dark">Catatan Pemeriksaan</label>
                        <textarea wire:model.defer="catatan" class="form-control" id="catatan_modal" rows="3" placeholder="Masukkan catatan kondisi gigi..."></textarea>
                        @error('catatan') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info px-4 shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Pemeriksaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

- [ ] **Step 2: Update JS handlers `selectTooth` and event listeners**

In the `@push('js')` block of `odontogram.blade.php`:
```javascript
function selectTooth(toothId, label) {
    $('.formgigi').val(toothId).trigger('change');
    @this.set('gigi', toothId);
    $('#modalToothTitle').text(label || toothId.replace('gg_', 'Gigi '));
    updateToothSelectionUI(toothId);
    $('#modalInputOdontogram').modal('show');
}

window.addEventListener('odontogram:saved', function() {
    $('#modalInputOdontogram').modal('hide');
});
```

- [ ] **Step 3: Commit Task 1**

```bash
git add resources/views/livewire/ralan/odontogram.blade.php
git commit -m "feat: add tooth click input modal and auto-close on save"
```
