<!-- Create/Edit Observation Modal -->
<div class="modal fade" id="observationModal" tabindex="-1" aria-labelledby="observationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="observationForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="observationModalLabel">Add New Observation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="observationId" name="id">

                    <!-- Observer Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="userId" class="form-label">Observer <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="userId" name="user_id" required>
                                    <option value="">Select Observer</option>
                                </select>
                                <div class="invalid-feedback" id="userIdError"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="waktuObservasi" class="form-label">Observation Time <span
                                        class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="waktuObservasi" name="waktu_observasi"
                                    required>
                                <div class="invalid-feedback" id="waktuObservasiError"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Time Duration -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="waktuMulai" class="form-label">Start Time <span
                                        class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="waktuMulai" name="waktu_mulai" required>
                                <div class="invalid-feedback" id="waktuMulaiError"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="waktuSelesai" class="form-label">End Time <span
                                        class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="waktuSelesai" name="waktu_selesai"
                                    required>
                                <div class="invalid-feedback" id="waktuSelesaiError"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">General Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="1000"
                            placeholder="General notes about the observation session (optional)"></textarea>
                        <div class="form-text">Maximum 1000 characters</div>
                        <div class="invalid-feedback" id="notesError"></div>
                    </div>

                    <!-- Observation Details Section -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Observation Details <span class="text-danger">*</span></h6>
                            <button type="button" class="btn btn-primary btn-sm" onclick="addObservationDetail()">
                                <i class="ri-add-line me-1"></i>Add Detail
                            </button>
                        </div>
                        <div id="observationDetails">
                            <!-- Observation details will be added here dynamically -->
                        </div>

                        <!-- Bottom Add Detail Button -->
                        <div class="text-center mt-3 mb-3" id="bottomAddDetailButton" style="display: none;">
                            <button type="button" class="btn btn-outline-primary" onclick="addObservationDetail()">
                                <i class="ri-add-line me-1"></i>Add Another Detail
                            </button>
                        </div>

                        <div class="text-muted small mt-2">
                            <i class="ri-information-line me-1"></i>Add at least one observation detail before saving
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status"></span>
                        <span id="submitText">Save Observation</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Observation Modal -->
<div class="modal fade" id="viewObservationModal" tabindex="-1" aria-labelledby="viewObservationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewObservationModalLabel">Observation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Observation details will be loaded here -->
                <div id="observationDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" onclick="editObservationFromView()"
                    id="editObservationBtn">
                    <i class="ri-edit-line me-1"></i>Edit Observation
                </button>
                <div class="btn-group" role="group" id="statusUpdateButtons">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="ri-check-line me-1"></i>Update Status
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="updateObservationStatus('submitted')"
                                id="submitBtn">Submit for Review</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateObservationStatus('reviewed')"
                                id="reviewBtn">Mark as Reviewed</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="statusForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Update Observation Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="statusObservationId" name="observation_id">
                    <input type="hidden" id="newStatus" name="status">

                    <div class="mb-3">
                        <label class="form-label">New Status:</label>
                        <p class="fw-bold" id="statusDisplay"></p>
                    </div>

                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        <span id="statusDescription"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Observation Detail Template -->
