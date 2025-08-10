<!-- Create/Edit Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="reportForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Add New Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="reportId" name="id">

                    <div class="row">
                        <!-- Employee Information -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employeeId" class="form-label">Employee <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="employeeId" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                </select>
                                <div class="invalid-feedback" id="employeeIdError"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hseStaffId" class="form-label">HSE Staff</label>
                                <select class="form-select" id="hseStaffId" name="hse_staff_id">
                                    <option value="">Select HSE Staff</option>
                                </select>
                                <div class="invalid-feedback" id="hseStaffIdError"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Report Classification -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="categoryId" class="form-label">Category <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="categoryId" name="category_id" required>
                                    <option value="">Select Category</option>
                                </select>
                                <div class="invalid-feedback" id="categoryIdError"></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="contributingId" class="form-label">Contributing Factor <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="contributingId" name="contributing_id" required>
                                    <option value="">Select Contributing Factor</option>
                                </select>
                                <div class="invalid-feedback" id="contributingIdError"></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="actionId" class="form-label">Action <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="actionId" name="action_id" required>
                                    <option value="">Select Action</option>
                                </select>
                                <div class="invalid-feedback" id="actionIdError"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="severityRating" class="form-label">Severity Rating <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="severityRating" name="severity_rating" required>
                                    <option value="">Select Severity</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                                <div class="invalid-feedback" id="severityRatingError"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label">Location <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" required
                                    maxlength="255" placeholder="Enter incident location">
                                <div class="invalid-feedback" id="locationError"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="4" required maxlength="2000"
                            placeholder="Describe the incident in detail"></textarea>
                        <div class="form-text">Maximum 2000 characters</div>
                        <div class="invalid-feedback" id="descriptionError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="actionTaken" class="form-label">Action Taken</label>
                        <textarea class="form-control" id="actionTaken" name="action_taken" rows="3" maxlength="1000"
                            placeholder="Describe immediate actions taken (optional)"></textarea>
                        <div class="form-text">Maximum 1000 characters</div>
                        <div class="invalid-feedback" id="actionTakenError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="images" class="form-label">Images</label>
                        <input type="file" class="form-control" id="images" name="images[]" multiple
                            accept="image/*">
                        <div class="form-text">You can upload multiple images (JPEG, PNG, JPG, GIF). Maximum 2MB per
                            file.</div>
                        <div class="invalid-feedback" id="imagesError"></div>
                        <div id="imagePreview" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"
                            role="status"></span>
                        <span id="submitText">Save Report</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Report Modal -->
<div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewReportModalLabel">Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Report details will be loaded here -->
                <div id="reportDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" onclick="editReportFromView()">
                    <i class="ri-edit-line me-1"></i>Edit Report
                </button>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="ri-check-line me-1"></i>Update Status
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="updateReportStatus('in-progress')">Mark
                                In Progress</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateReportStatus('done')">Mark
                                Completed</a></li>
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
                    <h5 class="modal-title" id="statusModalLabel">Update Report Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="statusReportId" name="report_id">
                    <input type="hidden" id="newStatus" name="status">

                    <div class="mb-3">
                        <label class="form-label">New Status:</label>
                        <p class="fw-bold" id="statusDisplay"></p>
                    </div>

                    <div class="mb-3" id="hseStaffSelection" style="display: none;">
                        <label for="statusHseStaffId" class="form-label">Assign HSE Staff</label>
                        <select class="form-select" id="statusHseStaffId" name="hse_staff_id">
                            <option value="">Select HSE Staff</option>
                        </select>
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
