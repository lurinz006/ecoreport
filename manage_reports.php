<?php
/**
 * Manage Reports Tab - Official Dashboard
 */
?>
<div class="glass-card p-4 border-0 mb-5 shadow-sm fade-in">
    <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-4">
        <h4 class="fw-bold mb-0" style="font-family: 'Outfit', sans-serif;">Report Management Center</h4>
        <div class="d-flex gap-3">
            <select class="form-select border shadow-sm rounded-pill px-4" id="filterStatus" onchange="applyFilters()" style="height: 44px; width: 180px; min-width: 150px; cursor: pointer;">
                <option value="" <?php echo $status_filter === '' ? 'selected' : ''; ?>>All Statuses</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="under_investigation" <?php echo $status_filter === 'under_investigation' ? 'selected' : ''; ?>>Investigation</option>
                <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
            <div class="input-group input-group-sm rounded-pill overflow-hidden border shadow-sm px-2 bg-white" style="width: 300px; height: 44px;">
                <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control border-0 bg-transparent shadow-none" id="searchReports" placeholder="Search by title or reporter..." onkeyup="searchReports(event)">
            </div>
        </div>
    </div>

    <div class="table-responsive rounded-4 shadow-sm border border-light overflow-hidden">
        <table class="table table-hover align-middle mb-0 bg-white">
            <thead class="bg-light">
                <tr>
                    <th class="border-0 px-4 py-4 text-uppercase small text-muted fw-bold ls-1">Report Details</th>
                    <th class="border-0 py-4 text-uppercase small text-muted fw-bold ls-1">Reporter</th>
                    <th class="border-0 py-4 text-uppercase small text-muted fw-bold ls-1">Category</th>
                    <th class="border-0 py-4 text-uppercase small text-muted fw-bold ls-1">Status</th>
                    <th class="border-0 py-4 text-center text-uppercase small text-muted fw-bold ls-1">Priority</th>
                    <th class="border-0 px-4 py-4 text-end text-uppercase small text-muted fw-bold ls-1">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($all_reports as $r): ?>
                <tr class="hover-glow">
                    <td class="px-4 py-4">
                        <div class="fw-bold mb-1 fs-6"><?php echo htmlspecialchars($r['title']); ?></div>
                        <div class="small text-muted d-flex align-items-center gap-1"><i class="bi bi-geo-alt-fill text-danger opacity-75"></i><?php echo truncateText($r['location_address'], 35); ?></div>
                    </td>
                    <td class="py-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                <i class="bi bi-person"></i>
                            </div>
                            <span class="fw-medium text-dark small"><?php echo htmlspecialchars($r['reporter_name']); ?></span>
                        </div>
                    </td>
                    <td class="py-4">
                        <span class="badge bg-light text-dark rounded-pill px-3 py-2 border fw-medium"><?php echo getIncidentTypeLabel($r['incident_type']); ?></span>
                    </td>
                    <td class="py-4"><?php echo getStatusBadge($r['status']); ?></td>
                    <td class="py-4 text-center"><?php echo getPriorityBadge($r['priority']); ?></td>
                    <td class="px-4 py-4 text-end">
                        <div class="hstack gap-2 justify-content-end">
                            <button class="btn btn-light btn-sm rounded-pill px-3 border shadow-sm" onclick="viewReport(<?php echo $r['id']; ?>)">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                            <button class="btn btn-premium btn-sm rounded-pill px-3 shadow-sm" onclick="openUpdateModal(<?php echo $r['id']; ?>, '<?php echo $r['status']; ?>')" style="padding: 0.25rem 0.6rem;">
                                <i class="bi bi-pencil-square me-1"></i> Update
                            </button>
                        </div>
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
            <li class="page-item">
                <a class="page-link rounded-circle border-0 shadow-sm" href="?page=<?php echo $page-1; ?><?php echo $status_filter ? '&status='.$status_filter : ''; ?><?php echo $incident_filter ? '&incident_type='.$incident_filter : ''; ?>#tab-manage"><i class="bi bi-chevron-left"></i></a>
            </li>
            <?php endif; ?>
            
            <?php for($i = 1; $i <= $pagination['total_pages']; $i++): ?>
            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                <a class="page-link rounded-circle border-0 shadow-sm" href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status='.$status_filter : ''; ?><?php echo $incident_filter ? '&incident_type='.$incident_filter : ''; ?>#tab-manage"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>

            <?php if($page < $pagination['total_pages']): ?>
            <li class="page-item">
                <a class="page-link rounded-circle border-0 shadow-sm" href="?page=<?php echo $page+1; ?><?php echo $status_filter ? '&status='.$status_filter : ''; ?><?php echo $incident_filter ? '&incident_type='.$incident_filter : ''; ?>#tab-manage"><i class="bi bi-chevron-right"></i></a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