<template id="observationDetailTemplate">
    <div class="observation-detail-item border rounded p-3 mb-3" data-detail-index="">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Detail <span class="detail-number"></span></h6>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeObservationDetail(this)">
                <i class="ri-delete-bin-line me-1"></i>Remove
            </button>
        </div>

        <div class="row">
            <!-- Observation Type -->
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Observation Type <span class="text-danger">*</span></label>
                    <select class="form-select observation-type-select" name="details[INDEX][observation_type]" required onchange="handleObservationTypeChange(this)">
                        <option value="">Select Observation Type</option>
                        <option value="at_risk_behavior">At Risk Behavior</option>
                        <option value="nearmiss_incident">Nearmiss Incident</option>
                        <option value="informal_risk_mgmt">Informal Risk Management</option>
                        <option value="sim_k3">SIM K3</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Category -->
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select" name="details[INDEX][category_id]" required>
                        <option value="">Select Category</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Contributing Factor -->
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Contributing Factor <span class="text-danger">*</span></label>
                    <select class="form-select" name="details[INDEX][contributing_id]" required>
                        <option value="">Select Contributing Factor</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Action -->
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Action <span class="text-danger">*</span></label>
                    <select class="form-select" name="details[INDEX][action_id]" required>
                        <option value="">Select Action</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Severity Rating -->
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Severity Rating <span class="text-danger">*</span></label>
                    <select class="form-select" name="details[INDEX][severity]" required>
                        <option value="">Select Severity</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Location -->
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Location <span class="text-danger">*</span></label>
                    <select class="form-select" name="details[INDEX][location_id]" required>
                        <option value="">Select Location</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Report Date -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Report Date <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control" name="details[INDEX][report_date]" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Project -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Project</label>
                    <select class="form-select" name="details[INDEX][project_id]">
                        <option value="">Select Project (Optional)</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>

        <!-- Activator Field (only for At Risk Behavior) -->
        <div class="row activator-row" style="display: none;">
            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label">Activator <span class="text-danger">*</span></label>
                    <select class="form-select" name="details[INDEX][activator_id]">
                        <option value="">Select Activator</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="mb-3">
            <label class="form-label">Description <span class="text-danger">*</span></label>
            <textarea class="form-control" name="details[INDEX][description]" rows="4" required maxlength="2000" placeholder="Describe the observation in detail"></textarea>
            <div class="form-text">Maximum 2000 characters</div>
            <div class="invalid-feedback"></div>
        </div>

        <!-- Action Taken -->
        <div class="mb-3">
            <label class="form-label">Action Taken</label>
            <textarea class="form-control" name="details[INDEX][action_taken]" rows="3" maxlength="1000" placeholder="Describe immediate actions taken (optional)"></textarea>
            <div class="form-text">Maximum 1000 characters</div>
            <div class="invalid-feedback"></div>
        </div>

        <!-- Images -->
        <div class="mb-3">
            <label class="form-label">Images</label>
            <input type="file" class="form-control" name="details[INDEX][images][]" multiple accept="image/*">
            <div class="form-text">You can upload multiple images (JPEG, PNG, JPG, GIF). Maximum 2MB per file.</div>
            <div class="invalid-feedback"></div>
            <div class="image-preview mt-2"></div>
        </div>
    </div>
</template>

