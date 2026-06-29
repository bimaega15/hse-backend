@extends('admin.layouts')

@section('title', 'HSE KPI')

@push('cssSection')
    <style>
        .kpi-detail-table td { vertical-align: middle; }
        .kpi-detail-table input, .kpi-detail-table select { min-width: 90px; }

        /* Pastikan modal body bisa di-scroll vertikal (override template) */
        #hseKpiModal .modal-dialog,
        #hseKpiViewModal .modal-dialog {
            max-height: calc(100vh - 3.5rem);
            margin-top: 1.75rem;
            margin-bottom: 1.75rem;
        }
        #hseKpiModal .modal-content,
        #hseKpiViewModal .modal-content {
            max-height: calc(100vh - 3.5rem);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        #hseKpiModal .modal-header,
        #hseKpiModal .modal-footer,
        #hseKpiViewModal .modal-header,
        #hseKpiViewModal .modal-footer {
            flex: 0 0 auto;
        }
        #hseKpiModal .modal-body,
        #hseKpiViewModal .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>
@endpush

@section('content')
    <div class="page-content">
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">HSE KPI</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">HSE Program (KPI)</a></li>
                    <li class="breadcrumb-item active">HSE</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body py-2">
                    <form id="kpiFilterForm" class="row g-2 align-items-end">
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label form-label-sm mb-1">Category</label>
                            <select id="fltCategory" class="form-select form-select-sm">
                                <option value="">All Category</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label form-label-sm mb-1">Project</label>
                            <select id="fltProject" class="form-select form-select-sm">
                                <option value="">All Project</option>
                                @foreach ($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label form-label-sm mb-1">Bulan</label>
                            <input type="month" id="fltMonth" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 col-sm-6 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i>Filter</button>
                            <button type="button" id="kpiClearFilter" class="btn btn-outline-secondary btn-sm"><i class="ri-refresh-line"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                    <h4 class="header-title mb-0">HSE KPI List</h4>
                    <button type="button" class="btn btn-primary" onclick="createHseKpi()">
                        <i class="ri-add-line me-1"></i>Add KPI
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="hseKpiTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                            <thead class="table-dark">
                                <tr>
                                    <th width="4%">#</th>
                                    <th width="15%">Category</th>
                                    <th width="20%">Project</th>
                                    <th width="11%">Periode</th>
                                    <th width="16%">Personel</th>
                                    <th width="8%">Indikator</th>
                                    <th width="9%">Average</th>
                                    <th width="9%">Nilai</th>
                                    <th width="8%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="hseKpiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form id="hseKpiForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="hseKpiModalLabel">Add KPI</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="hseKpiId">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Category KPI <span class="text-danger">*</span></label>
                                    <select class="form-select" id="kpiCategory" required>
                                        <option value="">Pilih Category</option>
                                        @foreach ($categories as $c)
                                            <option value="{{ $c->id }}">{{ $c->category_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Project <span class="text-danger">*</span></label>
                                    <select class="form-select" id="kpiProject" required>
                                        <option value="">Pilih Project</option>
                                        @foreach ($projects as $p)
                                            <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Periode Laporan <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="kpiReportDate" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Personel (HSE Staff)</label>
                                    <select class="form-select" id="kpiUsers" multiple>
                                        @foreach ($hseStaff as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}{{ $u->department ? ' — ' . $u->department : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <input type="text" class="form-control" id="kpiDescription" maxlength="2000" placeholder="opsional">
                                </div>
                            </div>
                        </div>

                        <!-- Indicators repeater -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0"><i class="ri-list-check-2 me-1"></i>Indikator KPI</h6>
                            <button type="button" class="btn btn-sm btn-soft-primary" onclick="addKpiDetailRow()"><i class="ri-add-line me-1"></i>Tambah Indikator</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm kpi-detail-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="36%">Activity / Indicator</th>
                                        <th width="16%">Type Target</th>
                                        <th width="14%">Target</th>
                                        <th width="14%">Realisasi</th>
                                        <th width="10%">Nilai</th>
                                        <th width="6%"></th>
                                    </tr>
                                </thead>
                                <tbody id="kpiDetailRows"></tbody>
                            </table>
                        </div>

                        <!-- Rumus (advanced) -->
                        <div class="mt-3">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#rumusBox">
                                <i class="ri-code-s-slash-line me-1"></i>Rumus Penilaian (JSON) — advanced
                            </button>
                            <div class="collapse mt-2" id="rumusBox">
                                <textarea class="form-control font-monospace" id="kpiRumus" rows="8" style="font-size:.8rem"></textarea>
                                <div class="form-text">Range band nilai (sangat baik, baik, cukup, kurang, kurang baik). Otomatis terisi sesuai category; bisa diubah.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" id="hseKpiSpinner" role="status"></span>
                            <span id="hseKpiSubmitText">Save KPI</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="hseKpiViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary-subtle">
                    <h5 class="modal-title text-primary"><i class="ri-bar-chart-box-line me-1"></i>Detail HSE KPI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="hseKpiViewContent"></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
            </div>
        </div>
    </div>
@endsection

@push('jsSection')
    <script>
        const TYPE_TARGETS = @json($typeTargets);
        const DEFAULT_RUMUS = @json($defaultRumus);
        const NILAI_BADGE = {
            'sangat baik': 'bg-success', 'baik': 'bg-primary', 'cukup': 'bg-info text-dark',
            'kurang': 'bg-warning text-dark', 'kurang baik': 'bg-danger'
        };
        let hseKpiTable, isEditMode = false;

        $(document).ready(function() {
            $('#kpiProject, #kpiCategory').select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $('#hseKpiModal') });
            $('#kpiUsers').select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $('#hseKpiModal'), placeholder: 'Pilih personel (bisa lebih dari satu)' });

            hseKpiTable = $('#hseKpiTable').DataTable({
                processing: true, serverSide: true, responsive: true,
                ajax: {
                    url: "{{ route('admin.kpi.hse.data') }}",
                    data: function(d) {
                        d.category_kpi_id = $('#fltCategory').val();
                        d.project_id = $('#fltProject').val();
                        d.month = $('#fltMonth').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'category_name', name: 'category_name', orderable: false },
                    { data: 'project_name', name: 'project_name', orderable: false },
                    { data: 'report_date_formatted', name: 'report_date' },
                    { data: 'users_display', name: 'users_display', orderable: false, searchable: false },
                    { data: 'detail_count', name: 'detail_count', orderable: false, searchable: false },
                    { data: 'average_display', name: 'average' },
                    { data: 'nilai_badge', name: 'nilai_badge', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false }
                ],
                order: [[3, 'desc']], pageLength: 10
            });

            $('#kpiFilterForm').on('submit', function(e) { e.preventDefault(); hseKpiTable.ajax.reload(); });
            $('#kpiClearFilter').on('click', function() { $('#kpiFilterForm')[0].reset(); hseKpiTable.ajax.reload(); });
            $('#kpiCategory').on('change', onCategoryChange);
            $('#hseKpiForm').on('submit', function(e) { e.preventDefault(); submitHseKpi(); });
            $('#hseKpiModal').on('hidden.bs.modal', resetHseKpiForm);
        });

        function onCategoryChange() {
            const id = $('#kpiCategory').val();
            if (!id) return;
            $.get("{{ route('admin.kpi.hse.default-rumus') }}", { category_kpi_id: id }, function(res) {
                if (res.success) {
                    $('#kpiRumus').val(JSON.stringify(res.data.rumus, null, 2));
                    recalcAllRows();
                }
            });
        }

        function createHseKpi() {
            isEditMode = false;
            $('#hseKpiModalLabel').text('Add KPI');
            $('#hseKpiSubmitText').text('Save KPI');
            resetHseKpiForm();
            addKpiDetailRow();
            $('#hseKpiModal').modal('show');
        }

        function editHseKpi(id) {
            isEditMode = true;
            $('#hseKpiModalLabel').text('Edit KPI');
            $('#hseKpiSubmitText').text('Update KPI');
            resetHseKpiForm();
            $('#hseKpiModal').modal('show');
            $.get(`{{ url('/') }}/admin/kpi/hse/${id}`, function(res) {
                if (!res.success) return kpiAlert('error', 'Error', res.message);
                const d = res.data;
                $('#hseKpiId').val(d.id);
                $('#kpiCategory').val(d.category_kpi_id).trigger('change.select2');
                $('#kpiProject').val(d.project_id).trigger('change.select2');
                $('#kpiUsers').val(d.users_id).trigger('change.select2');
                $('#kpiReportDate').val(d.report_date);
                $('#kpiDescription').val(d.description);
                $('#kpiRumus').val(JSON.stringify(d.rumus, null, 2));
                $('#kpiDetailRows').empty();
                (d.details || []).forEach(addKpiDetailRow);
                if (!d.details || d.details.length === 0) addKpiDetailRow();
            });
        }

        function addKpiDetailRow(data) {
            data = data || {};
            const typeOpts = TYPE_TARGETS.map(t => `<option value="${t}" ${data.type_target === t ? 'selected' : ''}>${t}</option>`).join('');
            const row = $(`
                <tr>
                    <td><input type="hidden" class="d-id" value="${data.id || ''}">
                        <input type="text" class="form-control form-control-sm d-name" value="${data.activity_name ? String(data.activity_name).replace(/"/g,'&quot;') : ''}" placeholder="Nama indikator" required></td>
                    <td><select class="form-select form-select-sm d-type">${typeOpts}</select></td>
                    <td><input type="number" step="any" class="form-control form-control-sm d-target" value="${data.target ?? ''}" required></td>
                    <td><input type="number" step="any" class="form-control form-control-sm d-real" value="${data.realisasi ?? ''}"></td>
                    <td class="d-nilai text-center"><span class="text-muted">-</span></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-soft-danger" onclick="$(this).closest('tr').remove()"><i class="ri-delete-bin-line"></i></button></td>
                </tr>`);
            row.find('.d-type, .d-target, .d-real').on('input change', function() { recalcRow(row); });
            $('#kpiDetailRows').append(row);
            recalcRow(row);
        }

        // Client-side nilai preview using the rumus textarea + scoring rules
        function recalcRow($row) {
            const type = $row.find('.d-type').val();
            const target = parseFloat($row.find('.d-target').val());
            const realRaw = $row.find('.d-real').val();
            const $cell = $row.find('.d-nilai');
            if (realRaw === '' || isNaN(target)) { $cell.html('<span class="text-muted">-</span>'); return; }
            const real = parseFloat(realRaw);
            let rumus;
            try { rumus = JSON.parse($('#kpiRumus').val() || '[]'); } catch (e) { rumus = []; }
            const key = (rumus[0] && rumus[0].category) || 'leading_indicator';
            const results = (rumus[0] && rumus[0].results) || [];
            let value;
            if (key === 'lagging_indicator') { value = real; }
            else { value = target > 0 ? Math.min(real / target * 100, 100) : 0; }
            const band = matchBand(value, results);
            if (band) { $cell.html(`<span class="badge ${NILAI_BADGE[band] || 'bg-secondary'}">${band}</span>`); }
            else { $cell.html('<span class="text-muted">-</span>'); }
        }

        function recalcAllRows() { $('#kpiDetailRows tr').each(function() { recalcRow($(this)); }); }

        function matchBand(value, results) {
            value = Math.round(value);
            let last = null;
            for (const row of results) {
                for (const band in row) {
                    last = band;
                    if (predicate(row[band])(value)) return band;
                }
            }
            return last;
        }
        function predicate(spec) {
            if (typeof spec === 'number') return v => v === spec;
            const s = String(spec).trim();
            let m;
            if ((m = s.match(/^(-?\d+(?:\.\d+)?)\s*-\s*(-?\d+(?:\.\d+)?)$/))) return v => v >= parseFloat(m[1]) && v <= parseFloat(m[2]);
            if ((m = s.match(/^>=\s*(-?\d+(?:\.\d+)?)$/))) return v => v >= parseFloat(m[1]);
            if ((m = s.match(/^<=\s*(-?\d+(?:\.\d+)?)$/))) return v => v <= parseFloat(m[1]);
            if ((m = s.match(/^>\s*(-?\d+(?:\.\d+)?)$/))) return v => v > parseFloat(m[1]);
            if ((m = s.match(/^<\s*(-?\d+(?:\.\d+)?)$/))) return v => v < parseFloat(m[1]);
            if (!isNaN(parseFloat(s))) return v => v === parseFloat(s);
            return () => false;
        }

        function collectDetails() {
            const details = [];
            $('#kpiDetailRows tr').each(function() {
                const name = $(this).find('.d-name').val().trim();
                if (!name) return;
                details.push({
                    id: $(this).find('.d-id').val() || null,
                    activity_name: name,
                    type_target: $(this).find('.d-type').val(),
                    target: $(this).find('.d-target').val(),
                    realisasi: $(this).find('.d-real').val()
                });
            });
            return details;
        }

        function submitHseKpi() {
            const payload = {
                category_kpi_id: $('#kpiCategory').val(),
                project_id: $('#kpiProject').val(),
                users_id: $('#kpiUsers').val() || [],
                report_date: $('#kpiReportDate').val(),
                description: $('#kpiDescription').val(),
                rumus: $('#kpiRumus').val(),
                details: collectDetails()
            };
            let url = isEditMode ? `{{ url('/') }}/admin/kpi/hse/${$('#hseKpiId').val()}` : '{{ url('/') }}/admin/kpi/hse';
            if (isEditMode) payload._method = 'PUT';
            $('#hseKpiSpinner').removeClass('d-none');
            $.ajax({
                url, type: 'POST', data: JSON.stringify(payload), contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    if (res.success) { kpiAlert('success', 'Success!', res.message); $('#hseKpiModal').modal('hide'); hseKpiTable.ajax.reload(); }
                    else { kpiAlert('error', 'Error', res.message); }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errs = xhr.responseJSON.errors;
                        kpiAlert('error', 'Validation', Object.values(errs)[0][0]);
                    } else { kpiAlert('error', 'Error', (xhr.responseJSON || {}).message || 'Failed'); }
                },
                complete: function() { $('#hseKpiSpinner').addClass('d-none'); }
            });
        }

        function deleteHseKpi(id) {
            Swal.fire({ title: 'Are you sure?', text: 'KPI beserta indikatornya akan dihapus!', icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!'
            }).then((r) => {
                if (r.isConfirmed) {
                    $.ajax({
                        url: `{{ url('/') }}/admin/kpi/hse/${id}?_method=delete`, type: 'POST',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function(res) {
                            if (res.success) { kpiAlert('success', 'Deleted!', res.message); hseKpiTable.ajax.reload(); }
                            else { kpiAlert('error', 'Error', res.message); }
                        }
                    });
                }
            });
        }

        function viewHseKpi(id) {
            $('#hseKpiViewContent').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            $('#hseKpiViewModal').modal('show');
            $.get(`{{ url('/') }}/admin/kpi/hse/${id}`, function(res) {
                if (!res.success) return $('#hseKpiViewContent').html('<p class="text-danger text-center">Failed</p>');
                const d = res.data;
                let rows = '';
                (d.details || []).forEach((it, i) => {
                    const band = it.nilai_label;
                    rows += `<tr>
                        <td class="text-center">${i + 1}</td>
                        <td>${it.activity_name}</td>
                        <td class="text-center">${it.target_display}</td>
                        <td class="text-center">${it.realisasi ?? '-'}</td>
                        <td class="text-center">${it.percentage !== null ? it.percentage + '%' : '-'}</td>
                        <td class="text-center">${band ? `<span class="badge ${NILAI_BADGE[band] || 'bg-secondary'}">${band}</span>` : '-'}</td>
                    </tr>`;
                });
                const overall = d.overall_nilai;
                $('#hseKpiViewContent').html(`
                    <div class="row g-3 mb-3">
                        <div class="col-md-3"><small class="text-muted d-block">Category</small><b>${d.category_name}</b></div>
                        <div class="col-md-4"><small class="text-muted d-block">Project</small><b>${d.project_name}</b></div>
                        <div class="col-md-2"><small class="text-muted d-block">Periode</small><b>${d.report_date}</b></div>
                        <div class="col-md-3"><small class="text-muted d-block">Personel</small><b>${(d.assigned_users || []).map(u => u.name).join(', ') || '-'}</b></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light"><tr><th>No</th><th>Indicator</th><th>Target</th><th>Realisasi</th><th>% Pencapaian</th><th>Nilai</th></tr></thead>
                            <tbody>${rows}</tbody>
                            <tfoot><tr class="table-light fw-bold">
                                <td colspan="4" class="text-end">RATA-RATA NILAI</td>
                                <td class="text-center">${d.average !== null ? d.average + '%' : '-'}</td>
                                <td class="text-center">${overall ? `<span class="badge ${NILAI_BADGE[overall] || 'bg-secondary'}">${overall}</span>` : '-'}</td>
                            </tr></tfoot>
                        </table>
                    </div>`);
            });
        }

        function resetHseKpiForm() {
            $('#hseKpiForm')[0].reset();
            $('#hseKpiId').val('');
            $('#kpiCategory, #kpiProject').val('').trigger('change.select2');
            $('#kpiUsers').val(null).trigger('change.select2');
            $('#kpiRumus').val('');
            $('#kpiDetailRows').empty();
        }

        function kpiAlert(type, title, message) {
            Swal.fire({ icon: type, title, text: message, timer: type === 'success' ? 2500 : null });
        }
    </script>
@endpush
