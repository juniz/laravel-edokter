@extends('adminlte::page')

@section('title', 'Pasien IGD')

@section('content_header')
    <h1>Pasien IGD</h1>
@stop

@section('content')
    <!-- Filter Tanggal -->
    <div class="minimal-filter-section">
        <div class="filter-info">
            <span class="data-count">
                <i class="fas fa-users"></i>
                {{count($data)}} pasien
            </span>
        </div>
        <form action="{{route('igd')}}" method="GET" class="filter-form">
            <div class="filter-group" id="date-filter-group" title="Klik untuk memilih tanggal (otomatis refresh)">
                <label for="tanggal-input" class="filter-label clickable-label">
                    <i class="fas fa-calendar-alt"></i>
                    Tanggal
                </label>
                <div class="date-input-wrapper">
                    <input type="date" 
                           name="tanggal" 
                           value="{{$tanggal ?? date('Y-m-d')}}" 
                           class="minimal-date-input" 
                           max="{{date('Y-m-d')}}"
                           id="tanggal-input">
                    <span class="date-display" id="date-display">
                        {{date('d/m/Y', strtotime($tanggal ?? date('Y-m-d')))}}
                    </span>
                    <div class="loading-indicator" id="loading-indicator" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <x-adminlte-callout theme="info" title="{{$nm_poli}}">
        @php
            $config["responsive"] = true;
        @endphp
        {{-- Minimal example / fill data using the component slot --}}
        <x-adminlte-datatable id="tablePasienRalan" :heads="$heads" :config="$config" head-theme="dark" striped hoverable bordered compressed>
            @foreach($data as $row)
                <tr @if(!empty($row->diagnosa_utama)) class="bg-success" @endif >
                    <td>{{$row->no_reg}}</td>
                    <td> 
                        @php
                        $noRawat = App\Http\Controllers\Ralan\PasienRalanController::encryptData($row->no_rawat);
                        $noRM = App\Http\Controllers\Ralan\PasienRalanController::encryptData($row->no_rkm_medis);
                        @endphp
                        <a @if(!empty($row->diagnosa_utama)) class="text-white" @else class="text-primary" @endif href="{{route('ralan.pemeriksaan', ['no_rawat' => $noRawat, 'no_rm' => $noRM])}} ">
                            {{$row->nm_pasien}}
                        </a>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button id="my-dropdown" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">{{$row->no_rawat}}</button>
                            <div class="dropdown-menu" aria-labelledby="my-dropdown">
                                <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-igd">Penilaian Awal Medis IGD</button>
                                <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-medis" wire:click="$emit('awalMedis')">Penilaian Awal Medis Umum</button>
                                <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-tht">Penilaian Awal Medis THT</button>
                                <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-anak">Penilaian Awal Medis Bayi/Anak</button>
                                <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-kandungan">Penilaian Awal Medis Kandungan</button>
                                <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-dalam">Penilaian Awal Medis Penyakit Dalam</button>
                                <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-psikiatri">Penilaian Awal Medis Psikiatri</button>
                                <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-mata">Penilaian Awal Medis Mata</button>
                                <button id="{{$row->no_rawat}}" class="dropdown-item btn-persetujuan-penolakan-tindakan">Persetujuan/Penolakan Tindakan</button>
                            </div>
                        </div>
                    </td>
                    <td>{{$row->no_tlp}}</td>
                    <td>{{$row->nm_dokter}}</td>
                    <td>{{$row->stts}}</td>
                </tr>
            @endforeach
        </x-adminlte-datatable>
    </x-adminlte-callout>
    
    <x-adminlte-modal wire:ignore.self id="modal-awal-keperawatan" title="Penilaian Awal Medis Umum" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-ralan.form />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-awal-medis-tht" title="Penilaian Awal Medis THT" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-tht.form  />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-awal-medis-anak" title="Penilaian Awal Medis Anak" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-anak.form-anak  />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-awal-medis-kandungan" title="Penilaian Awal Medis Kandungan" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-kandungan.form-kandungan  />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-awal-medis-dalam" title="Penilaian Awal Medis Penyakit Dalam" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-dalam.form-dalam  />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-awal-medis-psikiatri" title="Penilaian Awal Medis Psikiatri" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-psikiatri.form-psikiatri  />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-awal-medis-mata" title="Penilaian Awal Medis Mata" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-mata.form-mata  />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-persetujuan-penolakan-tindakan" title="Persetujuan/Penolakan Tindakan" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.persetujuan-penolakan-tindakan.form />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-awal-medis-igd" title="Penilaian Awal Medis IGD" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-igd.form />
    </x-adminlte-modal>
@stop

