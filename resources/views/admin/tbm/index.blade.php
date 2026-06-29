@extends('admin.layouts')

@section('title', 'TBM / Safety Talk')

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

        .analytics-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
        }

        .tbm-photo-thumb {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: transform .2s ease;
        }

        .tbm-photo-thumb:hover {
            transform: scale(1.04);
        }

        .detail-label {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6c757d;
            margin-bottom: .15rem;
        }
    </style>
@endpush

@section('content')
    <div class="page-content">
        <!-- Page Title -->
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">
                    TBM / Safety Talk
                    @if ($view === 'analytics')
                        <span class="text-muted fw-normal">- Trending Report</span>
                    @endif
                </h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">BAIK Management</a></li>
                    <li class="breadcrumb-item active">TBM / Safety Talk</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            @if ($view === 'analytics')
                @include('admin.tbm.partials.analytics')
            @else
                @include('admin.tbm.partials.list')
            @endif
        </div>
    </div>

    <!-- View Detail Modal (read-only) -->
    <div class="modal fade" id="viewTbmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary-subtle">
                    <h5 class="modal-title text-primary">
                        <i class="ri-megaphone-line me-1"></i> TBM / Safety Talk Detail
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="tbmDetailContent">
                    <!-- filled by JS -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Lightbox Modal -->
    <div class="modal fade" id="tbmImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-0 text-center bg-dark">
                    <img src="" id="tbmImageModalImg" class="img-fluid" style="max-height: 80vh;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('jsSection')
    <script>
        @if ($view !== 'analytics')
            let tbmTable;

            $(document).ready(function() {
                loadTbmStatistics();
                initTbmFilterSelect2();
                initTbmTable();

                $('#tbmFilterForm').on('submit', function(e) {
                    e.preventDefault();
                    tbmTable.ajax.reload();
                });

                $('#tbmClearFilters').on('click', function() {
                    $('#tbmFilterForm')[0].reset();
                    $('.tbm-filter-select').val('').trigger('change');
                    tbmTable.ajax.reload();
                });
            });

            function loadTbmStatistics() {
                $.get("{{ route('admin.tbm.statistics.data') }}", function(res) {
                    if (res.success) {
                        $('#statTotalTbm').text(res.data.total_tbm);
                        $('#statThisMonth').text(res.data.this_month_tbm);
                        $('#statParticipants').text(res.data.total_participants);
                        $('#statSpeakers').text(res.data.total_speakers);
                    }
                });
            }

            function initTbmFilterSelect2() {
                $('.tbm-filter-select').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    allowClear: true,
                    placeholder: function() {
                        return $(this).data('placeholder') || 'All';
                    }
                });
            }

            function initTbmTable() {
                tbmTable = $('#tbmTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('admin.tbm.data') }}",
                        type: 'GET',
                        data: function(d) {
                            d.date_from = $('#filterDateFrom').val();
                            d.date_to = $('#filterDateTo').val();
                            d.month = $('#filterMonth').val();
                            d.speaker = $('#filterSpeaker').val();
                            d.project = $('#filterProject').val();
                            d.location = $('#filterLocation').val();
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'date_time_formatted',
                            name: 'date_time_tbm'
                        },
                        {
                            data: 'speaker_name',
                            name: 'speaker_name',
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
                            data: 'participant_badge',
                            name: 'participant_count'
                        },
                        {
                            data: 'summary_short',
                            name: 'summary_topic'
                        },
                        {
                            data: 'photos_badge',
                            name: 'photos_badge',
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
                    language: {
                        processing: "Loading...",
                        search: "Search:",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        infoFiltered: "(filtered from _MAX_ total entries)",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        }
                    },
                    pageLength: 10,
                    order: [
                        [1, 'desc']
                    ]
                });
            }
        @endif

        // --- Shared: view detail modal (works in both views) ---
        function viewTbm(id) {
            $('#tbmDetailContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>`);
            $('#viewTbmModal').modal('show');

            $.ajax({
                url: `{{ url('/') }}/admin/tbm/${id}`,
                type: 'GET',
                success: function(res) {
                    if (res.success) {
                        renderTbmDetail(res.data);
                    } else {
                        $('#tbmDetailContent').html(
                            '<p class="text-danger text-center mb-0">Failed to load detail.</p>');
                    }
                },
                error: function() {
                    $('#tbmDetailContent').html(
                        '<p class="text-danger text-center mb-0">Failed to load detail.</p>');
                }
            });
        }

        function renderTbmDetail(d) {
            const speaker = d.speaker ? d.speaker.name : '-';
            const speakerDept = d.speaker && d.speaker.department ? `<small class="text-muted d-block">${d.speaker.department}</small>` : '';
            const project = d.project ? d.project.project_name : '-';
            const location = d.location ? d.location.name : '-';

            let photos = '';
            if (d.activity_picture_urls && d.activity_picture_urls.length > 0) {
                photos = d.activity_picture_urls.map(url =>
                    `<img src="${url}" class="tbm-photo-thumb me-2 mb-2" onclick="showTbmImage('${url}')">`
                ).join('');
            } else {
                photos = '<span class="text-muted">Tidak ada foto kegiatan</span>';
            }

            $('#tbmDetailContent').html(`
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="detail-label">Tanggal & Jam</div>
                        <div class="fw-semibold"><i class="ri-calendar-event-line me-1 text-primary"></i>${d.date_time_formatted || '-'}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Jumlah Partisipan</div>
                        <div class="fw-semibold"><i class="ri-group-line me-1 text-primary"></i>${d.participant_count ?? 0} orang</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Pembicara</div>
                        <div class="fw-semibold">${speaker}</div>${speakerDept}
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Project</div>
                        <div class="fw-semibold">${project}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Area Kerja / Dept</div>
                        <div class="fw-semibold">${location}</div>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <div class="detail-label">Ringkasan Pembicaraan (Topik)</div>
                    <div>${d.summary_topic ? d.summary_topic.replace(/\n/g, '<br>') : '<span class="text-muted">-</span>'}</div>
                </div>
                <div>
                    <div class="detail-label mb-2">Foto Kegiatan TBM</div>
                    <div class="d-flex flex-wrap">${photos}</div>
                </div>
            `);
        }

        function showTbmImage(url) {
            $('#tbmImageModalImg').attr('src', url);
            $('#tbmImageModal').modal('show');
        }
    </script>
@endpush
