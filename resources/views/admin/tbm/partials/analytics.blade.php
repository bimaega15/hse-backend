{{-- TBM / Safety Talk — Trending Report (read-only analytics) --}}

<!-- Filters -->
<div class="card mb-3">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between py-2">
        <h6 class="card-title mb-0"><i class="ri-filter-3-line me-2"></i>Filter Trending</h6>
        <a href="{{ route('admin.tbm.index') }}" class="btn btn-sm btn-soft-secondary">
            <i class="ri-list-check me-1"></i>Kembali ke Daftar
        </a>
    </div>
    <div class="card-body pb-2">
        <form method="GET" action="{{ route('admin.tbm.index') }}" id="tbmAnalyticsForm">
            <input type="hidden" name="view" value="analytics">
            <div class="row g-2">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label form-label-sm mb-1">Bulan (Trend Harian)</label>
                    <input type="month" name="month" class="form-control form-control-sm"
                        value="{{ $filters['month'] ?? now()->format('Y-m') }}">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label form-label-sm mb-1">Pembicara</label>
                    <select name="speaker" class="form-select form-select-sm tbm-analytics-select">
                        <option value="">Semua Pembicara</option>
                        @foreach ($filterOptions['speakers'] ?? [] as $u)
                            <option value="{{ $u->id }}" {{ ($filters['speaker'] ?? '') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label form-label-sm mb-1">Project</label>
                    <select name="project" class="form-select form-select-sm tbm-analytics-select">
                        <option value="">Semua Project</option>
                        @foreach ($filterOptions['projects'] ?? [] as $p)
                            <option value="{{ $p->id }}" {{ ($filters['project'] ?? '') == $p->id ? 'selected' : '' }}>
                                {{ $p->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label form-label-sm mb-1">Area Kerja / Dept</label>
                    <select name="location" class="form-select form-select-sm tbm-analytics-select">
                        <option value="">Semua Area Kerja</option>
                        @foreach ($filterOptions['locations'] ?? [] as $l)
                            <option value="{{ $l->id }}" {{ ($filters['location'] ?? '') == $l->id ? 'selected' : '' }}>
                                {{ $l->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="ri-search-line me-1"></i>Terapkan
                    </button>
                    <a href="{{ route('admin.tbm.index') }}?view=analytics" class="btn btn-outline-secondary btn-sm">
                        <i class="ri-refresh-line me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary cards (for selected month) -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-md-6">
        <div class="card analytics-card">
            <div class="card-body text-center">
                <h3 class="fw-bold text-primary mb-1">{{ $additionalData['summary']['total_tbm'] ?? 0 }}</h3>
                <p class="text-muted mb-0 fs-13">TBM di {{ $additionalData['month_label'] ?? '-' }}</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card analytics-card">
            <div class="card-body text-center">
                <h3 class="fw-bold text-info mb-1">{{ $additionalData['summary']['total_participants'] ?? 0 }}</h3>
                <p class="text-muted mb-0 fs-13">Total Partisipan</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card analytics-card">
            <div class="card-body text-center">
                <h3 class="fw-bold text-success mb-1">{{ $additionalData['summary']['avg_participants'] ?? 0 }}</h3>
                <p class="text-muted mb-0 fs-13">Rata-rata Partisipan / TBM</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card analytics-card">
            <div class="card-body text-center">
                <h3 class="fw-bold text-warning mb-1">{{ $additionalData['summary']['total_speakers'] ?? 0 }}</h3>
                <p class="text-muted mb-0 fs-13">Pembicara Aktif</p>
            </div>
        </div>
    </div>
</div>

<!-- Daily trend -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card analytics-card">
            <div class="card-header border-bottom border-dashed">
                <h5 class="card-title mb-0">
                    <i class="ri-line-chart-line me-2"></i>Trend Harian Pelaksanaan TBM —
                    {{ $additionalData['month_label'] ?? '' }}
                </h5>
            </div>
            <div class="card-body">
                <div id="tbmDailyTrendChart" style="min-height: 320px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Per Project & Per Area Kerja -->
<div class="row mb-3">
    <div class="col-xl-6">
        <div class="card analytics-card">
            <div class="card-header border-bottom border-dashed">
                <h5 class="card-title mb-0"><i class="ri-folder-chart-line me-2"></i>Trend per Project</h5>
            </div>
            <div class="card-body">
                <div id="tbmByProjectChart" style="min-height: 320px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card analytics-card">
            <div class="card-header border-bottom border-dashed">
                <h5 class="card-title mb-0"><i class="ri-map-pin-line me-2"></i>Trend per Area Kerja</h5>
            </div>
            <div class="card-body">
                <div id="tbmByLocationChart" style="min-height: 320px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Per Pembicara -->
<div class="row">
    <div class="col-12">
        <div class="card analytics-card">
            <div class="card-header border-bottom border-dashed">
                <h5 class="card-title mb-0"><i class="ri-user-voice-line me-2"></i>Trend per Pembicara</h5>
            </div>
            <div class="card-body">
                <div id="tbmBySpeakerChart" style="min-height: 360px;"></div>
            </div>
        </div>
    </div>
</div>

@push('jsSection')
    <script>
        $(document).ready(function() {
            $('.tbm-analytics-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
            });

            initTbmTrendingCharts();
        });

        function initTbmTrendingCharts() {
            const PRIMARY = '#2563eb';
            const COLORS = ['#2563eb', '#16a34a', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#84cc16',
                '#f97316', '#14b8a6'
            ];

            const daily = @json($additionalData['daily_trend'] ?? []);
            const byProject = @json($additionalData['by_project'] ?? []);
            const byLocation = @json($additionalData['by_location'] ?? []);
            const bySpeaker = @json($additionalData['by_speaker'] ?? []);

            const emptyState = (id, msg) => {
                document.querySelector(id).innerHTML =
                    `<div class="d-flex align-items-center justify-content-center text-muted" style="min-height:300px">
                        <div class="text-center"><i class="ri-bar-chart-2-line fs-48 d-block mb-2"></i>${msg}</div>
                     </div>`;
            };

            // --- Daily trend (area) ---
            if (daily.length > 0) {
                new ApexCharts(document.querySelector('#tbmDailyTrendChart'), {
                    chart: {
                        type: 'area',
                        height: 320,
                        toolbar: {
                            show: false
                        }
                    },
                    series: [{
                        name: 'Jumlah TBM',
                        data: daily.map(d => d.total)
                    }],
                    xaxis: {
                        categories: daily.map(d => d.label),
                        title: {
                            text: 'Tanggal'
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: v => Math.round(v)
                        },
                        title: {
                            text: 'Jumlah TBM'
                        }
                    },
                    colors: [PRIMARY],
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: .4,
                            opacityTo: .05
                        }
                    },
                    markers: {
                        size: 3
                    },
                }).render();
            } else {
                emptyState('#tbmDailyTrendChart', 'Belum ada data pada bulan ini');
            }

            // --- Per Project (bar) ---
            if (byProject.length > 0) {
                new ApexCharts(document.querySelector('#tbmByProjectChart'), {
                    chart: {
                        type: 'bar',
                        height: 320,
                        toolbar: {
                            show: false
                        }
                    },
                    series: [{
                        name: 'Jumlah TBM',
                        data: byProject.map(d => d.total)
                    }],
                    xaxis: {
                        categories: byProject.map(d => d.label)
                    },
                    colors: [PRIMARY],
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            columnWidth: '55%',
                            distributed: false
                        }
                    },
                    dataLabels: {
                        enabled: true
                    },
                    legend: {
                        show: false
                    },
                }).render();
            } else {
                emptyState('#tbmByProjectChart', 'Belum ada data');
            }

            // --- Per Area Kerja (bar) ---
            if (byLocation.length > 0) {
                new ApexCharts(document.querySelector('#tbmByLocationChart'), {
                    chart: {
                        type: 'bar',
                        height: 320,
                        toolbar: {
                            show: false
                        }
                    },
                    series: [{
                        name: 'Jumlah TBM',
                        data: byLocation.map(d => d.total)
                    }],
                    xaxis: {
                        categories: byLocation.map(d => d.label)
                    },
                    colors: ['#16a34a'],
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            columnWidth: '55%'
                        }
                    },
                    dataLabels: {
                        enabled: true
                    },
                    legend: {
                        show: false
                    },
                }).render();
            } else {
                emptyState('#tbmByLocationChart', 'Belum ada data');
            }

            // --- Per Pembicara (horizontal bar) ---
            if (bySpeaker.length > 0) {
                new ApexCharts(document.querySelector('#tbmBySpeakerChart'), {
                    chart: {
                        type: 'bar',
                        height: 360,
                        toolbar: {
                            show: false
                        }
                    },
                    series: [{
                        name: 'Jumlah TBM',
                        data: bySpeaker.map(d => d.total)
                    }],
                    xaxis: {
                        categories: bySpeaker.map(d => d.label)
                    },
                    colors: COLORS,
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: true,
                            distributed: true
                        }
                    },
                    dataLabels: {
                        enabled: true
                    },
                    legend: {
                        show: false
                    },
                }).render();
            } else {
                emptyState('#tbmBySpeakerChart', 'Belum ada data');
            }
        }
    </script>
@endpush
