<div class="penandaan-area-operasi">
    <div class="form-group">
        <label for="tanggal-penandaan"><i class="fas fa-clock mr-1 text-primary"></i> Tanggal & Jam Penandaan</label>
        <input
            type="datetime-local"
            wire:model.defer="tanggalPenandaan"
            id="tanggal-penandaan"
            class="form-control @error('tanggalPenandaan') is-invalid @enderror"
            required
        >
        @error('tanggalPenandaan')
            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="prosedur-operasi">Prosedur operasi</label>
        <textarea
            wire:model.defer="prosedurOperasi"
            id="prosedur-operasi"
            class="form-control @error('prosedurOperasi') is-invalid @enderror"
            rows="3"
            required
            placeholder="Tuliskan prosedur operasi yang akan dilakukan"
        ></textarea>
        @error('prosedurOperasi')
            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
        @enderror
    </div>

    <div class="penandaan-instructions" aria-label="Petunjuk penandaan">
        <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
        <span>Ketuk bagian tubuh untuk menambahkan penanda merah bernomor.</span>
    </div>

    <div wire:ignore id="penandaan-area-operasi-canvas-root">
        <div class="penandaan-mobile-toolbar" aria-label="Navigasi diagram">
            <button type="button" class="btn btn-outline-secondary penandaan-nav" data-direction="-1" aria-label="Diagram sebelumnya">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
                <span>Sebelumnya</span>
            </button>
            <strong class="penandaan-active-label" aria-live="polite">Badan depan</strong>
            <button type="button" class="btn btn-outline-secondary penandaan-nav" data-direction="1" aria-label="Diagram berikutnya">
                <span>Berikutnya</span>
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
        </div>

        <div class="penandaan-diagram-grid">
            @php
                $diagramViews = [
                    ['body_front', 'Badan depan', 'Diagram badan depan', true],
                    ['body_back', 'Badan belakang', 'Diagram badan belakang', true],
                    ['head_left_profile', 'Kepala profil kiri', 'Diagram kepala profil kiri', false],
                    ['head_right_profile', 'Kepala profil kanan', 'Diagram kepala profil kanan', false],
                    ['head_front', 'Kepala depan', 'Diagram kepala depan', false],
                    ['head_back', 'Kepala belakang', 'Diagram kepala belakang', false],
                    ['hand_left_palm', 'Telapak tangan kiri', 'Diagram telapak tangan kiri', false],
                    ['hand_right_palm', 'Telapak tangan kanan', 'Diagram telapak tangan kanan', false],
                    ['hand_left_dorsal', 'Punggung tangan kiri', 'Diagram punggung tangan kiri', false],
                    ['hand_right_dorsal', 'Punggung tangan kanan', 'Diagram punggung tangan kanan', false],
                    ['foot_left_plantar', 'Telapak kaki kiri', 'Diagram telapak kaki kiri', false],
                    ['foot_right_plantar', 'Telapak kaki kanan', 'Diagram telapak kaki kanan', false],
                    ['foot_left_dorsal', 'Punggung kaki kiri', 'Diagram punggung kaki kiri', false],
                    ['foot_right_dorsal', 'Punggung kaki kanan', 'Diagram punggung kaki kanan', false],
                ];
            @endphp

            @foreach ($diagramViews as [$key, $label, $ariaLabel, $isBody])
                <figure class="penandaan-diagram{{ $isBody ? ' penandaan-diagram--body' : '' }}{{ $loop->first ? ' is-active' : '' }}" data-diagram-panel="{{ $key }}">
                    <figcaption>{{ $label }}</figcaption>
                    <div class="penandaan-canvas-frame">
                        <canvas data-diagram="{{ $key }}" aria-label="{{ $ariaLabel }}">
                            Browser Anda tidak mendukung Canvas.
                        </canvas>
                        <span class="penandaan-canvas-status" data-status-for="{{ $key }}">Memuat diagram…</span>
                    </div>
                </figure>
            @endforeach
        </div>
    </div>

    @error('gambarPenandaanBase64')
        <div class="alert alert-danger mt-3 mb-0" role="alert">{{ $message }}</div>
    @enderror
    @error('operator')
        <div class="alert alert-danger mt-3 mb-0" role="alert">{{ $message }}</div>
    @enderror

    <div class="penandaan-actions d-flex flex-wrap justify-content-between align-items-center">
        <div class="penandaan-reset-group mb-2 mb-sm-0">
            <button type="button" id="reset-penandaan-aktif" class="btn btn-outline-warning btn-sm" disabled title="Reset penandaan pada diagram yang tampil saat ini">
                <i class="fas fa-undo mr-1" aria-hidden="true"></i>
                Reset Diagram Ini
            </button>
            <button type="button" id="reset-semua-penandaan" class="btn btn-outline-danger btn-sm ml-2" disabled title="Hapus semua penandaan dari seluruh diagram">
                <i class="fas fa-trash-alt mr-1" aria-hidden="true"></i>
                Reset Semua
            </button>
        </div>
        <div class="penandaan-submit-group d-flex align-items-center">
            @if($modeEdit)
                <button type="button" id="ubah-penandaan" class="btn btn-warning" disabled>
                    <i class="fas fa-save mr-1" aria-hidden="true"></i>
                    <span class="penandaan-btn-label">Ubah Penandaan</span>
                </button>
                <button type="button" wire:click="batalEdit" class="btn btn-secondary ml-2" id="batal-penandaan">
                    <i class="fas fa-times mr-1" aria-hidden="true"></i>
                    Batal
                </button>
            @else
                <button type="button" id="simpan-penandaan" class="btn btn-primary" disabled>
                    <i class="fas fa-save mr-1" aria-hidden="true"></i>
                    <span class="penandaan-btn-label">Simpan</span>
                </button>
            @endif
        </div>
    </div>

    <div class="card card-outline card-info mt-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history mr-1"></i> Riwayat Penandaan Area Operasi</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 170px;">Tanggal Penandaan</th>
                        <th>Prosedur Operasi</th>
                        <th style="width: 200px;">Dokter Operator</th>
                        <th style="width: 180px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $index => $item)
                        @php
                            $item = (object) $item;
                            $itemId = $item->id ?? null;
                            $itemTanggal = !empty($item->tanggal_penandaan) ? \Carbon\Carbon::parse($item->tanggal_penandaan)->format('d-m-Y H:i') : '-';
                            $itemProsedur = $item->prosedur_operasi ?? '-';
                            $itemDokter = $item->nm_dokter ?? $item->nama_operator ?? '-';
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $itemTanggal }}</td>
                            <td>{{ $itemProsedur }}</td>
                            <td>{{ $itemDokter }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" wire:click="edit({{ $itemId }})" class="btn btn-warning" title="Edit Penandaan">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" wire:click="confirmHapus({{ $itemId }})" class="btn btn-danger" title="Hapus Penandaan">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <a href="{{ route('penandaan-area-operasi.cetak', ['no_rawat' => $noRawat, 'id' => $itemId]) }}" target="_blank" class="btn btn-info" title="Cetak Form">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="{{ route('penandaan-area-operasi.download-pdf', ['no_rawat' => $noRawat, 'id' => $itemId]) }}" target="_blank" class="btn btn-secondary" title="Download PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Belum ada riwayat penandaan area operasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('css')
<style>
    .penandaan-area-operasi {
        --penandaan-ink: #24313d;
        --penandaan-muted: #647483;
        --penandaan-paper: #f8faf9;
        --penandaan-line: #d6dee3;
        color: var(--penandaan-ink);
    }

    .penandaan-instructions {
        align-items: center;
        background: #eef7f6;
        border: 1px solid #c6e2df;
        color: #245852;
        display: flex;
        gap: .65rem;
        margin-bottom: 1rem;
        padding: .7rem .85rem;
    }

    .penandaan-instructions .fas {
        color: #c62f3f;
    }

    .penandaan-mobile-toolbar {
        align-items: center;
        border-bottom: 1px solid var(--penandaan-line);
        display: grid;
        gap: .5rem;
        grid-template-columns: minmax(44px, auto) minmax(0, 1fr) minmax(44px, auto);
        margin-bottom: .85rem;
        padding-bottom: .85rem;
    }

    .penandaan-mobile-toolbar .penandaan-nav {
        align-items: center;
        display: inline-flex;
        gap: .4rem;
        justify-content: center;
        min-height: 44px;
        min-width: 44px;
    }

    .penandaan-active-label {
        font-size: .92rem;
        line-height: 1.2;
        text-align: center;
    }

    .penandaan-diagram-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: minmax(0, 1fr);
    }

    .penandaan-diagram {
        display: none;
        margin: 0;
        min-width: 0;
    }

    .penandaan-diagram.is-active {
        display: block;
    }

    .penandaan-diagram figcaption {
        color: var(--penandaan-muted);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .06em;
        margin-bottom: .4rem;
        text-transform: uppercase;
    }

    .penandaan-canvas-frame {
        align-items: center;
        background:
            linear-gradient(90deg, rgba(36, 49, 61, .035) 1px, transparent 1px),
            linear-gradient(rgba(36, 49, 61, .035) 1px, transparent 1px),
            var(--penandaan-paper);
        background-size: 18px 18px;
        border: 1px solid var(--penandaan-line);
        display: flex;
        justify-content: center;
        min-height: 360px;
        overflow: hidden;
        position: relative;
    }

    .penandaan-canvas-frame canvas {
        cursor: crosshair;
        display: block;
        height: auto;
        max-height: min(68vh, 760px);
        max-width: 100%;
        touch-action: none;
        width: 100%;
    }

    .penandaan-canvas-frame canvas:focus-visible {
        outline: 3px solid rgba(0, 123, 255, .35);
        outline-offset: -3px;
    }

    .penandaan-canvas-status {
        background: rgba(248, 250, 249, .94);
        color: var(--penandaan-muted);
        font-size: .84rem;
        left: 50%;
        padding: .4rem .6rem;
        pointer-events: none;
        position: absolute;
        top: 50%;
        transform: translate(-50%, -50%);
        white-space: nowrap;
    }

    .penandaan-canvas-status.is-hidden {
        display: none;
    }

    .penandaan-canvas-status.is-error {
        color: #a3202f;
    }

    .penandaan-actions {
        border-top: 1px solid var(--penandaan-line);
        display: flex;
        margin-top: 1.1rem;
        padding-top: 1rem;
    }

    #simpan-penandaan,
    #ubah-penandaan,
    #batal-penandaan {
        min-height: 44px;
        min-width: 120px;
    }

    @media (max-width: 420px) {
        .penandaan-mobile-toolbar .penandaan-nav span {
            display: none;
        }

        .penandaan-canvas-frame {
            min-height: 420px;
        }
    }

    @media (min-width: 768px) {
        .penandaan-mobile-toolbar {
            display: none;
        }

        .penandaan-diagram-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .penandaan-diagram,
        .penandaan-diagram.is-active {
            display: block;
        }

        .penandaan-diagram--body {
            grid-column: span 2;
        }

        .penandaan-canvas-frame {
            min-height: 260px;
        }

        .penandaan-diagram--body .penandaan-canvas-frame {
            min-height: 520px;
        }

        .penandaan-canvas-frame canvas {
            max-height: 360px;
        }

        .penandaan-diagram--body .penandaan-canvas-frame canvas {
            max-height: 600px;
        }
    }
