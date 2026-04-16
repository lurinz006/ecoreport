<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();
$report = new Report($db);

$search_query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search_results = [];
$total_results = 0;

if(!empty($search_query)) {
    $search_results = $report->search($search_query, $limit, $offset);
    
    // Get total count for pagination
    $count_query = "SELECT COUNT(*) as total FROM reports 
                   WHERE title LIKE :keyword OR description LIKE :keyword OR location_address LIKE :keyword";
    $count_stmt = $db->prepare($count_query);
    $keyword = "%{$search_query}%";
    $count_stmt->bindParam(":keyword", $keyword);
    $count_stmt->execute();
    $total_results = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

$pagination = paginate($total_results, $limit, $page);

$page_title = "Search Reports - " . APP_NAME;
include 'includes/header.php';
?>

<div class="container py-5 fade-in">
    <div class="row g-4">
        <!-- Search Sidebar -->
        <div class="col-lg-4">
            <div class="glass-card p-4 border-0 shadow-sm sticky-top" style="top: 100px; border-radius: 20px;">
                <h4 class="fw-bold mb-4" style="font-family: 'Outfit', sans-serif;">Search Filters</h4>
                <form method="GET" action="">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted">Keyword</label>
                        <div class="input-group input-group-premium">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control form-control-premium bg-light border-0" 
                                   name="q" value="<?php echo htmlspecialchars($search_query); ?>" 
                                   placeholder="Type something...">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-premium w-100 py-3 rounded-3 shadow-sm">
                        Refresh Search
                    </button>
                    <?php if(!empty($search_query)): ?>
                        <a href="search.php" class="btn btn-light w-100 py-2 rounded-3 mt-3 text-secondary small">Clear All</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Search Results -->
        <div class="col-lg-8">
            <div class="glass-card p-4 border-0 shadow-sm" style="border-radius: 24px;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0" style="font-family: 'Outfit', sans-serif;">
                        <?php echo empty($search_query) ? 'Global Database' : 'Search Findings'; ?>
                    </h4>
                    <?php if(!empty($search_query)): ?>
                        <span class="badge bg-emerald bg-opacity-10 text-emerald rounded-pill px-3 py-2 border border-emerald border-opacity-25">
                            <?php echo $total_results; ?> matches found
                        </span>
                    <?php endif; ?>
                </div>

                <?php if(empty($search_query)): ?>
                    <div class="text-center py-5">
                        <div class="avatar-circle mx-auto mb-4 bg-light text-muted" style="width: 100px; height: 100px; font-size: 3rem;">
                            <i class="bi bi-search"></i>
                        </div>
                        <h5 class="fw-bold text-dark">Awaiting Input</h5>
                        <p class="text-muted mx-auto" style="max-width: 300px;">Enter keywords in the sidebar to start exploring our environmental database.</p>
                    </div>
                <?php elseif(empty($search_results)): ?>
                    <div class="text-center py-5">
                        <div class="avatar-circle mx-auto mb-4 bg-light text-muted" style="width: 100px; height: 100px; font-size: 3rem;">
                            <i class="bi bi-emoji-frown"></i>
                        </div>
                        <h5 class="fw-bold text-dark">No Matches</h5>
                        <p class="text-muted mx-auto" style="max-width: 300px;">We couldn't find anything for "<b><?php echo htmlspecialchars($search_query); ?></b>". Try different keywords.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive rounded-4">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-4 py-3">Report Details</th>
                                    <th class="border-0 py-3">Reference</th>
                                    <th class="border-0 py-3">Status</th>
                                    <th class="border-0 px-4 py-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($search_results as $r): ?>
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="fw-bold fs-6 text-dark mb-1"><?php echo htmlspecialchars($r['title']); ?></div>
                                        <div class="small text-muted"><i class="bi bi-tag-fill me-1 small"></i><?php echo getIncidentTypeLabel($r['incident_type']); ?></div>
                                    </td>
                                    <td class="py-4">
                                        <div class="small text-secondary mb-1">
                                            <?php if(isOfficial()): ?>
                                                <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($r['reporter_name']); ?>
                                            <?php else: ?>
                                                <i class="bi bi-calendar3 me-1"></i><?php echo formatDate($r['created_at']); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="smaller text-muted"><i class="bi bi-geo-alt me-1"></i><?php echo truncateText($r['location_address'], 35); ?></div>
                                    </td>
                                    <td class="py-4"><?php echo getStatusBadge($r['status']); ?></td>
                                    <td class="px-4 py-4 text-end">
                                        <button class="btn btn-premium btn-sm rounded-pill shadow-sm px-4" onclick="viewReport(<?php echo $r['id']; ?>)">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if($pagination['total_pages'] > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination pagination-premium justify-content-center">
                            <?php for($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link shadow-none" href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 glass-card p-2" style="border-radius: 24px;">
            <div class="modal-header border-0 fs-4 fw-bold p-4 pb-0">
                <span>Detailed Case Profile</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="reportModalBody"></div>
        </div>
    </div>
</div>

<script>
function viewReport(id) {
    fetch('get_report.php?id=' + id)
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            const body = document.getElementById('reportModalBody');
            body.innerHTML = `
                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="mb-4">
                            <h4 class="fw-bold text-dark mb-2">${data.report.title}</h4>
                            <div class="hstack gap-2 mb-3">
                                ${data.report.status_badge}
                                ${data.report.priority_badge}
                            </div>
                            <p class="text-muted mb-0 small uppercase fw-bold">Case Description</p>
                            <p class="text-dark bg-light p-3 rounded-4 mt-2" style="border-left: 4px solid var(--primary-color)">${data.report.description}</p>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <p class="text-muted small uppercase fw-bold mb-1">Incident Type</p>
                                <p class="mb-0 fw-bold">${data.report.incident_type_label}</p>
                            </div>
                            <div class="col-6">
                                <p class="text-muted small uppercase fw-bold mb-1">Time Logged</p>
                                <p class="mb-0 small text-secondary">${data.report.created_at}</p>
                            </div>
                        </div>

                        <?php if(isOfficial()): ?>
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <p class="text-muted small uppercase fw-bold mb-1">Reporter</p>
                                <p class="mb-0 fw-bold"><i class="bi bi-person-circle me-2"></i>${data.report.reporter_name}</p>
                            </div>
                            <div class="col-6">
                                <p class="text-muted small uppercase fw-bold mb-1">Contact No.</p>
                                <p class="mb-0"><i class="bi bi-telephone-fill me-2 small"></i>${data.report.reporter_contact}</p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mb-0">
                            <p class="text-muted small uppercase fw-bold mb-1">Incident Spot</p>
                            <p class="mb-0 bg-light p-3 rounded-4"><i class="bi bi-geo-alt-fill text-danger me-2"></i>${data.report.location_address}</p>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <p class="text-muted small uppercase fw-bold mb-2">Evidence & Multimedia</p>
                        ${data.report.image_path ? `
                            <img src="uploads/${data.report.image_path}" class="w-100 rounded-4 shadow-sm border mb-3">
                        ` : '<div class="bg-light rounded-4 d-flex align-items-center justify-content-center border-dashed border-2 py-5"><p class="text-muted mb-0">No multimedia attached</p></div>'}
                        
                        ${data.report.official_remarks ? `
                        <div class="mt-4 bg-primary bg-opacity-10 p-4 rounded-4">
                            <p class="text-primary small uppercase fw-bold mb-1">Latest Official Action</p>
                            <p class="mb-0 fs-6 italic">"${data.report.official_remarks}"</p>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('reportModal')).show();
        }
    });
}
</script>

<style>
.smaller { font-size: 0.75rem; }
.italic { font-style: italic; }
.border-dashed { border-style: dashed !important; }
</style>

<?php include 'includes/footer.php'; ?>
