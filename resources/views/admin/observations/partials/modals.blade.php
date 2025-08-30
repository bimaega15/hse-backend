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
</script>