<script>
    // Modal-specific JavaScript functions
    document.addEventListener('DOMContentLoaded', function() {
        // Update modal buttons based on observation status when viewing
        $('#viewObservationModal').on('show.bs.modal', function() {
            // This will be called when the modal is about to show
            // Button visibility will be updated in renderObservationDetails function
        });

        // Handle status update descriptions
        $('#newStatus').on('change', function() {
            const status = $(this).val();
            let description = '';

            switch (status) {
                case 'submitted':
                    description =
                        'This observation will be submitted for BAIK staff review. Once submitted, it cannot be edited until reviewed.';
                    break;
                case 'reviewed':
                    description =
                        'This observation will be marked as reviewed and completed. This action indicates BAIK staff has reviewed the observation.';
                    break;
                default:
                    description = 'Status will be updated.';
            }

            $('#statusDescription').text(description);
        });
    });

    // Function to update modal buttons based on observation status
    function updateModalButtons(observation) {
        const editBtn = $('#editObservationBtn');
        const statusButtons = $('#statusUpdateButtons');
        const submitBtn = $('#submitBtn');
        const reviewBtn = $('#reviewBtn');

        // Show/hide edit button based on status
        if (observation.status === 'draft') {
            editBtn.show();
        } else {
            editBtn.hide();
        }

        // Show/hide status update buttons based on status
        if (observation.status === 'draft') {
            statusButtons.show();
            submitBtn.show();
            reviewBtn.hide();
        } else if (observation.status === 'submitted') {
            statusButtons.show();
            submitBtn.hide();
            reviewBtn.show();
        } else {
            statusButtons.hide();
        }
    }

    // Enhanced renderObservationDetails function to handle button visibility
    const originalRenderObservationDetails = window.renderObservationDetails;
    window.renderObservationDetails = function(observation) {
        // Call the original function
        originalRenderObservationDetails(observation);

        // Update modal buttons
        updateModalButtons(observation);
    };

    // Observation Detail Management Functions
    let observationDetailIndex = 0;
    let categories = [];
    let contributings = [];
    let actions = [];
    let locations = [];
    let projects = [];
    let activators = [];

    // Add new observation detail
    function addObservationDetail() {
        const template = document.getElementById('observationDetailTemplate');
        const clone = template.content.cloneNode(true);

        // Replace INDEX placeholders with actual index
        const html = clone.querySelector('.observation-detail-item').outerHTML.replace(/INDEX/g, observationDetailIndex);

        // Create element and set innerHTML
        const div = document.createElement('div');
        div.innerHTML = html;
        const detailElement = div.firstChild;

        // Set data attribute and detail number
        detailElement.setAttribute('data-detail-index', observationDetailIndex);
        detailElement.querySelector('.detail-number').textContent = observationDetailIndex + 1;

        // Set default report date to current datetime
        const reportDateInput = detailElement.querySelector('input[name*="[report_date]"]');
        const now = new Date();
        const localISOTime = new Date(now.getTime() - (now.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
        reportDateInput.value = localISOTime;

        // Append to container
        document.getElementById('observationDetails').appendChild(detailElement);

        // Populate dropdowns
        populateDropdowns(detailElement);

        observationDetailIndex++;
        updateDetailNumbers();
    }

    // Remove observation detail
    function removeObservationDetail(button) {
        const detailItem = button.closest('.observation-detail-item');
        detailItem.remove();
        updateDetailNumbers();
    }

    // Update detail numbers after add/remove
    function updateDetailNumbers() {
        const details = document.querySelectorAll('.observation-detail-item');
        details.forEach((detail, index) => {
            detail.querySelector('.detail-number').textContent = index + 1;
        });
    }

    // Handle observation type change (show/hide activator field)
    function handleObservationTypeChange(selectElement) {
        const detailItem = selectElement.closest('.observation-detail-item');
        const activatorRow = detailItem.querySelector('.activator-row');
        const activatorSelect = detailItem.querySelector('select[name*="[activator_id]"]');

        if (selectElement.value === 'at_risk_behavior') {
            activatorRow.style.display = 'block';
            activatorSelect.setAttribute('required', 'required');
        } else {
            activatorRow.style.display = 'none';
            activatorSelect.removeAttribute('required');
            activatorSelect.value = '';
        }
    }

    // Populate dropdowns with data
    function populateDropdowns(detailElement) {
        const categorySelect = detailElement.querySelector('select[name*="[category_id]"]');
        const contributingSelect = detailElement.querySelector('select[name*="[contributing_id]"]');
        const actionSelect = detailElement.querySelector('select[name*="[action_id]"]');
        const locationSelect = detailElement.querySelector('select[name*="[location_id]"]');
        const projectSelect = detailElement.querySelector('select[name*="[project_id]"]');
        const activatorSelect = detailElement.querySelector('select[name*="[activator_id]"]');

        // Populate categories
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            categorySelect.appendChild(option);
        });

        // Populate contributings
        contributings.forEach(contributing => {
            const option = document.createElement('option');
            option.value = contributing.id;
            option.textContent = contributing.name;
            contributingSelect.appendChild(option);
        });

        // Populate actions
        actions.forEach(action => {
            const option = document.createElement('option');
            option.value = action.id;
            option.textContent = action.name;
            actionSelect.appendChild(option);
        });

        // Populate locations
        locations.forEach(location => {
            const option = document.createElement('option');
            option.value = location.id;
            option.textContent = location.name;
            locationSelect.appendChild(option);
        });

        // Populate projects
        projects.forEach(project => {
            const option = document.createElement('option');
            option.value = project.id;
            option.textContent = project.name;
            projectSelect.appendChild(option);
        });

        // Populate activators
        activators.forEach(activator => {
            const option = document.createElement('option');
            option.value = activator.id;
            option.textContent = activator.name;
            activatorSelect.appendChild(option);
        });
    }

    // Load master data for all dropdowns
    function loadMasterData() {
        // Load categories
        fetch('{{ url('/') }}/master-data/categories')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    categories = data.data;
                }
            })
            .catch(error => console.error('Error loading categories:', error));

        // Load contributings
        fetch('{{ url('/') }}/master-data/contributings')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    contributings = data.data;
                }
            })
            .catch(error => console.error('Error loading contributings:', error));

        // Load actions
        fetch('{{ url('/') }}/master-data/actions')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    actions = data.data;
                }
            })
            .catch(error => console.error('Error loading actions:', error));

        // Load locations
        fetch('{{ url('/') }}/master-data/locations')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    locations = data.data;
                }
            })
            .catch(error => console.error('Error loading locations:', error));

        // Load projects
        fetch('{{ url('/') }}/master-data/projects')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    projects = data.data;
                }
            })
            .catch(error => console.error('Error loading projects:', error));

        // Load activators
        fetch('{{ url('/') }}/master-data/activators')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    activators = data.data;
                }
            })
            .catch(error => console.error('Error loading activators:', error));
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadMasterData();
    });
</script>

<!-- Floating Add Detail Button -->
<div id="floatingAddDetailBtn" class="floating-add-detail-btn" style="display: none;" onclick="addObservationDetail()">
    <i class="ri-add-line"></i>
    <span class="floating-btn-text">Add Detail</span>
</div>
