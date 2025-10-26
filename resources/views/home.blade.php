@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="dashboard-header">
        <div class="header-content">
            <h1 class="welcome-title">
                <i class="fas fa-tachometer-alt me-3"></i>
                Dashboard Medis
            </h1>
            <p class="welcome-subtitle">
                Selamat datang, <strong>{{$nm_dokter}}</strong> - {{$poliklinik}}
            </p>
        </div>
        <div class="header-actions">
            <span class="current-date">
                <i class="fas fa-calendar-alt me-2"></i>
                {{ date('d F Y') }}
            </span>
        </div>
    </div>
@stop

@section('content')
    <div class="dashboard-container">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-number">{{$totalPasien}}</h3>
                    <p class="stat-label">Total Pasien</p>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-number">{{$pasienBulanIni}}</h3>
                    <p class="stat-label">Pasien Bulan Ini</p>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>

            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-hospital"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-number">{{$pasienPoliBulanIni}}</h3>
                    <p class="stat-label">Poli Bulan Ini</p>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>

            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-number">{{$pasienPoliHariIni}}</h3>
                    <p class="stat-label">Poli Hari Ini</p>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="chart-section">
            <div class="modern-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar me-2"></i>
                        <span id="chartTitle">Statistik Kunjungan {{ ucwords(strtolower($poliklinik))}}</span>
                    </h3>
                    <div class="card-actions">
                        <button class="btn-filter active" data-period="month">
                            <i class="fas fa-calendar-alt"></i> Bulanan
                        </button>
                        <button class="btn-filter" data-period="year">
                            <i class="fas fa-calendar"></i> Tahunan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-controls mb-3">
                        <div class="chart-type-buttons">
                            <button class="chart-btn active" data-type="bar" title="Column Chart">
                                <i class="fas fa-chart-bar"></i>
                            </button>
                            <button class="chart-btn" data-type="area" title="Area Chart">
                                <i class="fas fa-chart-area"></i>
                            </button>
                            <button class="chart-btn" data-type="line" title="Line Chart">
                                <i class="fas fa-chart-line"></i>
                            </button>
                        </div>
                        <div class="chart-info">
                            <span class="total-visits">Total: <strong id="totalVisits">0</strong> kunjungan</span>
                        </div>
                    </div>
                    <div class="chart-container">
                        <div id="chartKunjungan"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="tables-grid">
            <div class="table-section">
                <div class="modern-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-star me-2"></i>
                            Pasien Paling Aktif
                        </h3>
                        <span class="badge badge-primary">{{ ucwords(strtolower($poliklinik))}}</span>
                    </div>
                    <div class="card-body table-responsive">
                        <div class="modern-table">
                            <table class="table" id="table5">
                                <thead>
                                    @foreach($headPasienAktif as $head)
                                        <th>{{ $head }}</th>
                                    @endforeach
                                </thead>
                                <tbody>
                                    @foreach($pasienAktif as $row)
                                        <tr>
                                            @foreach($row as $cell)
                                                <td>{!! $cell !!}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-section">
                <div class="modern-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock me-2"></i>
                            Antrian Terakhir
                        </h3>
                        <span class="badge badge-info">10 Terbaru</span>
                    </div>
                    <div class="card-body table-responsive">
                        <div class="modern-table">
                            <table class="table" id="table6">
                                <thead>
                                    @foreach($headPasienTerakhir as $head)
                                        <th>{{ $head }}</th>
                                    @endforeach
                                </thead>
                                <tbody>
                                    @foreach($pasienTerakhir as $row)
                                        <tr>
                                            @foreach($row as $cell)
                                                <td>{!! $cell !!}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Buttons -->
    <div class="floating-actions">
        <button class="fab-btn tooltip-fab" data-tooltip="Refresh Dashboard" onclick="refreshDashboard()">
            <i class="fas fa-sync-alt"></i>
        </button>
        <button class="fab-btn refresh tooltip-fab" data-tooltip="Export Data" onclick="exportData()">
            <i class="fas fa-download"></i>
        </button>
    </div>
