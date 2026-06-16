@extends('layouts.app')
@section('title', 'Export Management')

@section('styles')
<style>
    .export-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 24px 0;
    }
    .export-header {
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .export-header-icon {
        width: 48px;
        height: 48px;
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #ef4444;
    }
    .export-header-text h1 {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .export-header-text p {
        font-size: 13px;
        color: var(--text-secondary);
    }
    .export-card {
        background: var(--card-color);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .export-section-title {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 24px;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 12px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 32px;
    }
    @media (max-width: 640px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .form-group label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--text-secondary);
        letter-spacing: .5px;
    }
    .form-select {
        background: #151515;
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        transition: all 0.2s;
        cursor: pointer;
        width: 100%;
    }
    .form-select:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.15);
    }
    .export-actions {
        display: flex;
        justify-content: flex-end;
    }
    .btn-export {
        background: #ef4444;
        color: #fff;
        padding: 14px 28px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-export:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
    .btn-export:active {
        transform: translateY(0);
    }
    .info-box {
        background: rgba(255, 255, 255, 0.02);
        border: 1px dashed var(--border-color);
        border-radius: 10px;
        padding: 16px;
        margin-top: 24px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }
    .info-box i {
        color: #ef4444;
        font-size: 16px;
        margin-top: 2px;
    }
    .info-box-text h4 {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 4px;
        color: var(--text-primary);
    }
    .info-box-text p {
        font-size: 11px;
        color: var(--text-secondary);
        line-height: 1.5;
    }
</style>
@endsection

@section('content')
<div class="export-container">
    <div class="export-header">
        <div class="export-header-icon">
            <i class="fa-solid fa-file-pdf"></i>
        </div>
        <div class="export-header-text">
            <h1>Export PDF Report</h1>
            <p>Konfigurasikan dan unduh laporan komprehensif kesehatan organisasi UMKM Anda.</p>
        </div>
    </div>

    @if(!$has_umkm)
        <div class="export-card" style="text-align: center; padding: 48px 32px;">
            <i class="fa-solid fa-store" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 16px;"></i>
            <h3 style="margin-bottom: 8px;">Belum Ada UMKM Terdaftar</h3>
            <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 24px;">Silakan daftarkan UMKM Anda terlebih dahulu untuk memulai ekspor laporan.</p>
            <a href="{{ route('tambah-umkm') }}" class="btn-export" style="background: var(--text-primary); color: #000;">
                <i class="fa-solid fa-plus"></i> Tambah UMKM
            </a>
        </div>
    @elseif($assessment_history->isEmpty())
        <div class="export-card" style="text-align: center; padding: 48px 32px;">
            <i class="fa-solid fa-file-invoice" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 16px;"></i>
            <h3 style="margin-bottom: 8px;">Belum Ada Riwayat Asesmen</h3>
            <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 24px;">UMKM yang dipilih belum menyelesaikan asesmen kesehatan organisasi.</p>
            
            <div style="max-width: 300px; margin: 0 auto 24px;">
                <form action="{{ route('export.pdf') }}" method="GET" id="umkmSelectorForm">
                    <label for="umkm_id" style="font-size: 10px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary); display: block; margin-bottom: 8px;">Pilih UMKM Lain</label>
                    <select name="umkm_id" id="umkm_id" class="form-select" onchange="document.getElementById('umkmSelectorForm').submit()">
                        @foreach($all_umkms as $u)
                            <option value="{{ $u->umkm_id }}" {{ $selected_umkm_id == $u->umkm_id ? 'selected' : '' }}>{{ $u->nama_umkm }} ({{ $u->sektor_usaha ?? 'Umum' }})</option>
                        @endforeach
                    </select>
                </form>
            </div>
            
            <a href="{{ route('assessment') }}" class="btn-export" style="background: var(--text-primary); color: #000;">
                <i class="fa-solid fa-file-pen"></i> Mulai Asesmen
            </a>
        </div>
    @else
        <div class="export-card">
            <div class="export-section-title">
                <i class="fa-solid fa-sliders"></i> Parameter Pembuatan Laporan
            </div>

            <form action="{{ route('export.pdf.print') }}" method="GET" target="_blank" id="pdfPrintForm">
                <div class="form-grid">
                    <!-- 1. Dropdown UMKM -->
                    <div class="form-group">
                        <label for="umkm_id_select">1. UMKM</label>
                        <select id="umkm_id_select" name="umkm_id" class="form-select" onchange="updateUmkmFilter(this.value)">
                            @foreach($all_umkms as $u)
                                <option value="{{ $u->umkm_id }}" {{ $selected_umkm_id == $u->umkm_id ? 'selected' : '' }}>
                                    {{ $u->nama_umkm }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Dropdown Periode Asesmen -->
                    <div class="form-group">
                        <label for="assessment_id_select">2. Periode Asesmen</label>
                        <select id="assessment_id_select" name="assessment_id" class="form-select">
                            @foreach($assessment_history as $hist)
                                @php
                                    $carbonDate = \Carbon\Carbon::parse($hist->tanggal_mulai ?? $hist->created_at);
                                    $monthName = $carbonDate->translatedFormat('F');
                                    $year = $carbonDate->format('Y');
                                @endphp
                                <option value="{{ $hist->assessment_id }}" {{ $selected_assessment_id == $hist->assessment_id ? 'selected' : '' }}>
                                    Periode: {{ $monthName }} {{ $year }} (Ke-{{ $hist->sequence_in_month }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Dropdown Sektor untuk Standar Deviasi -->
                    <div class="form-group">
                        <label for="sd_sector_select">3. Sektor untuk Standar Deviasi</label>
                        <select id="sd_sector_select" name="sd_sector" class="form-select">
                            <option value="global" {{ $selected_sd_sector == 'global' ? 'selected' : '' }}>Global (Seluruh Sektor)</option>
                            @foreach($all_sectors as $sector)
                                <option value="{{ $sector }}" {{ $selected_sd_sector == $sector ? 'selected' : '' }}>
                                    Sektor: {{ $sector }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 4. Dropdown Sektor untuk Perbandingan Industri -->
                    <div class="form-group">
                        <label for="comparison_sector_select">4. Sektor untuk Perbandingan Industri</label>
                        <select id="comparison_sector_select" name="comparison_sector" class="form-select">
                            @foreach($all_sectors as $sector)
                                <option value="{{ $sector }}" {{ $selected_comparison_sector == $sector ? 'selected' : '' }}>
                                    Sektor: {{ $sector }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="export-actions">
                    <button type="submit" class="btn-export">
                        <i class="fa-solid fa-file-pdf"></i> Ekspor Laporan ke PDF
                    </button>
                </div>
            </form>

            <div class="info-box">
                <i class="fa-solid fa-circle-info"></i>
                <div class="info-box-text">
                    <h4>Catatan Proses PDF</h4>
                    <p>Setelah menekan tombol ekspor, halaman cetak baru akan terbuka di tab baru. Dialog cetak bawaan browser akan muncul secara otomatis. Untuk hasil terbaik, pastikan memilih opsi <strong>"Save as PDF"</strong> sebagai tujuan (Destination) dan centang opsi <strong>"Background graphics"</strong> pada pengaturan cetak browser Anda.</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function updateUmkmFilter(umkmId) {
        // Reload page to refresh assessment periods history for this UMKM
        const url = new URL(window.location.href);
        url.searchParams.set('umkm_id', umkmId);
        url.searchParams.delete('assessment_id'); // Clear assessment so it defaults to the latest
        window.location.href = url.toString();
    }
</script>
@endsection
