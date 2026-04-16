<?php
/**
 * New Report Tab - Resident Dashboard
 */
?>
<div class="glass-card p-4 border-0 mb-5 shadow-sm fade-in">
    <h4 class="fw-bold mb-4" style="font-family: 'Outfit', sans-serif;">Submit New Environmental Report</h4>
    
    <form id="reportForm" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-7">
                <div class="mb-4">
                    <label class="form-label fw-semibold">Report Title *</label>
                    <input type="text" class="form-control form-control-premium bg-light border-0" id="title" name="title" placeholder="e.g. Illegal Dumping at Purok 4" required>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="form-label fw-semibold">Incident Category *</label>
                        <select class="form-select form-control-premium bg-light border-0" id="incident_type" name="incident_type" required>
                            <option value="">Select Category</option>
                            <option value="pollution">General Pollution</option>
                            <option value="illegal_dumping">Illegal Dumping</option>
                            <option value="flood">Flood/Drainage Issue</option>
                            <option value="fire_hazard">Fire Hazard</option>
                            <option value="noise_pollution">Noise Pollution</option>
                            <option value="other">Other Concern</option>
                        </select>
                    </div>
                    <div class="col-md-6 text-center">
                        <label class="form-label fw-semibold">Priority Level</label>
                        <div class="priority-toggle-group shadow-sm">
                            <input type="radio" class="btn-check" name="priority" id="p_low" value="low" autocomplete="off">
                            <label class="priority-btn low" for="p_low">Low</label>
                            
                            <input type="radio" class="btn-check" name="priority" id="p_med" value="medium" autocomplete="off" checked>
                            <label class="priority-btn medium" for="p_med">Medium</label>
                            
                            <input type="radio" class="btn-check" name="priority" id="p_high" value="high" autocomplete="off">
                            <label class="priority-btn high" for="p_high">High</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Detailed Description *</label>
                    <textarea class="form-control form-control-premium bg-light border-0" id="description" name="description" rows="5" placeholder="Explain what happened, the severity, and any other relevant details..." required></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold">Location Address *</label>
                    <input type="text" class="form-control form-control-premium bg-light border-0" id="location_address" name="location_address" placeholder="e.g. Near the main bridge of Purok 4" required>
                </div>
            </div>
            
            <div class="col-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-semibold mb-0">Pin the Location</label>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm border" onclick="getLocationForMap()">
                        <i class="bi bi-geo-alt-fill me-1"></i> Use My Location
                    </button>
                </div>
                <div id="map" class="mb-3 rounded-4 shadow-sm border overflow-hidden" style="height: 350px; z-index: 1;"></div>
                <div class="row g-2 mb-4">
                    <div class="col">
                        <input type="text" class="form-control form-control-sm text-center bg-light border-0 rounded-3 py-2" name="latitude" id="latitude" placeholder="Latitude" readonly>
                    </div>
                    <div class="col">
                        <input type="text" class="form-control form-control-sm text-center bg-light border-0 rounded-3 py-2" name="longitude" id="longitude" placeholder="Longitude" readonly>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Evidence Photo (Optional)</label>
                    <div class="p-4 border-2 border-dashed border-light bg-light rounded-4 text-center cursor-pointer hover-glow" onclick="document.getElementById('image').click();" style="transition: all 0.3s ease;">
                        <i class="bi bi-camera-fill text-muted display-4 mb-2"></i>
                        <p class="text-muted small mb-0">Click to upload photo evidence<br>(Max 5MB, JPG/PNG)</p>
                        <input type="file" id="image" name="image" class="d-none" accept="image/*" onchange="previewImage(this);">
                    </div>
                    <div id="imagePreview" class="mt-2 d-none position-relative d-inline-block">
                        <img src="" class="img-fluid rounded-4 shadow-sm border p-1" style="max-height: 200px;">
                        <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 m-2 shadow hover-scale" onclick="removeImage();" title="Remove image">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4 opacity-10">
        <div class="d-flex justify-content-end gap-3">
            <button type="reset" class="btn btn-light px-4 rounded-3 border-0">Clear Form</button>
            <button type="submit" class="btn btn-premium px-5 py-3 rounded-4">
                <i class="bi bi-send-fill me-2"></i>Submit Official Report
            </button>
        </div>
    </form>
</div>