@stop

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('css')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<style>
    /* Simple Dashboard Styles */
    body {
        background: #f8f9fa;
    }

    .content-wrapper {
        background: #f8f9fa;
    }

    /* Dashboard Header */
    .dashboard-header {
        background: #007bff;
        color: white;
        padding: 1.5rem 2rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .welcome-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
    }

    .welcome-subtitle {
        font-size: 1rem;
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
    }

    .current-date {
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.9rem;
    }

    /* Dashboard Container */
    .dashboard-container {
        padding: 0;
    }

    /* Statistics Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 6px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
        border-top: 4px solid #007bff;
    }

    .stat-card.stat-primary {
        border-top-color: #007bff;
    }

    .stat-card.stat-success {
        border-top-color: #28a745;
    }

    .stat-card.stat-warning {
        border-top-color: #ffc107;
    }

    .stat-card.stat-info {
        border-top-color: #17a2b8;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #007bff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .stat-content {
        flex: 1;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        color: #333;
        line-height: 1;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #666;
        margin: 0.3rem 0 0 0;
    }

    .stat-trend {
        color: #28a745;
        font-size: 1rem;
    }

    /* Simple Cards */
    .modern-card {
        background: white;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }

    .modern-card .card-header {
        background: #007bff;
        color: white;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 6px 6px 0 0;
    }

    .modern-card .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .modern-card .card-body {
        padding: 1.5rem;
    }

    .card-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-filter {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        padding: 0.4rem 0.8rem;
        border-radius: 4px;
        font-size: 0.85rem;
        color: white;
        cursor: pointer;
    }

    .btn-filter.active {
        background: rgba(255,255,255,0.3);
    }

    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    /* Chart Section */
    .chart-section {
        margin-bottom: 2rem;
    }

    .chart-container {
        position: relative;
        height: 350px;
        padding: 1rem;
    }

    /* Chart Controls */
    .chart-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 4px;
        margin-bottom: 1rem;
    }

    .chart-type-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .chart-btn {
        width: 36px;
        height: 36px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: white;
        color: #666;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .chart-btn:hover {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }

    .chart-btn.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }

    .total-visits {
        font-size: 0.9rem;
        color: #666;
    }

    .total-visits strong {
        color: #007bff;
    }

    /* Custom ApexCharts Tooltip */
    .custom-tooltip {
        background: #007bff;
        border-radius: 6px;
        padding: 0.75rem;
        color: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        min-width: 180px;
    }

    .tooltip-header {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        padding-bottom: 0.5rem;
    }

    .tooltip-body {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .tooltip-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .tooltip-label {
        font-size: 11px;
        opacity: 0.9;
    }

    .tooltip-value {
        font-size: 11px;
        font-weight: 600;
    }

    /* Tables Grid */
    .tables-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 2rem;
    }

    .table-section {
        min-height: 500px;
    }

    /* Simple Table */
    .modern-table {
        border-radius: 6px;
        overflow: hidden;
    }

    .modern-table .table {
        margin: 0;
    }

    .modern-table .table thead th {
        background: #007bff;
        color: white;
        font-weight: 600;
        padding: 0.75rem;
        border: none;
        font-size: 0.85rem;
    }

    .modern-table .table tbody tr:hover {
        background: #f8f9fa;
    }

    .modern-table .table tbody td {
        padding: 0.75rem;
        border-bottom: 1px solid #dee2e6;
        font-size: 0.9rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .dashboard-header {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
            padding: 1rem;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        .stat-card {
            padding: 1rem;
        }

        .tables-grid {
            grid-template-columns: 1fr;
        }

        .chart-container {
            height: 300px;
        }

        .fab-btn {
            width: 44px;
            height: 44px;
        }
    }

    /* Floating Action Button */
    .floating-actions {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .fab-btn {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #007bff;
        border: none;
        color: white;
        font-size: 1rem;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fab-btn:hover {
        background: #0056b3;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .fab-btn.refresh {
        background: #28a745;
    }

    .fab-btn.refresh:hover {
        background: #218838;
    }
</style>
@stop

@section('js')
    <script>
        // Chart Configuration with ApexCharts
        @php 
            $bulan = [];
            $jumlah = [];
            foreach ($statistikKunjungan as $key => $value) {
                $bulan[] = $value->bulan;
                $jumlah[] = intval($value->jumlah);
            }
        @endphp

        // Chart Data
        @php 
            $tahun = [];
            $jumlahTahunan = [];
            foreach ($statistikKunjunganTahunan as $key => $value) {
                $tahun[] = $value->tahun;
                $jumlahTahunan[] = intval($value->jumlah);
            }
        @endphp

        const chartData = {
            monthly: {
                categories: {!! json_encode($bulan) !!},
                values: {!! json_encode($jumlah) !!},
                title: 'Statistik Kunjungan Bulanan'
            },
            yearly: {
                categories: {!! json_encode($tahun) !!},
                values: {!! json_encode($jumlahTahunan) !!},
                title: 'Statistik Kunjungan Tahunan'
            },
            poliklinik: "{{ ucwords(strtolower($poliklinik))}}"
        };

        // Global chart variable
        let visitChart;
        let currentChartType = 'bar';
        let currentPeriod = 'month';

        // Initialize ApexChart
        document.addEventListener('DOMContentLoaded', function() {
            initializeChart('bar', 'month');
            updateTotalVisits();
            setupChartControls();
            setupPeriodControls();
        });

        function initializeChart(chartType, period = 'month') {
            // Destroy existing chart if exists
            if (visitChart) {
                visitChart.destroy();
            }

            // Get current data based on period
            const currentData = period === 'year' ? chartData.yearly : chartData.monthly;

            const options = {
                series: [{
                    name: 'Kunjungan Pasien',
                    data: currentData.values
                }],
                chart: {
                    type: chartType,
                    height: 350,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: false,
                            reset: true
                        }
                    },
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '70%'
                    }
                },
                colors: getChartColors(chartType),
                fill: getFillOptions(chartType),
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '12px',
                        fontWeight: 'bold',
                        colors: chartType === 'bar' ? ['#fff'] : ['#333']
                    },
                    offsetY: chartType === 'bar' ? 0 : -20,
                    formatter: function (val) {
                        return val + ' pasien';
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: chartType === 'line' ? 4 : chartType === 'area' ? 2 : 0
                },
                grid: {
                    borderColor: '#e7e7e7',
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    },
                    column: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    }
                },
                xaxis: {
                    categories: currentData.categories,
                    title: {
                        text: period === 'year' ? 'Tahun' : 'Bulan',
                        style: {
                            fontSize: '13px',
                            fontWeight: 'bold',
                            color: '#007bff'
                        }
                    },
                    labels: {
                        style: {
                            colors: '#666',
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Jumlah Kunjungan',
                        style: {
                            fontSize: '13px',
                            fontWeight: 'bold',
                            color: '#007bff'
                        }
                    },
                    labels: {
                        style: {
                            colors: '#666',
                            fontSize: '12px'
                        },
                        formatter: function (val) {
                            return Math.floor(val) + ' pasien';
                        }
                    }
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: function (val) {
                            return val + ' pasien';
                        }
                    }
                },
                title: {
                    text: currentData.title + ' ' + chartData.poliklinik,
                    align: 'center',
                    style: {
                        fontSize: '15px',
                        fontWeight: 'bold',
                        color: '#333'
                    }
                },
                legend: {
                    show: false
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: {
                            height: 300
                        },
                        dataLabels: {
                            enabled: false
                        },
                        title: {
                            style: {
                                fontSize: '14px'
                            }
                        }
                    }
                }]
            };

            visitChart = new ApexCharts(document.querySelector("#chartKunjungan"), options);
            visitChart.render();
        }

        function getChartColors(chartType) {
            if (chartType === 'line' || chartType === 'area') {
                return ['#007bff'];
            }
            
            // Primary Bootstrap colors
            const baseColors = [
                '#007bff', '#28a745', '#ffc107', '#17a2b8',
                '#dc3545', '#6c757d', '#007bff', '#28a745'
            ];
            
            const currentData = currentPeriod === 'year' ? chartData.yearly : chartData.monthly;
            return baseColors.slice(0, currentData.values.length);
        }

        function getFillOptions(chartType) {
            if (chartType === 'area') {
                return {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        shadeIntensity: 0.3,
                        gradientToColors: ['#0056b3'],
                        inverseColors: false,
                        opacityFrom: 0.8,
                        opacityTo: 0.2,
                        stops: [0, 100]
                    }
                };
            }
            
            return {
                opacity: 1
            };
        }

        function updateTotalVisits() {
            const currentData = currentPeriod === 'year' ? chartData.yearly : chartData.monthly;
            const total = currentData.values.reduce((a, b) => a + b, 0);
            document.getElementById('totalVisits').textContent = total.toLocaleString('id-ID');
        }

        function updateChartTitle(period) {
            const titleElement = document.getElementById('chartTitle');
            const baseTitle = 'Statistik Kunjungan {{ ucwords(strtolower($poliklinik))}}';
            const periodText = period === 'year' ? ' - 5 Tahun Terakhir' : ' - Tahun {{ date("Y") }}';
            titleElement.textContent = baseTitle + periodText;
        }

        function setupChartControls() {
            const chartButtons = document.querySelectorAll('.chart-btn');
            
            chartButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const chartType = this.getAttribute('data-type');
                    
                    // Update active button
                    chartButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update chart
                    currentChartType = chartType;
                    initializeChart(chartType, currentPeriod);
                });
            });
        }

        function setupPeriodControls() {
            const periodButtons = document.querySelectorAll('.btn-filter');
            
            periodButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const period = this.getAttribute('data-period');
                    
                    // Update active button
                    periodButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update period
                    currentPeriod = period;
                    
                    // Update chart title
                    updateChartTitle(period);
                    
                    // Update chart
                    initializeChart(currentChartType, period);
                    updateTotalVisits();
                });
            });
        }

        // Add loading states and interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTables with modern styling
            if (typeof $('#table5').DataTable === 'function') {
                $('#table5').DataTable({
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                    },
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excel',
                            className: 'btn btn-success btn-sm',
                            text: '<i class="fas fa-file-excel"></i> Excel'
                        },
                        {
                            extend: 'pdf',
                            className: 'btn btn-danger btn-sm',
                            text: '<i class="fas fa-file-pdf"></i> PDF'
                        }
                    ]
                });
            }

            if (typeof $('#table6').DataTable === 'function') {
                $('#table6').DataTable({
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                    },
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excel',
                            className: 'btn btn-success btn-sm',
                            text: '<i class="fas fa-file-excel"></i> Excel'
                        },
                        {
                            extend: 'pdf',
                            className: 'btn btn-danger btn-sm',
                            text: '<i class="fas fa-file-pdf"></i> PDF'
                        }
                    ]
                });
            }


            // Add chart responsiveness
            window.addEventListener('resize', function() {
                if (visitChart) {
                    visitChart.updateOptions({
                        chart: {
                            height: window.innerWidth <= 768 ? 300 : 350
                        }
                    });
                }
            });
        });

        // Floating Action Button Functions
        function refreshDashboard() {
            // Add loading state
            const refreshBtn = document.querySelector('.fab-btn');
            const originalIcon = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            refreshBtn.disabled = true;

            // Simulate refresh delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }

        function exportData() {
            // Create export options modal or direct download
            const tables = ['table5', 'table6'];
            const exportData = [];
            
            tables.forEach(tableId => {
                const table = document.getElementById(tableId);
                if (table && $.fn.DataTable.isDataTable('#' + tableId)) {
                    const dataTable = $('#' + tableId).DataTable();
                    dataTable.button('.buttons-excel').trigger();
                }
            });

            // Show notification
            showNotification('Data berhasil diexport!', 'success');
        }

        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            `;
            
            // Add notification styles
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#56ab2f' : '#667eea'};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                z-index: 9999;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;

            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);

            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Add responsive behavior for floating buttons
        function handleFloatingButtons() {
            const floatingActions = document.querySelector('.floating-actions');
            if (window.innerWidth <= 768) {
                floatingActions.style.bottom = '1rem';
                floatingActions.style.right = '1rem';
            } else {
                floatingActions.style.bottom = '2rem';
                floatingActions.style.right = '2rem';
            }
        }

        window.addEventListener('resize', handleFloatingButtons);
        handleFloatingButtons();
    </script>
    
    {{-- Include Dokumen Online Modal --}}
    @include('components.dokumen-online')
    
    @push('css')
    <style>
        .list-group-item {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .list-group-item:hover {
            background-color: #f8f9fa;
        }
        
        .folder-item {
            cursor: pointer;
        }
        
        .breadcrumb {
            background-color: #f8f9fa;
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
        }
        
        #documentList {
            max-height: 400px;
            overflow-y: auto;
        }
        
        #folderList {
            max-height: 400px;
            overflow-y: auto;
        }
        
        /* PDF Viewer Styles */
        #modalPdfViewer .modal-body {
            overflow: hidden;
        }
        
        #pdfIframe {
            transition: opacity 0.3s ease;
        }
        
        #modalPdfViewer .modal-dialog {
            margin: 1.75rem auto;
        }
        
        /* Search Styles */
        #searchDocument {
            transition: border-color 0.2s ease;
        }
        
        #searchDocument:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        #searchBoxDocument mark {
            background-color: #fff3cd;
            color: #856404;
            padding: 0 2px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        #clearSearchDocument {
            transition: all 0.2s ease;
        }
        
        #clearSearchDocument:hover {
            background-color: #f8f9fa;
        }
        
        #searchResultInfo {
            transition: opacity 0.2s ease;
        }
        
        @media (max-width: 768px) {
            #modalPdfViewer .modal-dialog {
                max-width: 98vw !important;
            }
            
            #modalPdfViewer .modal-body {
                height: 70vh !important;
            }
            
            #pdfIframe {
                height: 70vh !important;
            }
            
            #searchBoxDocument .input-group {
                font-size: 0.875rem;
            }
        }
    </style>
    @endpush
    
    @push('js')
    <script>
    $(document).ready(function() {
        let currentFolder = null;
        const baseUrl = '{{ url('/') }}';
        
        // Load folders when modal is shown
        $('#modalDokumenOnline').on('show.bs.modal', function() {
            if (currentFolder === null) {
                loadFolders();
            }
        });
        
        // Back to folder list
        $('#btnBackToFolder').on('click', function(e) {
            e.preventDefault();
            currentFolder = null;
            $('#breadcrumbNav').hide();
            $('#documentList').hide();
            $('#folderList').show();
            $('#searchBoxDocument').hide();
            $('#searchDocument').val('');
            $('#clearSearchDocument').hide();
            $('#searchResultInfo').empty();
        });
        
        function loadFolders() {
            $.ajax({
                url: baseUrl + '/dokumen-online/folders',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        displayFolders(response.folders);
                    } else {
                        showError('Gagal memuat folder');
                    }
                },
                error: function() {
                    showError('Terjadi kesalahan saat memuat folder');
                }
            });
        }
        
        function displayFolders(folders) {
            let html = '';
            
            if (folders.length === 0) {
                html = '<div class="alert alert-info">Tidak ada folder tersedia</div>';
            } else {
                folders.forEach(function(folder) {
                    html += `
                        <div class="list-group-item list-group-item-action folder-item" data-folder="${folder.path}">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-folder mr-2 text-warning"></i>
                                    <strong>${folder.name}</strong>
                                </div>
                                <small class="text-muted">${folder.size}</small>
                            </div>
                        </div>
                    `;
                });
            }
            
            $('#folderList').html(html);
            
            // Add click handler for folders
            $('.folder-item').on('click', function() {
                const folderPath = $(this).data('folder');
                loadDocuments(folderPath);
            });
        }
        
        function loadDocuments(folder) {
            currentFolder = folder;
            
            $.ajax({
                url: baseUrl + '/dokumen-online/documents/' + encodeURIComponent(folder),
                method: 'GET',
                beforeSend: function() {
                    $('#documentList').html('<div class="text-center p-3"><div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div></div>').show();
                },
                success: function(response) {
                    if (response.success) {
                        displayDocuments(response.documents, folder);
                        $('#breadcrumbNav').show();
                        $('#currentFolder').text(folder);
                        $('#folderList').hide();
                        $('#documentList').show();
                        $('#searchBoxDocument').show();
                    } else {
                        showError('Gagal memuat dokumen');
                    }
                },
                error: function() {
                    showError('Terjadi kesalahan saat memuat dokumen');
                }
            });
        }
        
        // Store all documents for search
        let allDocuments = [];
        
        function displayDocuments(documents, folder) {
            // Store all documents
            allDocuments = documents;
            
            // Display all documents initially
            filterAndDisplayDocuments('');
            
            // Setup search functionality
            setupSearchFunctionality();
        }
        
        function setupSearchFunctionality() {
            // Remove previous listeners
            $('#searchDocument').off('input');
            $('#clearSearchDocument').off('click');
            
            // Search input handler
            let searchTimeout;
            $('#searchDocument').on('input', function() {
                const query = $(this).val().toLowerCase().trim();
                
                clearTimeout(searchTimeout);
                
                searchTimeout = setTimeout(function() {
                    filterAndDisplayDocuments(query);
                    
                    // Show/hide clear button
                    if (query.length > 0) {
                        $('#clearSearchDocument').show();
                    } else {
                        $('#clearSearchDocument').hide();
                    }
                }, 300);
            });
            
            // Clear search button
            $('#clearSearchDocument').on('click', function() {
                $('#searchDocument').val('').trigger('input');
                $('#clearSearchDocument').hide();
            });
        }
        
        function filterAndDisplayDocuments(query) {
            let html = '';
            let filteredDocs = allDocuments;
            
            // Filter documents if query exists
            if (query.length > 0) {
                filteredDocs = allDocuments.filter(function(doc) {
                    return doc.name.toLowerCase().includes(query);
                });
            }
            
            // Update search result info
            updateSearchResultInfo(allDocuments.length, filteredDocs.length, query);
            
            if (filteredDocs.length === 0) {
                html = `<div class="alert alert-warning">
                    <i class="fas fa-search mr-2"></i>
                    ${query.length > 0 ? 'Tidak ada dokumen yang cocok dengan "' + query + '"' : 'Tidak ada dokumen tersedia'}
                </div>`;
            } else {
                filteredDocs.forEach(function(doc) {
                    html += `
                        <div class="list-group-item list-group-item-action document-item" data-name="${doc.name.toLowerCase()}">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-file-pdf mr-2 text-danger"></i>
                                    <span>${highlightSearchTerm(doc.name, query)}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    ${doc.size ? '<small class="text-muted mr-2">' + doc.size + '</small>' : ''}
                                    <button class="btn btn-sm btn-primary open-pdf-viewer" data-url="${doc.url}" data-name="${doc.name}" title="Buka PDF">
                                        <i class="fas fa-file-pdf"></i> Buka
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            
            $('#documentList').html(html);
            
            // Add click handler for PDF viewer buttons
            $('.open-pdf-viewer').off('click').on('click', function(e) {
                e.preventDefault();
                const pdfUrl = $(this).data('url');
                const pdfName = $(this).data('name');
                openPdfViewer(pdfUrl, pdfName);
            });
        }
        
        function highlightSearchTerm(text, query) {
            if (!query || query.length === 0) {
                return text;
            }
            
            // Escape special regex characters
            const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(`(${escapedQuery})`, 'gi');
            return text.replace(regex, '<mark>$1</mark>');
        }
        
        function updateSearchResultInfo(total, filtered, query) {
            let info = '';
            
            if (query.length > 0) {
                if (filtered === total) {
                    info = `<i class="fas fa-check-circle text-success"></i> Menampilkan ${filtered} dari ${total} dokumen`;
                } else {
                    info = `<i class="fas fa-filter text-primary"></i> Menampilkan ${filtered} dari ${total} dokumen`;
                }
            } else {
                info = `<i class="fas fa-info-circle text-muted"></i> ${total} dokumen tersedia`;
            }
            
            $('#searchResultInfo').html(info);
        }
        
        function openPdfViewer(pdfUrl, pdfName) {
            // Set modal title
            $('#modalPdfViewerTitle').html('<i class="fas fa-file-pdf mr-2"></i>' + pdfName);
            
            // Reset previous listeners
            $('#pdfIframe').off('load error');
            
            // Show loading state
            $('#pdfLoading').show();
            $('#pdfIframe').hide();
            $('#pdfError').hide();
            
            // Set external links
            $('#pdfExternalLink').attr('href', pdfUrl);
            $('#pdfDownloadLink').attr('href', pdfUrl);
            
            // Create Google Docs Viewer URL
            const viewerUrl = 'https://docs.google.com/viewer?url=' + encodeURIComponent(pdfUrl) + '&embedded=true';
            
            // Set iframe source
            $('#pdfIframe').attr('src', viewerUrl);
            
            // Show modal
            $('#modalPdfViewer').modal('show');
            
            // Handle iframe load with timeout
            let loadTimeout = setTimeout(function() {
                $('#pdfLoading').hide();
                $('#pdfIframe').show();
            }, 1500);
            
            // Handle iframe load event (one-time)
            $('#pdfIframe').one('load', function() {
                clearTimeout(loadTimeout);
                $('#pdfLoading').hide();
                $('#pdfIframe').show();
            });
            
            // Handle iframe error (one-time)
            $('#pdfIframe').one('error', function() {
                clearTimeout(loadTimeout);
                showPdfError(pdfUrl);
            });
        }
        
        function showPdfError(pdfUrl) {
            $('#pdfLoading').hide();
            $('#pdfIframe').hide();
            $('#pdfError').show();
            $('#pdfDownloadLink').attr('href', pdfUrl);
        }
        
        // Reset modal when closed
        $('#modalPdfViewer').on('hidden.bs.modal', function() {
            // Clear iframe source to prevent memory leaks
            $('#pdfIframe').attr('src', 'about:blank');
            
            // Reset states
            $('#pdfLoading').show();
            $('#pdfIframe').hide();
            $('#pdfError').hide();
            
            // Remove all event listeners
            $('#pdfIframe').off('load error');
        });
        
        function showError(message) {
            $('#folderList').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    ${message}
                </div>
            `);
        }
    });
    </script>
    @endpush
@stop
