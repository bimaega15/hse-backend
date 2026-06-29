@extends('admin.layouts')

@section('title', 'Category KPI')

@section('content')
    <div class="page-content">
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">Category KPI</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">HSE Program (KPI)</a></li>
                    <li class="breadcrumb-item active">Category KPI</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <div class="card">
                <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                    <h4 class="header-title mb-0">Category KPI List</h4>
                    <button type="button" class="btn btn-primary" onclick="createCategoryKpi()">
                        <i class="ri-add-line me-1"></i>Add Category
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="catKpiTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="40%">Category Name</th>
                                    <th width="15%">Status</th>
                                    <th width="15%">KPI Count</th>
                                    <th width="15%">Created At</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="catKpiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="catKpiForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="catKpiModalLabel">Add Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="catKpiId" name="id">
                        <div class="mb-3">
                            <label class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="catKpiName" name="category_name" required
                                maxlength="255" placeholder="mis. Lagging Indicator / Leading Indicator">
                            <div class="invalid-feedback" id="category_nameError"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="catKpiStatus" name="status" required>
                                <option value="active">Active</option>
                                <option value="not active">Not Active</option>
                            </select>
                            <div class="invalid-feedback" id="statusError"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" id="catKpiSpinner" role="status"></span>
                            <span id="catKpiSubmitText">Save</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('jsSection')
    <script>
        let catKpiTable, isEditMode = false;

        $(document).ready(function() {
            catKpiTable = $('#catKpiTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.kpi.categories.data') }}",
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'kpi_count', name: 'kpi_count', orderable: false, searchable: false },
                    { data: 'created_at_formatted', name: 'created_at' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                order: [[4, 'desc']],
                pageLength: 10
            });

            $('#catKpiForm').on('submit', function(e) { e.preventDefault(); submitCatKpi(); });
            $('#catKpiModal').on('hidden.bs.modal', resetCatKpiForm);
        });

        function createCategoryKpi() {
            isEditMode = false;
            $('#catKpiModalLabel').text('Add Category');
            $('#catKpiSubmitText').text('Save');
            resetCatKpiForm();
            $('#catKpiModal').modal('show');
        }

        function editCategoryKpi(id) {
            isEditMode = true;
            $('#catKpiModalLabel').text('Edit Category');
            $('#catKpiSubmitText').text('Update');
            resetCatKpiForm();
            $('#catKpiModal').modal('show');
            $.get(`{{ url('/') }}/admin/kpi/categories/${id}`, function(res) {
                if (res.success) {
                    $('#catKpiId').val(res.data.id);
                    $('#catKpiName').val(res.data.category_name);
                    $('#catKpiStatus').val(res.data.status);
                }
            });
        }

        function submitCatKpi() {
            clearKpiErrors();
            const fd = new FormData($('#catKpiForm')[0]);
            let url = isEditMode ? `{{ url('/') }}/admin/kpi/categories/${$('#catKpiId').val()}` : '{{ url('/') }}/admin/kpi/categories';
            if (isEditMode) { fd.append('_method', 'PUT'); url += '?_method=PUT'; }
            $('#catKpiSpinner').removeClass('d-none');
            $.ajax({
                url, type: 'POST', data: fd, processData: false, contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    if (res.success) {
                        kpiAlert('success', 'Success!', res.message);
                        $('#catKpiModal').modal('hide');
                        catKpiTable.ajax.reload();
                    } else { kpiAlert('error', 'Error', res.message); }
                },
                error: function(xhr) {
                    if (xhr.status === 422) { showKpiErrors(xhr.responseJSON.errors); }
                    else { kpiAlert('error', 'Error', (xhr.responseJSON || {}).message || 'Failed'); }
                },
                complete: function() { $('#catKpiSpinner').addClass('d-none'); }
            });
        }

        function deleteCategoryKpi(id) {
            Swal.fire({ title: 'Are you sure?', text: 'This action cannot be undone!', icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!'
            }).then((r) => {
                if (r.isConfirmed) {
                    $.ajax({
                        url: `{{ url('/') }}/admin/kpi/categories/${id}?_method=delete`, type: 'POST',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function(res) {
                            if (res.success) { kpiAlert('success', 'Deleted!', res.message); catKpiTable.ajax.reload(); }
                            else { kpiAlert('error', 'Error', res.message); }
                        },
                        error: function(xhr) { kpiAlert('error', 'Error', (xhr.responseJSON || {}).message || 'Failed'); }
                    });
                }
            });
        }

        function resetCatKpiForm() { $('#catKpiForm')[0].reset(); $('#catKpiId').val(''); clearKpiErrors(); }
        function clearKpiErrors() { $('.is-invalid').removeClass('is-invalid'); $('.invalid-feedback').text(''); }
        function showKpiErrors(errors) {
            $.each(errors, function(field, msgs) {
                const div = $(`#${field.replace('.', '_')}Error`);
                div.text(msgs[0]); div.closest('.mb-3').find('.form-control,.form-select').addClass('is-invalid');
            });
        }
        function kpiAlert(type, title, message) {
            Swal.fire({ icon: type, title, text: message, timer: type === 'success' ? 2500 : null });
        }
    </script>
@endpush
