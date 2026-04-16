<?php
/**
 * Overview Tab - Official Dashboard
 */
?>
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="stat-card-premium glass-card border-0 shadow-sm p-4">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-3">
                <i class="bi bi-clipboard-data"></i>
            </div>
            <h2 class="fw-bold mb-0 text-gradient"><?php echo $stats['total']; ?></h2>
            <p class="text-muted small fw-bold text-uppercase mb-0">Total Submissions</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-premium glass-card border-0 shadow-sm p-4" style="border-left: 5px solid var(--warning-color) !important;">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning mb-3">
                <i class="bi bi-clock-fill"></i>
            </div>
            <h2 class="fw-bold mb-0 text-warning"><?php echo $stats['pending']; ?></h2>
            <p class="text-muted small fw-bold text-uppercase mb-0">Pending Review</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-premium glass-card border-0 shadow-sm p-4" style="border-left: 5px solid var(--info-color) !important;">
            <div class="stat-icon bg-info bg-opacity-10 text-info mb-3">
                <i class="bi bi-search"></i>
            </div>
            <h2 class="fw-bold mb-0 text-info"><?php echo $stats['investigating']; ?></h2>
            <p class="text-muted small fw-bold text-uppercase mb-0">Investigation</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-premium glass-card border-0 shadow-sm p-4" style="border-left: 5px solid var(--success-color) !important;">
            <div class="stat-icon bg-success bg-opacity-10 text-success mb-3">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h2 class="fw-bold mb-0 text-success"><?php echo $stats['resolved']; ?></h2>
            <p class="text-muted small fw-bold text-uppercase mb-0">Resolved Cases</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="glass-card p-4 border-0 shadow-sm h-100">
            <h5 class="fw-bold mb-4" style="font-family: 'Outfit', sans-serif;">Status Distribution</h5>
            <div style="height: 300px;">
                <canvas id="overviewStatusChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-7">
        <div class="glass-card p-4 border-0 shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0" style="font-family: 'Outfit', sans-serif;">Most Recent Alerts</h5>
                <button class="btn btn-premium btn-sm rounded-pill px-3" onclick="document.querySelector('[data-bs-target=\'#tab-manage\']').click()">
                    View All Reports
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <tbody id="recentTable">
                        <?php 
                        $latest_query = "SELECT r.*, u.full_name as reporter_name FROM reports r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 5";
                        $latest_stmt = $db->prepare($latest_query);
                        $latest_stmt->execute();
                        $latest = $latest_stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach($latest as $l): ?>
                        <tr>
                            <td style="width: 50px;">
                                <div class="avatar-circle-sm bg-primary bg-opacity-5 text-primary" style="width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark small mb-1"><?php echo htmlspecialchars($l['title']); ?></div>
                                <div class="text-muted smaller"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($l['reporter_name']); ?></div>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-light text-muted border rounded-pill px-2 py-1" style="font-size: 0.7rem;"><?php echo formatDate($l['created_at']); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
