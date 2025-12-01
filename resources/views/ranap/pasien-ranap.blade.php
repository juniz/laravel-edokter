@extends('adminlte::page')

@section('title', 'Pasien Ranap')

@section('content_header')
    <h1>Pasien Ranap</h1>
@stop

@section('content')
<!-- Filter Card -->
<x-adminlte-card theme="info" title="Filter Pencarian Pasien Ranap" icon="fas fa-filter" collapsible>
    @php
        $config = ['format' => 'YYYY-MM-DD'];
        $statusAktif = request('status') ?? 'belum_pulang';
        $tanggalMulaiAktif = request('tanggal_mulai') ?? date('Y-m-01');
        $tanggalAkhirAktif = request('tanggal_akhir') ?? date('Y-m-d');
    @endphp
    <form action="{{route('ranap.pasien')}}" method="GET" id="filterForm">
        <div class="row {{ $statusAktif == 'belum_pulang' ? 'filter-row-compact' : '' }}">
            <div class="col-12 col-md-6 col-lg-3 mb-3 mb-md-0" id="statusWrapper">
                <label for="status" class="form-label">
                    <i class="fas fa-user-check text-info"></i> Status Pasien
                </label>
                <x-adminlte-select name="status" id="status">
                    <option value="belum_pulang" {{ $statusAktif == 'belum_pulang' ? 'selected' : '' }}>Belum Pulang</option>
                    <option value="sudah_pulang" {{ $statusAktif == 'sudah_pulang' ? 'selected' : '' }}>Sudah Pulang</option>
                </x-adminlte-select>
                <small class="form-text text-muted">Filter berdasarkan status pasien</small>
            </div>
            <div class="col-12 col-md-6 col-lg-4 mb-3 mb-md-0 tanggal-filter-wrapper" id="tanggalMulaiWrapper">
                <label for="tanggal_mulai" class="form-label">
                    <i class="fas fa-calendar-alt text-primary"></i> Tanggal Mulai
                </label>
                <x-adminlte-input-date name="tanggal_mulai" id="tanggal_mulai" value="{{ $tanggalMulaiAktif }}" :config="$config" placeholder="Pilih Tanggal Mulai...">
                </x-adminlte-input-date>
                <small class="form-text text-muted">Tanggal awal periode pencarian</small>
            </div>
            <div class="col-12 col-md-6 col-lg-4 mb-3 mb-md-0 tanggal-filter-wrapper" id="tanggalAkhirWrapper">
                <label for="tanggal_akhir" class="form-label">
                    <i class="fas fa-calendar-check text-success"></i> Tanggal Akhir
                </label>
                <x-adminlte-input-date name="tanggal_akhir" id="tanggal_akhir" value="{{ $tanggalAkhirAktif }}" :config="$config" placeholder="Pilih Tanggal Akhir...">
                    <x-slot name="appendSlot">
                        <x-adminlte-button class="btn-sm" type="submit" theme="primary" icon="fas fa-lg fa-search" style="display: none;"/>
                    </x-slot>
                </x-adminlte-input-date>
                <small class="form-text text-muted">Tanggal akhir periode pencarian</small>
            </div>
            <div class="col-12 col-md-6 col-lg-1 d-flex align-items-end mb-3 mb-md-0" id="resetWrapper">
                <button type="button" class="btn btn-secondary btn-sm w-100" id="resetFilter" title="Reset Filter">
                    <i class="fas fa-redo"></i>
                </button>
            </div>
        </div>
        <!-- Info Filter Aktif -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="alert alert-info mb-0 py-2">
                    <div class="d-flex flex-wrap align-items-center" style="gap: 15px;">
                        <div>
                            <i class="fas fa-info-circle"></i> <strong>Filter Aktif:</strong>
                        </div>
                        <div>
                            <span class="badge badge-info">
                                <i class="fas fa-user-check"></i> Status: {{ $statusAktif == 'belum_pulang' ? 'Belum Pulang' : 'Sudah Pulang' }}
                            </span>
                        </div>
                        @if($statusAktif == 'sudah_pulang')
                        <div>
                            <span class="badge badge-primary">
                                <i class="fas fa-calendar-alt"></i> Periode: {{ date('d/m/Y', strtotime($tanggalMulaiAktif)) }} - {{ date('d/m/Y', strtotime($tanggalAkhirAktif)) }}
                            </span>
                        </div>
                        @endif
                        <div class="ml-auto">
                            <span class="badge badge-success">
                                <i class="fas fa-users"></i> Total: {{ count($data) }} Pasien
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-adminlte-card>