</style>
@endpush

@push('js')
<script>
    (function () {
        'use strict';

        var isMale = @json($jenisKelamin === 'L');
        var diagrams = [
            { key: 'body_front', label: 'Badan depan', asset: isMale ? '/img/penandaan-area-operasi/body-front-male.svg' : '/img/penandaan-area-operasi/body-front.svg' },
            { key: 'body_back', label: 'Badan belakang', asset: isMale ? '/img/penandaan-area-operasi/body-back-male.svg' : '/img/penandaan-area-operasi/body-back.svg' },
            { key: 'head_left_profile', label: 'Kepala profil kiri', asset: '/img/penandaan-area-operasi/head-left-profile.svg' },
            { key: 'head_right_profile', label: 'Kepala profil kanan', asset: '/img/penandaan-area-operasi/head-right-profile.svg' },
            { key: 'head_front', label: 'Kepala depan', asset: '/img/penandaan-area-operasi/head-front.svg' },
            { key: 'head_back', label: 'Kepala belakang', asset: '/img/penandaan-area-operasi/head-back.svg' },
            { key: 'hand_left_palm', label: 'Telapak tangan kiri', asset: '/img/penandaan-area-operasi/hand-left-palm.svg' },
            { key: 'hand_right_palm', label: 'Telapak tangan kanan', asset: '/img/penandaan-area-operasi/hand-right-palm.svg' },
            { key: 'hand_left_dorsal', label: 'Punggung tangan kiri', asset: '/img/penandaan-area-operasi/hand-left-dorsal.svg' },
            { key: 'hand_right_dorsal', label: 'Punggung tangan kanan', asset: '/img/penandaan-area-operasi/hand-right-dorsal.svg' },
            { key: 'foot_left_plantar', label: 'Telapak kaki kiri', asset: '/img/penandaan-area-operasi/foot-left-plantar.svg' },
            { key: 'foot_right_plantar', label: 'Telapak kaki kanan', asset: '/img/penandaan-area-operasi/foot-right-plantar.svg' },
            { key: 'foot_left_dorsal', label: 'Punggung kaki kiri', asset: '/img/penandaan-area-operasi/foot-left-dorsal.svg' },
            { key: 'foot_right_dorsal', label: 'Punggung kaki kanan', asset: '/img/penandaan-area-operasi/foot-right-dorsal.svg' }
        ];

        var root = document.getElementById('penandaan-area-operasi-canvas-root');
        var resetActiveButton = document.getElementById('reset-penandaan-aktif');
        var resetAllButton = document.getElementById('reset-semua-penandaan');

        if (!root || root.dataset.initialized === 'true') {
            return;
        }

        root.dataset.initialized = 'true';

        var canvasStates = {};
        var savedPinCounts = {};
        var activeIndex = 0;
        var nextPinNumber = 1;
        var readyCount = 0;
        var initialSavedImages = @json($savedGambarPenandaanBase64);
        var pendingSavedImages = (initialSavedImages && typeof initialSavedImages === 'object' && Object.keys(initialSavedImages).length > 0)
            ? initialSavedImages
            : null;

        function getSubmitButton() {
            return document.getElementById('simpan-penandaan') || document.getElementById('ubah-penandaan');
        }

        function setSubmitButtonDisabled(disabled) {
            var btn = getSubmitButton();
            if (btn) {
                btn.disabled = disabled;
            }
        }

        function setStatus(key, message, isError) {
            var status = root.querySelector('[data-status-for="' + key + '"]');

            if (!status) {
                return;
            }

            if (!message) {
                status.textContent = '';
                status.classList.add('is-hidden');
                status.classList.remove('is-error');
                return;
            }

            status.textContent = message;
            status.classList.remove('is-hidden');
            status.classList.toggle('is-error', Boolean(isError));
        }

        function setCanvasSize(canvas, image) {
            var naturalWidth = image.naturalWidth || image.width || 800;
            var naturalHeight = image.naturalHeight || image.height || 1000;
            var scale = 1200 / Math.max(naturalWidth, naturalHeight);

            canvas.width = Math.max(1, Math.round(naturalWidth * scale));
            canvas.height = Math.max(1, Math.round(naturalHeight * scale));
        }

        function drawBase(state) {
            state.context.save();
            state.context.fillStyle = '#ffffff';
            state.context.fillRect(0, 0, state.canvas.width, state.canvas.height);

            if (state.background) {
                state.context.drawImage(state.background, 0, 0, state.canvas.width, state.canvas.height);
            }

            state.context.restore();
        }

        function drawSavedImage(state, image) {
            drawBase(state);
            state.context.drawImage(image, 0, 0, state.canvas.width, state.canvas.height);
        }

        function countRedPins(state) {
            var pixels;

            try {
                pixels = state.context.getImageData(0, 0, state.canvas.width, state.canvas.height).data;
            } catch (error) {
                return 0;
            }

            var width = state.canvas.width;
            var height = state.canvas.height;
            var visited = new Uint8Array(width * height);
            var count = 0;

            function isPinPixel(index) {
                var offset = index * 4;

                return pixels[offset] > 150 &&
                    pixels[offset + 1] < 115 &&
                    pixels[offset + 2] < 125 &&
                    pixels[offset + 3] > 180;
            }

            for (var y = 0; y < height; y += 1) {
                for (var x = 0; x < width; x += 1) {
                    var start = (y * width) + x;

                    if (visited[start] || !isPinPixel(start)) {
                        continue;
                    }

                    var stack = [start];
                    var componentSize = 0;
                    visited[start] = 1;

                    while (stack.length) {
                        var current = stack.pop();
                        var currentX = current % width;
                        var currentY = Math.floor(current / width);
                        componentSize += 1;

                        var neighbors = [
                            currentX > 0 ? current - 1 : -1,
                            currentX + 1 < width ? current + 1 : -1,
                            currentY > 0 ? current - width : -1,
                            currentY + 1 < height ? current + width : -1
                        ];

                        for (var i = 0; i < neighbors.length; i += 1) {
                            var neighbor = neighbors[i];

                            if (neighbor >= 0 && !visited[neighbor] && isPinPixel(neighbor)) {
                                visited[neighbor] = 1;
                                stack.push(neighbor);
                            }
                        }
                    }

                    if (componentSize >= 80) {
                        count += 1;
                    }
                }
            }

            return count;
        }

        function updateResetButtonsState() {
            var activeKey = diagrams[activeIndex] ? diagrams[activeIndex].key : null;
            var activeState = activeKey ? canvasStates[activeKey] : null;
            var activeHasPins = activeState ? countRedPins(activeState) > 0 : false;

            var totalPins = Object.keys(canvasStates).reduce(function (total, key) {
                return total + countRedPins(canvasStates[key]);
            }, 0);

            if (resetActiveButton) {
                resetActiveButton.disabled = !activeHasPins;
            }
            if (resetAllButton) {
                resetAllButton.disabled = totalPins === 0;
            }
        }

        function refreshNextPinNumber() {
            var savedCount = Object.keys(savedPinCounts).reduce(function (total, key) {
                return total + savedPinCounts[key];
            }, 0);

            nextPinNumber = savedCount + 1;
        }

        function addPin(state, event) {
            if (!state.ready || event.button > 0) {
                return;
            }

            var rect = state.canvas.getBoundingClientRect();

            if (!rect.width || !rect.height) {
                return;
            }

            var scaleX = state.canvas.width / rect.width;
            var scaleY = state.canvas.height / rect.height;
            var x = (event.clientX - rect.left) * scaleX;
            var y = (event.clientY - rect.top) * scaleY;
            var radius = Math.max(16, Math.min(state.canvas.width, state.canvas.height) * .025);
            var number = nextPinNumber;

            state.context.save();
            state.context.beginPath();
            state.context.arc(x, y, radius, 0, Math.PI * 2);
            state.context.fillStyle = '#c6283d';
            state.context.fill();
            state.context.lineWidth = Math.max(3, radius * .16);
            state.context.strokeStyle = '#8f1728';
            state.context.stroke();
            state.context.fillStyle = '#ffffff';
            state.context.font = '700 ' + Math.round(radius * 1.15) + 'px sans-serif';
            state.context.textAlign = 'center';
            state.context.textBaseline = 'middle';
            state.context.fillText(String(number), x, y + (radius * .04));
            state.context.restore();

            savedPinCounts[state.key] = (savedPinCounts[state.key] || 0) + 1;
            nextPinNumber += 1;
            updateResetButtonsState();
        }

        function markCanvasReady(state) {
            if (state.ready) {
                return;
            }

            state.ready = true;
            readyCount += 1;
            setStatus(state.key, '', false);

            if (readyCount === diagrams.length) {
                setSubmitButtonDisabled(false);

                if (pendingSavedImages) {
                    var payload = pendingSavedImages;
                    pendingSavedImages = null;
                    loadSavedImages(payload);
                } else {
                    updateResetButtonsState();
                }
            }
        }

        function initializeCanvas(diagram) {
            var canvas = root.querySelector('canvas[data-diagram="' + diagram.key + '"]');
            var context = canvas.getContext('2d');
            var state = {
                key: diagram.key,
                canvas: canvas,
                context: context,
                background: null,
                ready: false
            };
            var background = new Image();

            canvasStates[diagram.key] = state;
            canvas.addEventListener('pointerup', function (event) {
                addPin(state, event);
            });

            background.onload = function () {
                state.background = background;
                setCanvasSize(canvas, background);
                drawBase(state);
                markCanvasReady(state);
            };

            background.onerror = function () {
                canvas.width = 800;
                canvas.height = 1200;
                drawBase(state);
                markCanvasReady(state);
                setStatus(diagram.key, 'Latar anatomi gagal dimuat.', true);
            };

            background.src = diagram.asset;
        }

        function loadSavedImages(images) {
            if (!images || typeof images !== 'object') {
                return Promise.resolve();
            }

            if (readyCount !== diagrams.length) {
                pendingSavedImages = images;
                return Promise.resolve();
            }

            setSubmitButtonDisabled(true);
            savedPinCounts = {};

            var loaders = diagrams.map(function (diagram) {
                var dataUrl = images[diagram.key];
                var state = canvasStates[diagram.key];

                if (typeof dataUrl !== 'string' || dataUrl.indexOf('data:image/png;base64,') !== 0) {
                    return Promise.resolve();
                }

                return new Promise(function (resolve) {
                    var savedImage = new Image();

                    savedImage.onload = function () {
                        drawSavedImage(state, savedImage);
                        savedPinCounts[diagram.key] = countRedPins(state);
                        setStatus(diagram.key, '', false);
                        resolve();
                    };

                    savedImage.onerror = function () {
                        drawBase(state);
                        savedPinCounts[diagram.key] = 0;
                        setStatus(diagram.key, 'Gambar tersimpan tidak dapat dibaca.', true);
                        resolve();
                    };

                    savedImage.src = dataUrl;
                });
            });

            return Promise.all(loaders).then(function () {
                refreshNextPinNumber();
                setSubmitButtonDisabled(false);
                updateResetButtonsState();
            });
        }

        function showDiagram(index) {
            var panels = root.querySelectorAll('[data-diagram-panel]');
            var label = root.querySelector('.penandaan-active-label');

            activeIndex = (index + diagrams.length) % diagrams.length;

            Array.prototype.forEach.call(panels, function (panel, panelIndex) {
                panel.classList.toggle('is-active', panelIndex === activeIndex);
            });

            if (label) {
                label.textContent = diagrams[activeIndex].label;
            }

            updateResetButtonsState();
        }

        function resetAll() {
            diagrams.forEach(function (diagram) {
                var state = canvasStates[diagram.key];
                if (state) {
                    drawBase(state);
                    setStatus(diagram.key, '', false);
                }
            });

            savedPinCounts = {};
            nextPinNumber = 1;
            updateResetButtonsState();
        }

        window.PenandaanAreaOperasi = {
            exportImages: function () {
                return diagrams.reduce(function (images, diagram) {
                    images[diagram.key] = canvasStates[diagram.key].canvas.toDataURL('image/png');
                    return images;
                }, {});
            },
            loadImages: loadSavedImages,
            resetAll: resetAll
        };

        Array.prototype.forEach.call(root.querySelectorAll('.penandaan-nav'), function (button) {
            button.addEventListener('click', function () {
                showDiagram(activeIndex + Number(button.dataset.direction));
            });
        });

        if (resetActiveButton) {
            resetActiveButton.addEventListener('click', function () {
                var activeKey = diagrams[activeIndex] ? diagrams[activeIndex].key : null;
                var activeState = activeKey ? canvasStates[activeKey] : null;

                if (!activeState) {
                    return;
                }

                drawBase(activeState);
                savedPinCounts[activeKey] = 0;
                refreshNextPinNumber();
                updateResetButtonsState();
                setStatus(activeKey, '', false);
            });
        }

        if (resetAllButton) {
            resetAllButton.addEventListener('click', resetAll);
        }

        document.addEventListener('click', function (event) {
            var target = event.target ? event.target.closest('#simpan-penandaan, #ubah-penandaan') : null;
            if (!target) {
                return;
            }

            var isEdit = target.id === 'ubah-penandaan';
            var action = isEdit ? 'update' : 'simpan';

            if (target.disabled && target.dataset.saving === 'true') {
                return;
            }

            var icon = target.querySelector('.fas');
            var label = target.querySelector('.penandaan-btn-label') || target.querySelector('span');
            var originalIconClass = icon ? icon.className : 'fas fa-save mr-1';
            var originalText = label ? label.textContent : (isEdit ? 'Ubah Penandaan' : 'Simpan');

            target.disabled = true;
            target.dataset.saving = 'true';

            if (icon) {
                icon.className = 'fas fa-spinner fa-spin mr-1';
            }
            if (label) {
                label.textContent = isEdit ? 'Menyimpan perubahan...' : 'Menyimpan...';
            }

            function restoreButton() {
                target.disabled = false;
                delete target.dataset.saving;
                if (icon) {
                    icon.className = originalIconClass;
                }
                if (label) {
                    label.textContent = originalText;
                }
            }

            var images = window.PenandaanAreaOperasi.exportImages();
            @this.set('gambarPenandaanBase64', images, false);

            if (typeof @this.call === 'function') {
                var callResult = @this.call(action);
                if (callResult && typeof callResult.then === 'function') {
                    callResult.then(restoreButton).catch(restoreButton);
                } else {
                    setTimeout(restoreButton, 1500);
                }
            } else {
                setTimeout(restoreButton, 1500);
            }
        });

        diagrams.forEach(initializeCanvas);
        showDiagram(0);

        Livewire.on('loadPenandaanCanvases', function (images) {
            loadSavedImages(images);
        });

        Livewire.on('resetAllCanvases', function () {
            resetAll();
        });

        if (window.Livewire && typeof window.Livewire.hook === 'function') {
            window.Livewire.hook('message.processed', function () {
                if (readyCount === diagrams.length) {
                    var btn = getSubmitButton();
                    if (btn && btn.dataset.saving !== 'true') {
                        btn.disabled = false;
                    }
                }
            });
        }
    })();
</script>
@endpush
