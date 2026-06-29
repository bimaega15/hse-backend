{{-- TBM / Safety Talk — List view (read-only) with Advance Search --}}

<!-- Summary stat cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="ri-megaphone-line"></i></div>
                <div>
                    <h4 class="mb-0 fw-bold" id="statTotalTbm">0</h4>
                    <p class="text-muted mb-0 fs-13">Total TBM / Safety Talk</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="ri-calendar-check-line"></i></div>
                <div>
                    <h4 class="mb-0 fw-bold" id="statThisMonth">0</h4>
                    <p class="text-muted mb-0 fs-13">Bulan Ini</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info"><i class="ri-group-line"></i></div>
                <div>
                    <h4 class="mb-0 fw-bold" id="statParticipants">0</h4>
                    <p class="text-muted mb-0 fs-13">Total Partisipan</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="ri-user-voice-line"></i></div>
                <div>
                    <h4 class="mb-0 fw-bold" id="statSpeakers">0</h4>
                    <p class="text-muted mb-0 fs-13">Pembicara</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Advance Search -->
<div class="card mb-3">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between py-2">
        <h6 class="card-title mb-0"><i class="ri-filter-3-line me-2"></i>Advance Search</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
            data-bs-target="#tbmFilterBody" aria-expanded="true">
            <i class="ri-equalizer-line"></i>
        </button>
    </div>
    <div class="collapse show" id="tbmFilterBody">
        <div class="card-body">
            <form id="tbmFilterForm">
                <div class="row g-2">
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label form-label-sm mb-1">Tanggal Dari</label>
                        <input type="date" id="filterDateFrom" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label form-label-sm mb-1">Tanggal Sampai</label>
                        <input type="date" id="filterDateTo" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label form-label-sm mb-1">Bulan</label>
                        <input type="month" id="filterMonth" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label form-label-sm mb-1">Pembicara</label>
                        <select id="filterSpeaker" class="form-select form-select-sm tbm-filter-select"
                            data-placeholder="Semua Pembicara">
                            <option value="">Semua Pembicara</option>
                            @foreach ($filterOptions['speakers'] ?? [] as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label form-label-sm mb-1">Project</label>
                        <select id="filterProject" class="form-select form-select-sm tbm-filter-select"
                            data-placeholder="Semua Project">
                            <option value="">Semua Project</option>
                            @foreach ($filterOptions['projects'] ?? [] as $p)
                                <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label form-label-sm mb-1">Area Kerja / Dept</label>
                        <select id="filterLocation" class="form-select form-select-sm tbm-filter-select"
                            data-placeholder="Semua Area Kerja">
                            <option value="">Semua Area Kerja</option>
                            @foreach ($filterOptions['locations'] ?? [] as $l)
                                <option value="{{ $l->id }}">{{ $l->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-sm-12 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="ri-search-line me-1"></i>Terapkan Filter
                        </button>
                        <button type="button" id="tbmClearFilters" class="btn btn-outline-secondary btn-sm">
                            <i class="ri-refresh-line me-1"></i>Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- List table -->
<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <h4 class="header-title mb-0">Daftar TBM / Safety Talk</h4>
        <a href="{{ route('admin.tbm.index') }}?view=analytics" class="btn btn-soft-primary btn-sm">
            <i class="ri-line-chart-line me-1"></i>Trending Report
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tbmTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th width="4%">#</th>
                        <th width="15%">Tanggal &amp; Jam</th>
                        <th width="15%">Pembicara</th>
                        <th width="15%">Project</th>
                        <th width="13%">Area Kerja</th>
                        <th width="8%">Partisipan</th>
                        <th width="18%">Ringkasan</th>
                        <th width="6%">Foto</th>
                        <th width="6%">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