<x-adminlte-callout theme="info" >
    @php
        $config["responsive"] = true;
    @endphp
    {{-- Minimal example / fill data using the component slot --}}
    <x-adminlte-datatable id="tablePasienRanap" :heads="$heads" :config="$config" head-theme="dark" striped hoverable bordered compressed>
        @foreach($data as $row)
            @php
                $noRawat = App\Http\Controllers\Ranap\PasienRanapController::encryptData($row->no_rawat);
                $noRM = App\Http\Controllers\Ranap\PasienRanapController::encryptData($row->no_rkm_medis);
            @endphp
            <tr>
                <td> 
                    <a class="text-primary" href="{{route('ranap.pemeriksaan', ['no_rawat' => $noRawat, 'no_rm' => $noRM, 'bangsal' => $row->kd_bangsal])}}">
                        {{$row->nm_pasien}}
                    </a>
                </td>
                <td>
                    <div class="dropdown">
                        <button id="my-dropdown-{{$row->no_rawat}}" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">{{$row->no_rawat}}</button>
                        <div class="dropdown-menu" aria-labelledby="my-dropdown-{{$row->no_rawat}}">
                            <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-medis-ranap"> Penilaian Awal Medis Ranap</button>
                            <a class="dropdown-item" href="{{route('ralan.pemeriksaan', ['no_rawat' => $noRawat, 'no_rm' => $noRM])}}">Pemeriksaan Ralan</a>
                        </div>
                    </div>
                </td>
                <td>{{$row->no_rkm_medis}}</td>
                <td>{{$row->nm_bangsal}}</td>
                <td>{{$row->kd_kamar}}</td>
                <td>{{$row->tgl_masuk}}</td>
                <td>{{$row->png_jawab}}</td>
            </tr>
        @endforeach
    </x-adminlte-datatable>
    
</x-adminlte-callout>
<x-adminlte-modal wire:ignore.self id="modal-awal-medis-ranap" title=" Medis Ranap" size="xl" v-centered static-backdrop scrollable>
    <livewire:component.awal-medis-ranap.form />
</x-adminlte-modal>
@stop

