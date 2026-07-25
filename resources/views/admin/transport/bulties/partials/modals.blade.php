<!-- Add City Modal -->
<div class="modal fade" id="addCityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form id="quickAddCityForm">
                @csrf
                <div class="modal-header modal-header-premium py-3">
                    <h5 class="modal-title text-white fw-bold"><i class="bx bx-map me-2"></i>Quick Add City</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">City Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Ahmedabad" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">State <span class="text-danger">*</span></label>
                            <input type="text" name="state" class="form-control" placeholder="e.g. Gujarat" required>
                        </div>
                    </div>
                    <div id="cityAddError" class="alert alert-danger d-none mt-3"></div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary shadow" id="saveCityBtn">Save City</button>
                </div>
            </form>
        </div>
    </div>
</div>


