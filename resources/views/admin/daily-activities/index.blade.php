@extends('admin.layouts')

@section('title', 'Daily Activity')

@push('cssSection')
    <style>
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .da-photo-thumb {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
        }

        .detail-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6c757d;
        }
    </style>
@endpush

@section('content')
    <div class="page-content">
        <!-- Page Title -->
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">Daily Activity <span class="text-muted fw-normal fs-15">— Laporan Aktifitas Harian HSE Personnel</span></h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">HSE Activity</a></li>
                    <li class="breadcrumb-item active">Daily Activity</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <!-- Stat cards -->
            <div class="row g-3 mb-3">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon bg-primary-subtle text-primary"><i class="ri-calendar-todo-line"></i></div>
                            <div>
                                <h4 class="mb-0 fw-bold" id="statTotal">0</h4>
                                <p class="text-muted mb-0 fs-13">Total Daily Activity</p>
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
                            <div class="stat-icon bg-info-subtle text-info"><i class="ri-list-check-2"></i></div>
                            <div>
                                <h4 class="mb-0 fw-bold" id="statTodo">0</h4>
                                <p class="text-muted mb-0 fs-13">Total To-do List</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon bg-warning-subtle text-warning"><i class="ri-user-star-line"></i></div>
                            <div>
                                <h4 class="mb-0 fw-bold" id="statPersonel">0</h4>
                                <p class="text-muted mb-0 fs-13">Personel</p>
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
                        data-bs-target="#daFilterBody" aria-expanded="true"><i class="ri-equalizer-line"></i></button>
                </div>
                <div class="collapse show" id="daFilterBody">
                    <div class="card-body">
                        <form id="daFilterForm">
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
                                    <label class="form-label form-label-sm mb-1">Personel (HSE Staff)</label>
                                    <select id="filterUser" class="form-select form-select-sm da-filter-select">
                                        <option value="">Semua Personel</option>
                                        @foreach ($hseStaff as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label form-label-sm mb-1">Project</label>
                                    <select id="filterProject" class="form-select form-select-sm da-filter-select">
                                        <option value="">Semua Project</option>
                                        @foreach ($projects as $p)
                                            <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label form-label-sm mb-1">Lokasi</label>
                                    <select id="filterLocation" class="form-select form-select-sm da-filter-select">
                                        <option value="">Semua Lokasi</option>
                                        @foreach ($locations as $l)
                                            <option value="{{ $l->id }}">{{ $l->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-12 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i>Terapkan Filter</button>
                                    <button type="button" id="daClearFilters" class="btn btn-outline-secondary btn-sm"><i class="ri-refresh-line me-1"></i>Reset</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                    <h4 class="header-title mb-0">Daftar Daily Activity</h4>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" onclick="exportDailyActivityExcel()">
                            <i class="ri-file-excel-2-line me-1"></i>Export Excel
                        </button>
                        <button type="button" class="btn btn-primary" onclick="createDailyActivity()">
                            <i class="ri-add-line me-1"></i>Assign Daily Activity
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="daTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                            <thead class="table-dark">
                                <tr>
                                    <th width="4%">#</th>
                                    <th width="14%">Tanggal &amp; Jam</th>
                                    <th width="16%">Personel</th>
                                    <th width="16%">Project</th>
                                    <th width="13%">Lokasi</th>
                                    <th width="17%">Deskripsi</th>
                                    <th width="10%">To-do</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Header Modal -->
    <div class="modal fade" id="daModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="daForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="daModalLabel">Assign Daily Activity</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="daId" name="id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Personel (HSE Staff) <span class="text-danger">*</span></label>
                                    <select class="form-select" id="daUser" name="user_id" required>
                                        <option value="">Pilih Personel</option>
                                        @foreach ($hseStaff as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}{{ $u->department ? ' — ' . $u->department : '' }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="user_idError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal &amp; Jam Aktivitas <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" id="daDatetime" name="datetime_activity" required>
                                    <div class="invalid-feedback" id="datetime_activityError"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Project <span class="text-danger">*</span></label>
                                    <select class="form-select" id="daProject" name="project_id" required>
                                        <option value="">Pilih Project</option>
                                        @foreach ($projects as $p)
                                            <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="project_idError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                                    <select class="form-select" id="daLocation" name="location_id" required>
                                        <option value="">Pilih Lokasi</option>
                                        @foreach ($locations as $l)
                                            <option value="{{ $l->id }}">{{ $l->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="location_idError"></div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="daDescription" name="description" rows="3" maxlength="2000"
                                placeholder="Deskripsi aktivitas (opsional)"></textarea>
                            <div class="invalid-feedback" id="descriptionError"></div>
                        </div>
                        <div class="alert alert-info py-2 mb-0 fs-13">
                            <i class="ri-information-line me-1"></i>To-do list (detail) diisi oleh personel HSE Staff melalui aplikasi mobile.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" id="daSubmitSpinner" role="status"></span>
                            <span id="daSubmitText">Save</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Detail Modal (header + to-do list, read-only) -->
    <div class="modal fade" id="daViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary-subtle">
                    <h5 class="modal-title text-primary"><i class="ri-clipboard-line me-1"></i>Detail Daily Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="daViewContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Lightbox -->
    <div class="modal fade" id="daImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-0 text-center bg-dark">
                    <img src="" id="daImageModalImg" class="img-fluid" style="max-height: 80vh;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('jsSection')
    <script>
        let daTable;
        let isEditMode = false;

        const STATUS_BADGE = {
            pending: 'secondary',
            in_progress: 'info',
            done: 'success',
            cancel: 'dark',
            rejected: 'danger'
        };

        $(document).ready(function() {
            loadStats();

            $('.da-filter-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true
            });

            $('#daUser, #daProject, #daLocation').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#daModal')
            });

            daTable = $('#daTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.daily-activities.data') }}",
                    type: 'GET',
                    data: function(d) {
                        d.date_from = $('#filterDateFrom').val();
                        d.date_to = $('#filterDateTo').val();
                        d.month = $('#filterMonth').val();
                        d.user_id = $('#filterUser').val();
                        d.project_id = $('#filterProject').val();
                        d.location_id = $('#filterLocation').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'datetime_formatted',
                        name: 'datetime_activity'
                    },
                    {
                        data: 'personel_name',
                        name: 'personel_name',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'project_name',
                        name: 'project_name',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'location_name',
                        name: 'location_name',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'description_short',
                        name: 'description'
                    },
                    {
                        data: 'todo_count',
                        name: 'todo_count',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                pageLength: 10,
                order: [
                    [1, 'desc']
                ]
            });

            $('#daFilterForm').on('submit', function(e) {
                e.preventDefault();
                daTable.ajax.reload();
            });

            $('#daClearFilters').on('click', function() {
                $('#daFilterForm')[0].reset();
                $('.da-filter-select').val('').trigger('change');
                daTable.ajax.reload();
            });

            $('#daForm').on('submit', function(e) {
                e.preventDefault();
                submitHeader();
            });

            $('#daModal').on('hidden.bs.modal', resetHeaderForm);
        });

        function loadStats() {
            $.get("{{ route('admin.daily-activities.statistics.data') }}", function(res) {
                if (res.success) {
                    $('#statTotal').text(res.data.total);
                    $('#statThisMonth').text(res.data.this_month);
                    $('#statTodo').text(res.data.total_todo);
                    $('#statPersonel').text(res.data.personel);
                }
            });
        }

        function exportDailyActivityExcel() {
            const map = {
                date_from: $('#filterDateFrom').val(),
                date_to: $('#filterDateTo').val(),
                month: $('#filterMonth').val(),
                user_id: $('#filterUser').val(),
                project_id: $('#filterProject').val(),
                location_id: $('#filterLocation').val()
            };
            const params = new URLSearchParams();
            Object.keys(map).forEach(k => {
                if (map[k]) params.append(k, map[k]);
            });
            const qs = params.toString();
            window.location.href = "{{ route('admin.daily-activities.export.excel') }}" + (qs ? ('?' + qs) : '');
        }

        function createDailyActivity() {
            isEditMode = false;
            $('#daModalLabel').text('Assign Daily Activity');
            $('#daSubmitText').text('Save');
            resetHeaderForm();
            $('#daModal').modal('show');
        }

        function editDailyActivity(id) {
            isEditMode = true;
            $('#daModalLabel').text('Edit Daily Activity');
            $('#daSubmitText').text('Update');
            resetHeaderForm();
            $('#daModal').modal('show');

            $.get(`{{ url('/') }}/admin/daily-activities/${id}`, function(res) {
                if (res.success) {
                    const d = res.data;
                    $('#daId').val(d.id);
                    $('#daUser').val(d.user_id).trigger('change');
                    $('#daProject').val(d.project_id).trigger('change');
                    $('#daLocation').val(d.location_id).trigger('change');
                    $('#daDatetime').val(d.datetime_activity);
                    $('#daDescription').val(d.description);
                } else {
                    showAlert('error', 'Error', res.message);
                }
            }).fail(() => showAlert('error', 'Error', 'Failed to load data'));
        }

        function submitHeader() {
            clearFormErrors();
            const payload = {
                user_id: $('#daUser').val(),
                datetime_activity: $('#daDatetime').val(),
                project_id: $('#daProject').val(),
                location_id: $('#daLocation').val(),
                description: $('#daDescription').val()
            };
            let url = isEditMode ? `{{ url('/') }}/admin/daily-activities/${$('#daId').val()}` :
                '{{ url('/') }}/admin/daily-activities';
            if (isEditMode) payload._method = 'PUT';

            showFormLoading(true);
            $.ajax({
                url: url,
                type: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res.success) {
                        showAlert('success', 'Success!', res.message);
                        $('#daModal').modal('hide');
                        daTable.ajax.reload();
                        loadStats();
                    } else {
                        showAlert('error', 'Error', res.message);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        displayFormErrors(xhr.responseJSON.errors);
                    } else {
                        const res = xhr.responseJSON || {};
                        showAlert('error', 'Error', res.message || 'Failed to save');
                    }
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function deleteDailyActivity(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Header beserta to-do list-nya akan dihapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('/') }}/admin/daily-activities/${id}?_method=delete`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            if (res.success) {
                                showAlert('success', 'Deleted!', res.message);
                                daTable.ajax.reload();
                                loadStats();
                            } else {
                                showAlert('error', 'Error', res.message);
                            }
                        },
                        error: function(xhr) {
                            const res = xhr.responseJSON || {};
                            showAlert('error', 'Error', res.message || 'Failed to delete');
                        }
                    });
                }
            });
        }

        function viewDailyActivity(id) {
            $('#daViewContent').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            $('#daViewModal').modal('show');

            $.get(`{{ url('/') }}/admin/daily-activities/${id}`, function(res) {
                if (res.success) {
                    renderDetail(res.data);
                } else {
                    $('#daViewContent').html('<p class="text-danger text-center">Failed to load.</p>');
                }
            }).fail(() => $('#daViewContent').html('<p class="text-danger text-center">Failed to load.</p>'));
        }

        function renderDetail(d) {
            let rows = '';
            if (d.details && d.details.length > 0) {
                d.details.forEach(function(it, i) {
                    let pics = (it.picture_urls && it.picture_urls.length) ?
                        it.picture_urls.map(u =>
                            `<img src="${u}" class="da-photo-thumb me-1 mb-1" onclick="showDaImage('${u}')">`).join('') :
                        '<span class="text-muted">-</span>';
                    rows += `
                        <tr>
                            <td class="text-center fw-bold">${i + 1}</td>
                            <td>${it.activity}</td>
                            <td style="white-space:pre-line">${it.todolist || '-'}</td>
                            <td>${it.activity_datetime || '-'}</td>
                            <td><span class="badge bg-${STATUS_BADGE[it.status] || 'secondary'}">${it.status_label}</span></td>
                            <td style="white-space:pre-line">${it.description_status || '-'}</td>
                            <td>${it.realization_datetime || '-'}</td>
                            <td>${pics}</td>
                        </tr>`;
                });
            } else {
                rows = '<tr><td colspan="8" class="text-center text-muted py-3">Belum ada to-do list dari personel</td></tr>';
            }

            $('#daViewContent').html(`
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><div class="detail-label">Personel</div><div class="fw-semibold">${d.personel ? d.personel.name : '-'}</div></div>
                    <div class="col-md-3"><div class="detail-label">Tanggal & Jam</div><div class="fw-semibold">${d.datetime_formatted || '-'}</div></div>
                    <div class="col-md-3"><div class="detail-label">Project</div><div class="fw-semibold">${d.project ? d.project.project_name : '-'}</div></div>
                    <div class="col-md-3"><div class="detail-label">Lokasi</div><div class="fw-semibold">${d.location ? d.location.name : '-'}</div></div>
                    <div class="col-12"><div class="detail-label">Deskripsi</div><div>${d.description || '<span class="text-muted">-</span>'}</div></div>
                </div>
                <h6 class="fw-bold mb-2"><i class="ri-list-check-2 me-1"></i>To-do List (${d.details ? d.details.length : 0})</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th><th>Activity</th><th>To-do</th><th>Tgl Kegiatan</th>
                                <th>Status</th><th>Deskripsi Status</th><th>Tgl Realisasi</th><th>Foto</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `);
        }

        function showDaImage(url) {
            $('#daImageModalImg').attr('src', url);
            $('#daImageModal').modal('show');
        }

        function resetHeaderForm() {
            $('#daForm')[0].reset();
            $('#daId').val('');
            $('#daUser, #daProject, #daLocation').val('').trigger('change');
            clearFormErrors();
            showFormLoading(false);
        }

        function showFormLoading(show) {
            if (show) {
                $('#daSubmitSpinner').removeClass('d-none');
                $('#daForm button[type="submit"]').prop('disabled', true);
            } else {
                $('#daSubmitSpinner').addClass('d-none');
                $('#daForm button[type="submit"]').prop('disabled', false);
            }
        }

        function clearFormErrors() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }

        function displayFormErrors(errors) {
            $.each(errors, function(field, messages) {
                const errorDiv = $(`#${field}Error`);
                errorDiv.text(messages[0]);
                errorDiv.closest('.mb-3').find('.form-control, .form-select').addClass('is-invalid');
            });
        }

        function showAlert(type, title, message) {
            Swal.fire({
                icon: type,
                title: title,
                text: message,
                showConfirmButton: true,
                timer: type === 'success' ? 3000 : null
            });
        }
    </script>
@endpush
