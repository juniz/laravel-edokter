@extends('adminlte::page')

@section('title', 'Pemeriksaan Pasien Ranap')

@section('content_header')
    <div class="ranap-header d-flex flex-wrap justify-content-between align-items-center pb-2 mb-2 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('ranap.pasien') }}" class="btn btn-sm btn-outline-secondary mr-2" title="Kembali ke Daftar Pasien Ranap">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <div class="d-flex align-items-center">
                    <h1 class="h5 font-weight-bold text-slate-900 mb-0">Pemeriksaan Rawat Inap</h1>
                    <span class="badge badge-light border text-secondary ml-2 font-weight-normal px-2 py-1" style="font-size: 0.75rem;">Visite &amp; Asuhan</span>
                </div>
                <div class="d-flex align-items-center mt-1 text-muted small" style="gap: 0.5rem;">
                    <span>No. Rawat:</span>
                    <span class="font-mono text-dark font-weight-bold bg-white border rounded px-1">{{ request()->get('no_rawat', '-') }}</span>
                    @if(request()->get('no_rm'))
                        <span class="text-secondary opacity-50">•</span>
                        <span>No. Rekam Medis:</span>
                        <span class="font-mono text-dark font-weight-bold bg-white border rounded px-1">{{ request()->get('no_rm') }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-none d-md-flex align-items-center" style="gap: 0.35rem;">
            <a href="#fase-asuhan" class="btn btn-xs btn-light border text-secondary font-weight-normal px-2">1. Asuhan</a>
            <a href="#fase-penunjang" class="btn btn-xs btn-light border text-secondary font-weight-normal px-2">2. Penunjang</a>
            <a href="#fase-perioperatif" class="btn btn-xs btn-light border text-secondary font-weight-normal px-2">3. Prosedur</a>
            <a href="#fase-handover" class="btn btn-xs btn-light border text-secondary font-weight-normal px-2">4. Serah Terima</a>
        </div>
    </div>
@stop

@section('content')
    <div class="ranap-station">
        <livewire:ralan.pasien :noRawat="request()->get('no_rawat')" />
        <x-ralan.riwayat :no-rawat="request()->get('no_rawat')" />

        <div class="row mt-3" style="row-gap: 0;">
            {{-- Kolom Kanan (Formulir Aktif Klinis) — mobile order-1 --}}
            <div class="col-12 col-lg-7 order-1 order-lg-2 mb-4">

                {{-- ─── Fase 1: Asuhan Klinis Utama ─────────────────────────── --}}
                <div id="fase-asuhan" class="clinical-phase-marker mb-3">
                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <span class="phase-number">1</span>
                        <h2 class="phase-title mb-0">Asuhan klinis utama</h2>
                    </div>
                    <p class="phase-caption mb-0 text-muted">SOAP harian, terapi resep rawat inap, dan resume perkembangan.</p>
                </div>

                <x-adminlte-card title="Pemeriksaan SOAP" theme="primary" theme-mode="outline" icon="fas fa-stethoscope" collapsible maximizable class="clinical-card mb-3">
                    <livewire:ranap.pemeriksaan :noRawat="request()->get('no_rawat')" :noRm="request()->get('no_rm')" />
                    <livewire:ranap.modal.edit-pemeriksaan />
                </x-adminlte-card>
                <div class="mb-3">
                    <x-ranap.resep-ranap />
                </div>
                <div class="mb-3">
                    <livewire:ranap.resume-pasien :no-rawat="request()->get('no_rawat')" />
                </div>
                <div class="mb-3">
                    <livewire:ranap.catatan-pasien :noRawat="request()->get('no_rawat')" :noRm="request()->get('no_rm')" />
                </div>

                {{-- ─── Fase 2: Penunjang & Konsultasi ───────────────────────── --}}
                <div id="fase-penunjang" class="clinical-phase-marker mt-4 mb-3">
                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <span class="phase-number">2</span>
                        <h2 class="phase-title mb-0">Penunjang medik &amp; konsultasi</h2>
                    </div>
                    <p class="phase-caption mb-0 text-muted">Konsultasi antar DPJP, order laboratorium, dan radiologi diagnostik.</p>
                </div>

                <x-adminlte-card title="Konsultasi Medik" icon="fas fa-user-md" theme="primary" theme-mode="outline" maximizable collapsible="collapsed" class="clinical-card mb-3">
                    <livewire:component.konsultasi-medik :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" />
                </x-adminlte-card>
                <div class="mb-3">
                    <livewire:ranap.permintaan-lab :no-rawat="request()->get('no_rawat')" />
                </div>
                <div class="mb-3">
                    <livewire:ranap.permintaan-radiologi :no-rawat="request()->get('no_rawat')" />
                </div>

                {{-- ─── Fase 3: Perioperatif & Anestesi ──────────────────────── --}}
                <div id="fase-perioperatif" class="clinical-phase-marker mt-4 mb-3">
                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <span class="phase-number">3</span>
                        <h2 class="phase-title mb-0">Prosedur perioperatif &amp; anestesi</h2>
                    </div>
                    <p class="phase-caption mb-0 text-muted">Asesmen pra-bedah, evaluasi induksi anestesi, dan laporan operasi.</p>
                </div>

                <x-adminlte-card title="Penandaan Area Operasi" icon="fas fa-map-marker-alt" theme="primary" theme-mode="outline" maximizable collapsible="collapsed" class="clinical-card mb-3">
                    <livewire:component.penandaan-area-operasi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" />
                </x-adminlte-card>
                <x-adminlte-card title="Penilaian Pre Operasi" icon="fas fa-clipboard-check" theme="primary" theme-mode="outline" maximizable collapsible="collapsed" class="clinical-card mb-3">
                    <livewire:component.penilaian-pre-operasi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" :key="'penilaian-pre-op-ranap-'.request()->get('no_rawat')" />
                </x-adminlte-card>
                <x-adminlte-card title="Penilaian Pre Anestesi" icon="fas fa-syringe" theme="primary" theme-mode="outline" maximizable collapsible="collapsed" class="clinical-card mb-3">
                    <livewire:component.penilaian-pre-anestesi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" :key="'penilaian-pre-anestesi-ranap-'.request()->get('no_rawat')" />
                </x-adminlte-card>
                <x-adminlte-card title="Penilaian Pre Induksi" icon="fas fa-lungs" theme="primary" theme-mode="outline" maximizable collapsible="collapsed" class="clinical-card mb-3">
                    <livewire:component.penilaian-pre-induksi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" :key="'penilaian-pre-induksi-ranap-'.request()->get('no_rawat')" />
                </x-adminlte-card>
                <x-adminlte-card title="Laporan Operasi" icon="fas fa-cut" theme="primary" theme-mode="outline" maximizable collapsible="collapsed" class="clinical-card mb-3">
                    <livewire:ranap.lap-operasi :no-rawat="request()->get('no_rawat')" />
                    <livewire:ranap.template-lap-operasi />
                </x-adminlte-card>

                {{-- ─── Fase 4: Handover & Tindakan ─────────────────────────── --}}
                <div id="fase-handover" class="clinical-phase-marker mt-4 mb-3">
                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <span class="phase-number">4</span>
                        <h2 class="phase-title mb-0">Serah terima &amp; tindakan medis</h2>
                    </div>
                    <p class="phase-caption mb-0 text-muted">Komunikasi efektif SBAR, tindakan keperawatan, dan ventilator.</p>
                </div>

                <x-adminlte-card title="SBAR (Situation, Background, Assessment, Recommendation)" icon="fas fa-exchange-alt" theme="primary" theme-mode="outline" maximizable collapsible="collapsed" class="clinical-card mb-3">
                    <livewire:ranap.sbar.detail-sbar />
                    <livewire:ranap.sbar.table-sbar :noRawat="request()->get('no_rawat')" />
                </x-adminlte-card>
                <x-adminlte-card title="Laporan Tindakan" icon="fas fa-file-medical-alt" theme="primary" theme-mode="outline" maximizable collapsible="collapsed" class="clinical-card mb-3">
                    <livewire:ranap.lap-tindakan :noRawat="request()->get('no_rawat')" />
                </x-adminlte-card>
                <x-adminlte-card title="Laporan Tindakan Ventilator" icon="fas fa-wind" theme="primary" theme-mode="outline" maximizable collapsible="collapsed" class="clinical-card mb-3">
                    <livewire:ranap.lap-tindakan-ventilator :noRawat="request()->get('no_rawat')" />
                </x-adminlte-card>
            </div>

            {{-- Kolom Kiri (Riwayat Pasien & Rekam Medis Longitudinal) — mobile order-2 --}}
            <div class="col-12 col-lg-5 order-2 order-lg-1 mb-4">
                <div class="clinical-phase-marker mb-3">
                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <span class="phase-badge"><i class="fas fa-clock"></i></span>
                        <h2 class="phase-title mb-0">Riwayat longitudinal pasien</h2>
                    </div>
                    <p class="phase-caption mb-0 text-muted">CPPT, rekam medis rawat jalan sebelumnya, dan hasil penunjang.</p>
                </div>
                <div class="sticky-top" style="top: 1rem; z-index: 10;">
                    <livewire:ralan.pasien-tabs :noRawat="request()->get('no_rawat')" />
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    /* Clinical Station Tokens */
    :root {
        --clinic-canvas: #f8fafc;
        --clinic-slate-900: #0f172a;
        --clinic-slate-700: #334155;
        --clinic-slate-500: #64748b;
        --clinic-slate-200: #e2e8f0;
        --clinic-cobalt: #1e40af;
    }

    .font-mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-variant-numeric: tabular-nums;
        font-size: 0.825rem;
    }

    .ranap-header {
        border-color: var(--clinic-slate-200) !important;
    }

    .text-slate-900 {
        color: var(--clinic-slate-900);
    }

    /* Phase Marker Headers */
    .clinical-phase-marker {
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--clinic-slate-200);
    }

    .phase-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #e0e7ff;
        color: var(--clinic-cobalt);
        font-size: 0.75rem;
        font-weight: 700;
        line-height: 1;
        flex-shrink: 0;
    }

    .phase-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #f1f5f9;
        color: var(--clinic-slate-500);
        font-size: 0.75rem;
        line-height: 1;
        flex-shrink: 0;
    }

    .phase-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--clinic-slate-900);
        letter-spacing: -0.01em;
    }

    .phase-caption {
        font-size: 0.8rem;
        margin-top: 0.15rem;
    }

    /* Clinical Cards Refinement */
    .clinical-card {
        border: 1px solid var(--clinic-slate-200) !important;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .clinical-card:focus-within {
        border-color: #93c5fd !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
    }

    .clinical-card .card-header {
        background-color: #ffffff !important;
        border-bottom: 1px solid var(--clinic-slate-200);
        padding: 0.65rem 1rem;
    }

    .clinical-card .card-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--clinic-slate-700);
    }

    /* Smooth anchor navigation */
    html {
        scroll-behavior: smooth;
    }

    @media (prefers-reduced-motion: reduce) {
        html {
            scroll-behavior: auto;
        }
    }
</style>
@stop

@section('plugins.TempusDominusBs4', true)
@section('js')
@stop