@section('plugins.TempusDominusBs4', true)
@section('css')
<style>
    .dropdown-menu .dropdown-item {
        color: #212529;
    }
    
    .dropdown-menu .dropdown-item:hover,
    .dropdown-menu .dropdown-item:focus {
        color: #212529 !important;
        background-color: #f8f9fa;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    
    .form-label i {
        margin-right: 5px;
    }
    
    .form-text {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .alert-info .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
        }
        
        .alert-info .ml-auto {
            margin-left: 0 !important;
            margin-top: 10px;
        }
        
        .form-label {
            font-size: 0.8rem;
        }
    }
    
    @media (max-width: 576px) {
        .badge {
            font-size: 0.7rem;
            padding: 0.35em 0.65em;
        }
        
        .alert {
            padding: 0.5rem;
        }
    }
    
    /* Card filter styling */
    .card-header {
        background-color: #17a2b8 !important;
        color: white !important;
    }
    
    .card-header * {
        color: white !important;
    }
    
    .card-header .card-title {
        color: white !important;
        font-weight: 600;
    }
    
    .card-header .card-title i {
        margin-right: 8px;
        color: white !important;
    }
    
    .card-header .btn-link {
        color: white !important;
    }
    
    .card-header .btn-link:hover,
    .card-header .btn-link:focus {
        color: white !important;
    }
    
    /* Ensure card header text is visible */
    .card.card-info .card-header,
    .card.card-info .card-header .card-title,
    .card.card-info .card-header .card-title span,
    .card.card-info .card-header a,
    .card.card-info .card-header button {
        color: white !important;
    }
    
    /* Loading overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .loading-overlay .spinner-border {
        width: 3rem;
        height: 3rem;
    }
    
    /* Badge styling */
    .badge {
        padding: 0.5em 0.75em;
        font-size: 0.85rem;
    }
    
    .badge i {
        margin-right: 5px;
    }
    
    /* Tanggal filter wrapper styling */
    .tanggal-filter-wrapper {
        transition: opacity 0.3s ease, max-height 0.3s ease;
        overflow: visible !important; /* Pastikan date picker tidak terpotong */
        position: relative;
        z-index: 1;
    }
    
    .tanggal-filter-wrapper.hidden {
        display: none !important;
    }
    
    .tanggal-filter-wrapper.disabled {
        opacity: 0.5;
        pointer-events: none;
    }
    
    /* Fix z-index untuk date picker TempusDominusBs4 */
    .tanggal-filter-wrapper .input-group {
        position: relative;
        z-index: 1;
    }
    
    /* Pastikan date picker dropdown tidak terpotong */
    .bootstrap-datetimepicker-widget {
        z-index: 1060 !important; /* Lebih tinggi dari modal (1050) dan card */
        position: absolute !important;
    }
    
    /* Pastikan card tidak memotong date picker */
    .card {
        overflow: visible !important;
    }
    
    .card-body {
        overflow: visible !important;
    }
    
    /* Pastikan form tidak memotong date picker */
    #filterForm {
        overflow: visible !important;
        position: relative;
    }
    
    #filterForm .row {
        overflow: visible !important;
    }
    
    /* Layout adjustment saat filter tanggal hidden */
    .filter-row-compact {
        display: flex;
        align-items: flex-end;
        gap: 15px;
    }
    
    .filter-row-compact #statusWrapper {
        flex: 0 0 auto;
        min-width: 250px;
        max-width: 350px;
        margin-bottom: 0 !important;
    }
    
    .filter-row-compact #resetWrapper {
        flex: 0 0 auto;
        width: auto;
        min-width: 50px;
        margin-bottom: 0 !important;
        margin-left: auto;
    }
    
    .filter-row-compact #resetWrapper .btn {
        min-width: 50px;
        padding: 0.375rem 0.75rem;
    }
    
    @media (max-width: 991px) {
        .filter-row-compact {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-row-compact #statusWrapper {
            max-width: 100%;
            margin-bottom: 1rem !important;
        }
        
        .filter-row-compact #resetWrapper {
            margin-left: 0;
            margin-bottom: 0 !important;
        }
    }
    
    @media (min-width: 992px) {
        .filter-row-compact #statusWrapper {
            min-width: 300px;
        }
    }
