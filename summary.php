<?php
/**
 * Summary Tab - Resident Dashboard
 * Restored to original table layout with Dark Emerald styling
 */
?>
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="stat-card-premium border-0 shadow-sm glass-card p-4">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-clipboard-data"></i>
            </div>
            <h3 class="fw-bold mb-0 text-white"><?php echo $user_stats['total']; ?></h3>
            <p class="text-white-50 small fw-medium text-uppercase mb-0">Total Submissions</p>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card-premium glass-card border-0 shadow-sm p-4" style="border-left: 4px solid #d97706">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-clock-fill"></i>
            </div>
            <h3 class="fw-bold mb-0 text-warning"><?php echo (int)$user_stats['pending']; ?></h3>
            <p class="text-white-50 small fw-medium text-uppercase mb-0">Pending</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card-premium glass-card border-0 shadow-sm p-4" style="border-left: 4px solid #2563eb">
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-search"></i>
            </div>
            <h3 class="fw-bold mb-0 text-info"><?php echo (int)$user_stats['investigating']; ?></h3>
            <p class="text-white-50 small fw-medium text-uppercase mb-0">In Progress</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card-premium glass-card border-0 shadow-sm p-4" style="border-left: 4px solid #10b981">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h3 class="fw-bold mb-0 text-success"><?php echo (int)$user_stats['resolved']; ?></h3>
            <p class="text-white-50 small fw-medium text-uppercase mb-0">Resolved</p>
        </div>
    </div>
</div>

<div class="glass-card p-4 border-0 mb-5 fade-in shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-white" style="font-family: 'Outfit', sans-serif;">Recent Reports</h4>
        <button class="btn btn-premium btn-sm rounded-3" onclick="document.getElementById('new-report-tab').click()">
            <i class="bi bi-plus-lg me-2"></i>New Report
        </button>
    </div>
    
    <?php if(empty($user_reports)): ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox text-white-50 display-1 mb-3 opacity-25"></i>
            <p class="text-white-50 fs-5">No reports yet. Start by identifying an issue in your community.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive rounded-4 overflow-hidden border border-white border-opacity-10">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="border-0 px-4 py-3">Incident Topic</th>
                        <th class="border-0 py-3 text-center">Type</th>
                        <th class="border-0 py-3 text-center">Status</th>
                        <th class="border-0 py-3 text-center">Submitted</th>
                        <th class="border-0 px-4 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php foreach($user_reports as $r): ?>
                    <tr class="hover-glow">
                        <td class="px-4 py-3">
                            <div class="fw-bold text-white"><?php echo htmlspecialchars($r['title']); ?></div>
                            <div class="small text-white-50"><i class="bi bi-geo-alt-fill text-danger opacity-75 me-1"></i><?php echo truncateText($r['location_address'], 40); ?></div>
                        </td>
                        <td class="text-center py-3">
                            <span class="badge bg-light text-white-50 border fw-medium rounded-pill px-3" style="background: rgba(255,255,255,0.05) !important;"><?php echo getIncidentTypeLabel($r['incident_type']); ?></span>
                        </td>
                        <td class="text-center py-3"><?php echo getStatusBadge($r['status']); ?></td>
                        <td class="text-center py-3 text-white-50 small"><?php echo formatDate($r['created_at']); ?></td>
                        <td class="px-4 py-3 text-end">
                            <button class="btn btn-outline-light btn-sm rounded-circle shadow-sm" onclick="viewReport(<?php echo $r['id']; ?>)" title="View Details">
                                <i class="bi bi-eye-fill p-1"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
