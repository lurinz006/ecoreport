<?php
/**
 * My Reports Tab - Resident Dashboard
 */
?>
<div class="glass-card p-4 border-0 mb-5 shadow-sm fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h4 class="fw-bold mb-0" style="font-family: 'Outfit', sans-serif;">Full History of Submissions</h4>
        <div class="d-flex gap-3">
            <div class="input-group input-group-sm rounded-pill overflow-hidden bg-light border-0 px-3 py-1" style="width: 250px;">
                <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control border-0 bg-transparent shadow-none" id="searchReports" placeholder="Search title...">
            </div>
            <select class="form-select border shadow-sm rounded-pill px-4" id="filterStatus" style="width: 160px; height: 44px; min-width: 140px; cursor: pointer;">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="under_investigation">Investigating</option>
                <option value="resolved">Resolved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>
    
    <div id="reportsContainer">
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
                    <?php 
                    // Reuse the existing query logic from summary or add pagination specific to this tab
                    foreach($user_reports as $r): 
                    ?>
                    <tr class="hover-glow">
                        <td class="px-4 py-4">
                            <div class="fw-bold mb-1 fs-6"><?php echo htmlspecialchars($r['title']); ?></div>
                            <div class="small text-muted d-flex align-items-center gap-1">
                                <i class="bi bi-geo-alt-fill text-danger opacity-75"></i>
                                <?php echo htmlspecialchars($r['location_address']); ?>
                            </div>
                        </td>
                        <td class="text-center py-4">
                            <span class="badge bg-light text-dark border fw-medium rounded-pill px-3 py-2"><?php echo getIncidentTypeLabel($r['incident_type']); ?></span>
                        </td>
                        <td class="text-center py-4"><?php echo getStatusBadge($r['status']); ?></td>
                        <td class="text-center py-4 text-muted small"><?php echo formatDate($r['created_at']); ?></td>
                        <td class="px-4 py-4 text-end">
                            <button class="btn btn-light btn-sm rounded-pill shadow-sm px-3 border" onclick="viewReport(<?php echo $r['id']; ?>)">
                                <i class="bi bi-eye-fill me-1"></i> Details
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($pagination['total_pages'] > 1): ?>
        <nav class="mt-5">
            <ul class="pagination pagination-premium justify-content-center gap-2">
                <?php if($page > 1): ?>
                <li class="page-item"><a class="page-link rounded-circle border-0 shadow-sm" href="?page=<?php echo $page-1; ?>#tab-my-reports"><i class="bi bi-chevron-left"></i></a></li>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link rounded-circle border-0 shadow-sm" href="?page=<?php echo $i; ?>#tab-my-reports"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>

                <?php if($page < $pagination['total_pages']): ?>
                <li class="page-item"><a class="page-link rounded-circle border-0 shadow-sm" href="?page=<?php echo $page+1; ?>#tab-my-reports"><i class="bi bi-chevron-right"></i></a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>
