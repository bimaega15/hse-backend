@extends('admin.layouts')

@section('title', 'Observasi Index Behavior & Trend')

@push('cssSection')
    <style>
        .table-yellow-header {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #000;
            font-weight: bold;
        }

        .table-bordered-custom {
            border: 2px solid #dee2e6;
        }

        .table-bordered-custom th,
        .table-bordered-custom td {
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .total-row {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            font-weight: bold;
        }

        .chart-section {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        .chart-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .section-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .section-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: bold;
            padding: 15px 20px;
            border: none;
        }

        .data-table {
            font-size: 0.9rem;
        }

        .data-table th {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #000;
            font-weight: 600;
            text-align: center;
            padding: 10px;
        }

        .data-table td {
            text-align: center;
            padding: 8px;
        }

        .data-table .total-row td {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            font-weight: bold;
        }

        .filter-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
@endpush

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">Observasi Index Behavior & Trend</h4>
                        <div class="page-title-right">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item active">Index Behavior & Trend</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-card">
                <form method="GET" action="{{ route('admin.behavior-index.trend') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Periode Awal</label>
                            <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Periode Akhir</label>
                            <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Lokasi</label>
                            <select class="form-select" name="location_id">
                                <option value="">Semua Lokasi</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}"
                                        {{ $locationId == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i data-lucide="filter" class="me-1"></i> Terapkan Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Section 1: Klasifikasi Observasi -->
            <div class="row">
                <div class="col-lg-5">
                    <div class="section-card">
                        <div class="card-header">
                            <i data-lucide="alert-triangle" class="me-2"></i>
                            BERDASARKAN KLASIFIKASI OBSERVASI
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered data-table mb-0">
                                <thead>
                                    <tr>
                                        <th>TIPE OBSERVASI</th>
                                        <th>JUMLAH</th>
                                        <th>PERSEN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($klasifikasiInsiden as $item)
                                        <tr>
                                            <td class="text-start">{{ $item['laporan'] }}</td>
                                            <td>{{ $item['jumlah'] }}</td>
                                            <td>{{ $item['persen'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td class="text-start"><strong>TOTAL</strong></td>
                                        <td><strong>{{ $totalInsiden }}</strong></td>
                                        <td><strong>100%</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="section-card">
                        <div class="card-header">
                            BERDASARKAN KLASIFIKASI OBSERVASI
                        </div>
                        <div class="card-body">
                            <div id="chartKlasifikasi" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Waktu Kejadian -->
            <div class="row">
                <div class="col-lg-5">
                    <div class="section-card">
                        <div class="card-header">
                            <i data-lucide="clock" class="me-2"></i>
                            BERDASARKAN WAKTU KEJADIAN
                        </div>
                        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-bordered data-table mb-0">
                                <thead>
                                    <tr>
                                        <th>JAM</th>
                                        <th>JUMLAH</th>
                                        <th>PERSEN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($waktuKejadian as $item)
                                        <tr>
                                            <td class="text-start">{{ $item['jam'] }}</td>
                                            <td>{{ $item['jumlah'] }}</td>
                                            <td>{{ $item['persen'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td class="text-start"><strong>TOTAL</strong></td>
                                        <td><strong>{{ $totalWaktu }}</strong></td>
                                        <td><strong>100%</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="section-card">
                        <div class="card-header">
                            BERDASARKAN JAM KEJADIAN
                        </div>
                        <div class="card-body">
                            <div id="chartWaktu" style="height: 500px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Hari Kejadian -->
            <div class="row">
                <div class="col-lg-5">
                    <div class="section-card">
                        <div class="card-header">
                            <i data-lucide="calendar" class="me-2"></i>
                            BERDASARKAN HARI KEJADIAN
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered data-table mb-0">
                                <thead>
                                    <tr>
                                        <th>HARI</th>
                                        <th>JUMLAH</th>
                                        <th>PERSEN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($hariKejadian as $item)
                                        <tr>
                                            <td class="text-start">{{ $item['hari'] }}</td>
                                            <td>{{ $item['jumlah'] }}</td>
                                            <td>{{ $item['persen'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td class="text-start"><strong>TOTAL</strong></td>
                                        <td><strong>{{ $totalHari }}</strong></td>
                                        <td><strong>100%</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="section-card">
                        <div class="card-header">
                            BERDASARKAN HARI KEJADIAN
                        </div>
                        <div class="card-body">
                            <div id="chartHari" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Tempat Kejadian -->
            <div class="row">
                <div class="col-lg-5">
                    <div class="section-card">
                        <div class="card-header">
                            <i data-lucide="map-pin" class="me-2"></i>
                            BERDASARKAN TEMPAT KEJADIAN
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered data-table mb-0">
                                <thead>
                                    <tr>
                                        <th>TEMPAT</th>
                                        <th>JUMLAH</th>
                                        <th>PERSEN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tempatKejadian as $item)
                                        <tr>
                                            <td class="text-start">{{ $item['tempat'] }}</td>
                                            <td>{{ $item['jumlah'] }}</td>
                                            <td>{{ $item['persen'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td class="text-start"><strong>TOTAL</strong></td>
                                        <td><strong>{{ $totalTempat }}</strong></td>
                                        <td><strong>100%</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="section-card">
                        <div class="card-header">
                            BERDASARKAN TEMPAT KEJADIAN
                        </div>
                        <div class="card-body">
                            <div id="chartTempat" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('jsSection')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Data from controller
            const klasifikasiData = @json($klasifikasiInsiden);
            const waktuData = @json($waktuKejadian);
            const hariData = @json($hariKejadian);
            const tempatData = @json($tempatKejadian);

            // Chart 1: Klasifikasi Observasi
            var optionsKlasifikasi = {
                series: [{
                    name: 'Jumlah',
                    data: klasifikasiData.map(item => item.jumlah)
                }],
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '60%',
                        borderRadius: 4,
                        distributed: true
                    }
                },
                colors: ['#ff6b6b', '#ffa502', '#ff7f50', '#ff4757', '#ff6348', '#ff9f43'],
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        const total = klasifikasiData.reduce((sum, item) => sum + item.jumlah, 0);
                        return total > 0 ? Math.round((val / total) * 100) + '%' : '0%';
                    },
                    style: {
                        fontSize: '11px'
                    }
                },
                xaxis: {
                    categories: klasifikasiData.map(item => item.laporan),
                    labels: {
                        rotate: -45,
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Jumlah'
                    }
                },
                legend: {
                    show: false
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        shadeIntensity: 0.25,
                        gradientToColors: undefined,
                        inverseColors: true,
                        opacityFrom: 1,
                        opacityTo: 0.85,
                        stops: [50, 100]
                    }
                }
            };
            var chartKlasifikasi = new ApexCharts(document.querySelector("#chartKlasifikasi"), optionsKlasifikasi);
            chartKlasifikasi.render();

            // Chart 2: Waktu Kejadian (Horizontal Bar)
            var optionsWaktu = {
                series: [{
                    name: 'Jumlah',
                    data: waktuData.map(item => item.jumlah).reverse()
                }],
                chart: {
                    type: 'bar',
                    height: 500,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '70%',
                        borderRadius: 2,
                        distributed: true
                    }
                },
                colors: ['#ff6b6b', '#ffa502', '#ff7f50', '#ff4757', '#ff6348', '#ff9f43', '#ff6b6b', '#ffa502',
                    '#ff7f50', '#ff4757', '#ff6348', '#ff9f43', '#ff6b6b', '#ffa502', '#ff7f50', '#ff4757',
                    '#ff6348', '#ff9f43', '#ff6b6b', '#ffa502', '#ff7f50', '#ff4757', '#ff6348', '#ff9f43'
                ],
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        const total = waktuData.reduce((sum, item) => sum + item.jumlah, 0);
                        return total > 0 ? Math.round((val / total) * 100) + '%' : '0%';
                    },
                    style: {
                        fontSize: '10px'
                    }
                },
                xaxis: {
                    categories: waktuData.map(item => item.jam).reverse(),
                    title: {
                        text: 'Jumlah'
                    }
                },
                legend: {
                    show: false
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'horizontal',
                        shadeIntensity: 0.25,
                        gradientToColors: undefined,
                        inverseColors: true,
                        opacityFrom: 1,
                        opacityTo: 0.85,
                        stops: [50, 100]
                    }
                }
            };
            var chartWaktu = new ApexCharts(document.querySelector("#chartWaktu"), optionsWaktu);
            chartWaktu.render();

            // Chart 3: Hari Kejadian
            var optionsHari = {
                series: [{
                    name: 'Jumlah',
                    data: hariData.map(item => item.jumlah)
                }],
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '60%',
                        borderRadius: 4,
                        distributed: true
                    }
                },
                colors: ['#ff6b6b', '#ff7f50', '#ff4757', '#ff6348', '#ff9f43', '#ffa502', '#ff6b6b'],
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        const total = hariData.reduce((sum, item) => sum + item.jumlah, 0);
                        return total > 0 ? Math.round((val / total) * 100) + '%' : '0%';
                    }
                },
                xaxis: {
                    categories: hariData.map(item => item.hari)
                },
                yaxis: {
                    title: {
                        text: 'Jumlah'
                    }
                },
                legend: {
                    show: false
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        shadeIntensity: 0.25,
                        gradientToColors: undefined,
                        inverseColors: true,
                        opacityFrom: 1,
                        opacityTo: 0.85,
                        stops: [50, 100]
                    }
                }
            };
            var chartHari = new ApexCharts(document.querySelector("#chartHari"), optionsHari);
            chartHari.render();

            // Chart 4: Tempat Kejadian
            var optionsTempat = {
                series: [{
                    name: 'Jumlah',
                    data: tempatData.map(item => item.jumlah)
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '70%',
                        borderRadius: 4,
                        distributed: true
                    }
                },
                colors: ['#ff6b6b', '#ffa502', '#ff7f50', '#ff4757', '#ff6348', '#ff9f43', '#e74c3c', '#f39c12',
                    '#e67e22', '#d35400', '#c0392b'
                ],
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        const total = tempatData.reduce((sum, item) => sum + item.jumlah, 0);
                        return total > 0 ? Math.round((val / total) * 100) + '%' : '0%';
                    },
                    style: {
                        fontSize: '10px'
                    }
                },
                xaxis: {
                    categories: tempatData.map(item => item.tempat),
                    labels: {
                        rotate: -45,
                        style: {
                            fontSize: '9px'
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Jumlah'
                    }
                },
                legend: {
                    show: false
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        shadeIntensity: 0.25,
                        gradientToColors: undefined,
                        inverseColors: true,
                        opacityFrom: 1,
                        opacityTo: 0.85,
                        stops: [50, 100]
                    }
                }
            };
            var chartTempat = new ApexCharts(document.querySelector("#chartTempat"), optionsTempat);
            chartTempat.render();
        });
    </script>
@endpush