@section('plugins.TempusDominusBs4', true)
@push('css')
<style>
    /* Minimalist Date Filter */
    .minimal-filter-section {
        margin-bottom: 1.5rem;
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .filter-info {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        margin-bottom: 0;
    }

    .data-count {
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(32, 201, 151, 0.1) 100%);
        color: #28a745;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid rgba(40, 167, 69, 0.2);
        white-space: nowrap;
    }

    .data-count i {
        font-size: 0.9rem;
    }

    .filter-form {
        display: flex;
        justify-content: flex-end;
        align-items: center;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: white;
        padding: 0.75rem 1rem;
        border-radius: 50px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }

    .filter-group:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        transform: translateY(-2px);
        border: 2px solid rgba(102, 126, 234, 0.2);
    }

    .filter-group:hover .filter-label {
        color: #667eea;
    }

    .filter-group:hover .date-display {
        color: #667eea;
        background: rgba(102, 126, 234, 0.1);
    }

    .filter-group.selected {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border: 2px solid rgba(102, 126, 234, 0.3);
        transform: scale(1.02);
    }

    .filter-group.clicking {
        transform: scale(0.98);
        box-shadow: 0 1px 5px rgba(0,0,0,0.15);
    }

    .filter-label {
        font-size: 0.85rem;
        font-weight: 500;
        color: #6c757d;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    .filter-label.clickable-label {
        cursor: pointer;
        user-select: none;
    }

    .filter-label.clickable-label:hover {
        color: #667eea;
    }

    .filter-label i {
        font-size: 0.9rem;
        color: #667eea;
        transition: all 0.2s ease;
    }

    .filter-label.clickable-label:hover i {
        transform: scale(1.1);
    }

    .date-input-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
    }

    .minimal-date-input {
        position: absolute;
        opacity: 0;
        pointer-events: auto;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: 1;
        cursor: pointer;
    }

    .date-display {
        font-size: 0.9rem;
        font-weight: 500;
        color: #495057;
        background: transparent;
        border: none;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        transition: all 0.2s ease;
        min-width: 140px;
        cursor: pointer;
        user-select: none;
    }

    .date-display:hover {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }

    .loading-indicator {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667eea;
        transition: all 0.3s ease;
    }

    .loading-indicator i {
        font-size: 1rem;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .filter-group.opening-picker .filter-label i {
        animation: pulse 1s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }

    @media (max-width: 768px) {
        .minimal-filter-section {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-form {
            justify-content: center;
        }
    }
</style>
@endpush

@push('js')
<script>
    $(document).ready(function() {
        // Popup datepicker functionality
        function openDatePicker() {
            const dateInput = document.getElementById('tanggal-input');
            
            // Add clicking effect
            $('#date-filter-group').addClass('clicking');
            setTimeout(() => {
                $('#date-filter-group').removeClass('clicking');
            }, 150);
            
            // Add loading animation
            $('#date-filter-group').addClass('opening-picker');
            
            // Try modern showPicker method first, fallback to click
            try {
                if (dateInput.showPicker) {
                    dateInput.showPicker();
                } else {
                    dateInput.focus();
                    dateInput.click();
                }
            } catch (error) {
                // Fallback for older browsers
                dateInput.focus();
                dateInput.click();
            }
            
            // Remove loading animation after a short delay
            setTimeout(() => {
                $('#date-filter-group').removeClass('opening-picker');
            }, 1000);
        }

        // Prevent manual form submission
        $('.filter-form').on('submit', function(e) {
            e.preventDefault();
            return false;
        });

        // Click handlers for opening datepicker
        $('#date-filter-group').on('click', function(e) {
            e.preventDefault();
            openDatePicker();
        });

        $('.clickable-label').on('click', function(e) {
            e.preventDefault();
            openDatePicker();
        });

        $('#date-display').on('click', function(e) {
            e.preventDefault();
            openDatePicker();
        });

        // Enhanced date input handling with auto-refresh
        $('#tanggal-input').on('change', function() {
            const selectedDate = $(this).val();
            if (selectedDate) {
                // Show loading state
                $('.filter-group').addClass('selected');
                $('#date-display').hide();
                $('#loading-indicator').show();
                $('.filter-label').html(`
                    <i class="fas fa-sync-alt fa-spin"></i>
                    Memuat data...
                `);
                
                // Update URL and refresh page
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('tanggal', selectedDate);
                
                // Add slight delay for better UX
                setTimeout(() => {
                    window.location.href = currentUrl.toString();
                }, 500);
            }
        });

        // Initialize with current date display
        const currentDate = $('#tanggal-input').val();
        if (currentDate) {
            const dateObj = new Date(currentDate);
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            const readableDate = dateObj.toLocaleDateString('id-ID', options);
            $('.filter-label').html(`
                <i class="fas fa-calendar-check"></i>
                ${readableDate}
            `);
            
            // Update date display
            const shortDate = dateObj.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            $('#date-display').text(shortDate);
            
            // Ensure loading indicator is hidden on page load
            $('#loading-indicator').hide();
            $('#date-display').show();
        }
    });
</script>
@endpush