</style>
@stop
@section('js')
<script>
    $(function() {
        var form = $('#filterForm');
        var isSubmitting = false;
        
        // Function to toggle tanggal filter visibility
        function toggleTanggalFilter() {
            var status = $('#status').val();
            var $tanggalMulaiWrapper = $('#tanggalMulaiWrapper');
            var $tanggalAkhirWrapper = $('#tanggalAkhirWrapper');
            var $filterRow = $('#filterForm .row').first();
            
            if (status === 'belum_pulang') {
                // Hide tanggal filter saat status belum pulang
                $tanggalMulaiWrapper.addClass('hidden');
                $tanggalAkhirWrapper.addClass('hidden');
                // Tambahkan class untuk layout compact
                $filterRow.addClass('filter-row-compact');
            } else {
                // Show tanggal filter saat status sudah pulang
                $tanggalMulaiWrapper.removeClass('hidden');
                $tanggalAkhirWrapper.removeClass('hidden');
                // Hapus class layout compact
                $filterRow.removeClass('filter-row-compact');
            }
        }
        
        // Initialize tanggal filter visibility on page load
        toggleTanggalFilter();
        
        // Auto-submit ketika status berubah
        $('#status').on('change', function() {
            toggleTanggalFilter();
            if (!isSubmitting) {
                isSubmitting = true;
                form.submit();
            }
        });
        
        // Function untuk submit form saat tanggal berubah
        function submitOnDateChange() {
            // Hanya submit jika status sudah pulang (tanggal filter terlihat)
            var status = $('#status').val();
            if (status === 'sudah_pulang' && !isSubmitting) {
                isSubmitting = true;
                // Delay kecil untuk memastikan value sudah ter-update
                setTimeout(function() {
                    form.submit();
                }, 150);
            }
        }
        
        // Auto-submit ketika tanggal mulai berubah (untuk TempusDominusBs4)
        // Event 'update.td' adalah event khusus TempusDominusBs4 yang dipanggil saat tanggal berubah
        $(document).on('update.td', '#tanggal_mulai', function() {
            submitOnDateChange();
        });
        
        $(document).on('changeDate', '#tanggal_mulai', function() {
            submitOnDateChange();
        });
        
        // Auto-submit ketika tanggal akhir berubah (untuk TempusDominusBs4)
        $(document).on('update.td', '#tanggal_akhir', function() {
            submitOnDateChange();
        });
        
        $(document).on('changeDate', '#tanggal_akhir', function() {
            submitOnDateChange();
        });
        
        // Fallback untuk event change biasa pada input tanggal mulai
        $(document).on('change', '#tanggal_mulai', function() {
            submitOnDateChange();
        });
        
        // Fallback untuk event change biasa pada input tanggal akhir
        $(document).on('change', '#tanggal_akhir', function() {
            submitOnDateChange();
        });
        
        // Event listener tambahan untuk input field langsung (jika user mengetik)
        $(document).on('blur', '#tanggal_mulai', function() {
            if ($(this).val() && !isSubmitting) {
                submitOnDateChange();
            }
        });
        
        $(document).on('blur', '#tanggal_akhir', function() {
            if ($(this).val() && !isSubmitting) {
                submitOnDateChange();
            }
        });
        
        // Event listener untuk saat datepicker ditutup (hide event)
        $(document).on('hide.td', '#tanggal_mulai', function() {
            submitOnDateChange();
        });
        
        $(document).on('hide.td', '#tanggal_akhir', function() {
            submitOnDateChange();
        });
        
        // Reset filter button
        $('#resetFilter').on('click', function() {
            var tanggalMulai = '{{ date('Y-m-01') }}';
            var tanggalAkhir = '{{ date('Y-m-d') }}';
            
            $('#status').val('belum_pulang');
            $('#tanggal_mulai').val(tanggalMulai);
            $('#tanggal_akhir').val(tanggalAkhir);
            
            // Trigger change event untuk datepicker jika menggunakan TempusDominusBs4
            if ($('#tanggal_mulai').data('datetimepicker')) {
                $('#tanggal_mulai').data('datetimepicker').date(tanggalMulai);
            }
            if ($('#tanggal_akhir').data('datetimepicker')) {
                $('#tanggal_akhir').data('datetimepicker').date(tanggalAkhir);
            }
            
            form.submit();
        });
        
        // Reset flag setelah form submit
        form.on('submit', function() {
            setTimeout(function() {
                isSubmitting = false;
            }, 500);
        });
        
        // Show loading indicator saat form submit
        form.on('submit', function() {
            // Remove existing overlay if any
            $('.loading-overlay').remove();
            // Add new overlay
            $('body').append('<div class="loading-overlay"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
        });
        
        // Remove loading overlay when page loads
        $(window).on('load', function() {
            setTimeout(function() {
                $('.loading-overlay').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 500);
        });
        
        // Inisialisasi event listener untuk datepicker setelah page load
        // Pastikan datepicker sudah terinisialisasi sebelum menambahkan event listener
        setTimeout(function() {
            // Attach event listener langsung ke datepicker instance jika tersedia
            var tanggalMulaiPicker = $('#tanggal_mulai').data('datetimepicker');
            var tanggalAkhirPicker = $('#tanggal_akhir').data('datetimepicker');
            
            if (tanggalMulaiPicker) {
                $('#tanggal_mulai').on('update.td', function() {
                    submitOnDateChange();
                });
            }
            
            if (tanggalAkhirPicker) {
                $('#tanggal_akhir').on('update.td', function() {
                    submitOnDateChange();
                });
            }
        }, 500);
    });
</script>
@stop
