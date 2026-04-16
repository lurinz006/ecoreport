<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

if(!isLoggedIn()) {
    redirect('../login.php');
}

if(isResident()) {
    redirect('../user/dashboard.php');
}

$database = new Database();
$db = $database->getConnection();
$report = new Report($db);

// Get Statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'under_investigation' THEN 1 ELSE 0 END) as investigating,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM reports";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get All Reports with filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$incident_filter = isset($_GET['incident_type']) ? sanitizeInput($_GET['incident_type']) : '';

$all_reports = $report->getAll($limit, $offset, $status_filter, $incident_filter);

// Determine active tab based on query parameters to prevent Javascript flash/failure
$active_tab = (isset($_GET['status']) || isset($_GET['incident_type']) || isset($_GET['page'])) ? 'manage' : 'overview';

// Pagination
$count_query = "SELECT COUNT(*) as total FROM reports WHERE 1=1";
if($status_filter) $count_query .= " AND status = :status";
if($incident_filter) $count_query .= " AND incident_type = :incident_type";
$count_stmt = $db->prepare($count_query);
if($status_filter) $count_stmt->bindParam(":status", $status_filter);
if($incident_filter) $count_stmt->bindParam(":incident_type", $incident_filter);
$count_stmt->execute();
$total_reports = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$pagination = paginate($total_reports, $limit, $page);

// Get unread notifications
$unread_count = getUnreadNotifications($_SESSION['user_id']);

$page_title = "Official Dashboard - " . APP_NAME;
include '../includes/header.php';
?>

<div class="container-fluid px-2 px-xl-5 fade-in">
    <div class="row g-4">
        <!-- Sidebar Column -->
        <div class="col-lg-4 col-xl-3 sidebar-column">
            <div class="sidebar-premium sticky-top border-0 shadow-lg" style="top: 100px;">
                <div class="text-center mb-5">
                    <div class="profile-avatar-wrapper position-relative mx-auto mb-3" style="width: 100px; height: 100px;">
                        <div class="avatar-circle-premium shadow-lg overflow-hidden" style="width: 100px; height: 100px; border-radius: 24px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; font-size: 2.8rem; color: white; border: 4px solid var(--glass-border); position: relative;">
                            <?php if(isset($_SESSION['profile_image']) && $_SESSION['profile_image']): ?>
                                <img src="../uploads/profiles/<?php echo $_SESSION['profile_image']; ?>" class="w-100 h-100 object-fit-cover">
                            <?php else: ?>
                                <i class="bi bi-shield-check"></i>
                            <?php endif; ?>
                        </div>
                        <label for="profileUpload" class="avatar-edit-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center opacity-0 transition-all cursor-pointer" style="border-radius: 24px; background: rgba(0,0,0,0.4); cursor: pointer;">
                            <i class="bi bi-camera-fill text-white fs-3"></i>
                            <input type="file" id="profileUpload" class="d-none" accept="image/*" onchange="uploadProfileImage(this)">
                        </label>
                    </div>
                    <h5 class="fw-bold fs-4 mb-2 text-white" style="font-family: 'Outfit', sans-serif;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></h5>
                    <span class="badge rounded-pill py-2 px-3 fw-medium mb-3 shadow-none border border-white border-opacity-10" style="background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.7);">Barangay Official</span>
                </div>
                
                <div class="d-grid gap-2" id="officialTabs" role="tablist">
                    <button class="nav-link nav-link-premium <?php echo $active_tab === 'overview' ? 'active' : ''; ?> text-start" 
                            data-bs-toggle="pill" data-bs-target="#tab-overview">
                        <i class="bi bi-grid-fill"></i> System Overview
                    </button>
                    <button class="nav-link nav-link-premium <?php echo $active_tab === 'manage' ? 'active' : ''; ?> text-start" 
                            data-bs-toggle="pill" data-bs-target="#tab-manage">
                        <i class="bi bi-clipboard-check-fill"></i> Action Center
                    </button>
                    <button class="nav-link nav-link-premium text-start" 
                            data-bs-toggle="pill" data-bs-target="#tab-analytics">
                        <i class="bi bi-bar-chart-line-fill"></i> Data Analytics
                    </button>
                    <button class="nav-link nav-link-premium text-start d-flex justify-content-between align-items-center" 
                            data-bs-toggle="pill" data-bs-target="#tab-notifications">
                        <span><i class="bi bi-bell-fill"></i> Notifications</span>
                        <?php if($unread_count > 0): ?>
                            <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 0.75rem;"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </button>
                    
                    <hr class="border-white border-opacity-10 my-3">
                    
                    <a href="../logout.php" class="nav-link nav-link-premium text-start text-danger opacity-75 hover-opacity-100">
                        <i class="bi bi-box-arrow-right"></i> Logout System
                    </a>
                </div>
            </div>
        </div>

        <!-- Content Column -->
        <div class="col-lg-8 col-xl-9 content-column py-2">
            <div class="tab-content">
                
                <!-- Grouped Modular Tabs -->
                <div class="tab-pane fade <?php echo $active_tab === 'overview' ? 'show active' : ''; ?>" id="tab-overview">
                    <?php include 'tabs/overview.php'; ?>
                </div>

                <div class="tab-pane fade <?php echo $active_tab === 'manage' ? 'show active' : ''; ?>" id="tab-manage">
                    <?php include 'tabs/manage_reports.php'; ?>
                </div>

                <div class="tab-pane fade" id="tab-analytics">
                    <?php include 'tabs/analytics.php'; ?>
                </div>

                <div class="tab-pane fade" id="tab-notifications">
                    <?php include 'tabs/notifications.php'; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Detailed Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 glass-card p-3 shadow-lg" style="border-radius: 32px;">
            <div class="modal-header border-0 fs-3 fw-bold p-4">
                <span class="text-gradient">Official Case Review</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-0" id="reportModalBody"></div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 glass-card p-2" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4">
                <h5 class="fw-bold mb-0">Direct Action Response</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <form id="updateStatusForm">
                    <input type="hidden" id="updateReportId">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Resolution Track</label>
                        <select class="form-select form-control-premium bg-light border-0 py-3 rounded-4" id="newStatus" required>
                            <option value="pending">Pending Review</option>
                            <option value="under_investigation">Active Investigation</option>
                            <option value="resolved">Mark as Resolved</option>
                            <option value="rejected">Mark as Rejected/Invalid</option>
                        </select>
                    </div>
                    <div class="mb-5">
                        <label class="form-label fw-bold">Official Remarks (Visible to Resident)</label>
                        <textarea class="form-control form-control-premium bg-light border-0 rounded-4 p-3" id="officialRemarks" rows="5" placeholder="Provide findings or resolution details..." required></textarea>
                        <p class="text-muted small mt-3"><i class="bi bi-info-circle me-1"></i> These remarks will trigger a notification to the reporter.</p>
                    </div>
                    <button type="submit" class="btn btn-premium w-100 py-3 rounded-4 fs-5 shadow">Update & Notify Monitor</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../js/map_handler.js"></script>
<script>
// Analytics Initialization with premium colors
function initCharts() {
    const statusCtx = document.getElementById('overviewStatusChart')?.getContext('2d');
    if(statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['  Pending', '  Investigation', '  Resolved', '  Rejected'],
                datasets: [{
                    data: [<?php echo (int)$stats['pending']; ?>, <?php echo (int)$stats['investigating']; ?>, <?php echo (int)$stats['resolved']; ?>, <?php echo (int)$stats['rejected']; ?>],
                    backgroundColor: ['#d97706', '#2563eb', '#059669', '#dc2626'],
                    borderWidth: 8,
                    borderColor: 'white',
                    hoverOffset: 25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { position: 'bottom', labels: { color: '#f8fafc', usePointStyle: true, padding: 30, boxPadding: 15, font: { family: "'Inter', sans-serif", weight: '600' } } },
                    tooltip: {
                        boxPadding: 8,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                let total = context.chart._metasets[context.datasetIndex].total;
                                let percentage = total > 0 ? ((value * 100) / total).toFixed(1) + '%' : '0%';
                                return '    ' + value + ' cases (' + percentage + ')';
                            }
                        }
                    }
                },
                cutout: '75%'
            }
        });
    }

    // Type Chart
    const typeCtx = document.getElementById('typeChart')?.getContext('2d');
    if(typeCtx) {
        fetch('../get_stats_data.php?type=incident_breakdown')
        .then(res => res.json())
        .then(data => {
            new Chart(typeCtx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Impact Magnitude',
                        data: data.values,
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        borderColor: 'rgb(5, 150, 105)',
                        borderWidth: 3,
                        borderRadius: 15,
                        barThickness: 30
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#f8fafc' } },
                        datalabels: { display: false }
                    },
                    scales: { 
                        y: { 
                            beginAtZero: true, 
                            ticks: { color: '#cbd5e1' },
                            grid: { borderDash: [5, 5], color: 'rgba(255,255,255,0.1)' } 
                        },
                        x: { 
                            ticks: { color: '#cbd5e1' },
                            grid: { display: false } 
                        }
                    }
                }
            });
        });
    }

    // Trend Chart
    const trendCtx = document.getElementById('trendChart')?.getContext('2d');
    if(trendCtx) {
        fetch('../get_stats_data.php?type=trend')
        .then(res => res.json())
        .then(data => {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Network Activity',
                        data: data.values,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        fill: true,
                        tension: 0.5,
                        pointRadius: 6,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#059669',
                        pointBorderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#f8fafc' } },
                        datalabels: { display: false }
                    },
                    scales: { 
                        y: { 
                            beginAtZero: true, 
                            ticks: { color: '#cbd5e1' },
                            grid: { borderDash: [5, 5], color: 'rgba(255,255,255,0.1)' } 
                        },
                        x: { 
                            ticks: { color: '#cbd5e1' },
                            grid: { display: false } 
                        }
                    }
                }
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initCharts();

    // Tab persistence: restore active tab from URL hash
    const hash = window.location.hash;
    if (hash) {
        const tabBtn = document.querySelector('[data-bs-target="' + hash + '"]');
        if (tabBtn) {
            new bootstrap.Tab(tabBtn).show();
        }
    }

    // Save active tab to URL hash on click
    document.querySelectorAll('[data-bs-toggle="pill"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', (e) => {
            const target = e.target.getAttribute('data-bs-target');
            history.replaceState(null, null, target);
        });
    });
});

function viewReport(id) {
    fetch('../get_report.php?id=' + id)
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            const r = data.report;
            const hasCoords = r.latitude && r.longitude && parseFloat(r.latitude) !== 0;
            const body = document.getElementById('reportModalBody');
            body.innerHTML = `
                <div class="row g-5">
                    <div class="col-lg-7">
                        <div class="mb-5">
                            <h3 class="fw-bold text-dark mb-4 fs-2">${r.title}</h3>
                            <div class="hstack gap-3 mb-5">
                                ${r.status_badge}
                                ${r.priority_badge}
                            </div>
                            <p class="text-muted small uppercase fw-bold mb-3 ls-1">Case Description</p>
                            <div class="text-dark bg-light p-4 rounded-4 fs-5 border-start border-4 border-emerald" style="line-height: 1.8;">${r.description}</div>
                        </div>
                        
                        <div class="row g-4 mb-5 p-4 bg-white rounded-4 border">
                            <div class="col-6">
                                <p class="text-muted small uppercase fw-bold mb-1">Citizen Reporter</p>
                                <p class="mb-0 fw-bold fs-5"><i class="bi bi-person-circle me-3 text-primary"></i>${r.reporter_name}</p>
                            </div>
                            <div class="col-6">
                                <p class="text-muted small uppercase fw-bold mb-1">Response Channel</p>
                                <a href="tel:${r.reporter_contact}" class="btn btn-outline-primary btn-sm rounded-pill px-3 border-0 bg-primary bg-opacity-5"><i class="bi bi-telephone-fill me-2"></i>${r.reporter_contact}</a>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="text-muted small uppercase fw-bold mb-3 ls-1">Verified Location</p>
                            <div class="bg-light p-4 rounded-4 shadow-none d-flex align-items-center gap-3 mb-3">
                                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                     <i class="bi bi-geo-alt-fill fs-3"></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5">${r.location_address}</div>
                                    ${hasCoords ? `
                                    <button class="btn btn-link text-decoration-none p-0 mt-1 text-success fw-semibold" onclick="toggleAdminMap(${r.latitude}, ${r.longitude})" id="coordToggleBtn">
                                        <i class="bi bi-map-fill me-1"></i> Coordinate Sync: ${parseFloat(r.latitude).toFixed(8)}, ${parseFloat(r.longitude).toFixed(8)}
                                        <i class="bi bi-chevron-down ms-1" id="coordChevron"></i>
                                    </button>` : '<div class="text-muted small mt-1">No coordinates pinned</div>'}
                                </div>
                            </div>
                            ${hasCoords ? `
                            <div id="adminDetailMapWrapper" class="d-none rounded-4 overflow-hidden shadow border" style="height: 280px;">
                                <div id="adminDetailMap" style="height: 100%;"></div>
                            </div>` : ''}
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <p class="text-muted small uppercase fw-bold mb-4 ls-1">Evidence Portfolio</p>
                        <div class="mb-5">
                            ${r.image_path ? `
                                <div class="rounded-4 overflow-hidden shadow border">
                                    <img src="../uploads/${r.image_path}" class="w-100 object-fit-cover" style="max-height: 400px;">
                                </div>
                            ` : '<div class="bg-light rounded-4 d-flex flex-column align-items-center justify-content-center border py-5"><i class="bi bi-camera-video-off display-4 mb-3 opacity-25"></i><p class="text-muted mb-0">No multimedia attached</p></div>'}
                        </div>
                        
                        <div class="mt-4 bg-light p-4 rounded-4 border">
                            <p class="text-dark small text-uppercase fw-bold mb-4 ls-1 border-bottom pb-2"><i class="bi bi-clock-history me-2 text-primary"></i>Audit Trail & Remarks</p>
                            ${r.updates && r.updates.length > 0 ? `
                                <div class="timeline position-relative ps-3" style="border-left: 2px solid #e2e8f0;">
                                    ${r.updates.map(u => `
                                        <div class="mb-4 position-relative">
                                            <div class="position-absolute bg-primary rounded-circle" style="width: 12px; height: 12px; left: -23px; top: 5px; border: 2px solid white;"></div>
                                            <div class="bg-white p-3 rounded-4 shadow-sm border">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="fw-bold small text-dark">${u.updated_by_name} <span class="badge bg-secondary bg-opacity-10 text-secondary border ms-1">${u.updated_by_role === 'official' ? 'Admin' : 'User'}</span></span>
                                                    <span class="text-muted" style="font-size: 0.7rem;">${u.created_at}</span>
                                                </div>
                                                <p class="mb-0 mt-2 text-dark italic font-monospace small" style="background: var(--bs-gray-100); padding: 8px; border-radius: 8px; border-left: 3px solid var(--primary-color);">"${u.description}"</p>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : `
                                <div class="text-center">
                                    <i class="bi bi-hourglass-split display-6 text-muted mb-3 d-block opacity-25"></i>
                                    <p class="text-muted mb-0 small fw-medium">No official updates or remarks yet.</p>
                                </div>
                            `}
                        </div>
                    </div>
                </div>
                <div class="mt-5 pt-4 border-top d-flex justify-content-between text-muted small">
                    <span>Reference: <strong>#ECO-${1000 + parseInt(id)}</strong></span>
                    <span>Reported officially on ${r.created_at}</span>
                </div>
            `;

            // Store map instance reference
            window._adminDetailMap = null;

            new bootstrap.Modal(document.getElementById('reportModal')).show();
        }
    });
}

function toggleAdminMap(lat, lng) {
    const wrapper = document.getElementById('adminDetailMapWrapper');
    const chevron = document.getElementById('coordChevron');
    if (!wrapper) return;

    const isHidden = wrapper.classList.contains('d-none');
    wrapper.classList.toggle('d-none', !isHidden);
    chevron.classList.toggle('bi-chevron-down', !isHidden);
    chevron.classList.toggle('bi-chevron-up', isHidden);

    if (isHidden) {
        if (!window._adminDetailMap) {
            // Small delay to ensure the div is visible before Leaflet initializes
            setTimeout(() => {
                window._adminDetailMap = L.map('adminDetailMap').setView([lat, lng], 17);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(window._adminDetailMap);

                const incidentIcon = L.divIcon({
                    html: '<div style="background:#ef4444;width:20px;height:20px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.4);"></div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10],
                    className: ''
                });

                L.marker([lat, lng], { icon: incidentIcon })
                    .addTo(window._adminDetailMap)
                    .bindPopup('<b>📍 Incident Location</b><br>Lat: ' + lat + '<br>Lng: ' + lng)
                    .openPopup();
            }, 150);
        } else {
            window._adminDetailMap.invalidateSize();
            window._adminDetailMap.setView([lat, lng], 17);
        }
    }
}


function openUpdateModal(id, currentStatus) {
    document.getElementById('updateReportId').value = id;
    document.getElementById('newStatus').value = currentStatus;
    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
}

document.getElementById('updateStatusForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Syncing...';
    
    const payload = {
        report_id: document.getElementById('updateReportId').value,
        status: document.getElementById('newStatus').value,
        remarks: document.getElementById('officialRemarks').value
    };
    
    fetch('../update_report_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            window.location.href = 'dashboard.php#tab-manage';
            location.reload();
        } else {
            alert('Failed to update: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = 'Update & Notify Monitor';
        }
    });
});

function applyFilters() {
    const s = document.getElementById('filterStatus').value;
    const url = new URL(window.location.href);
    if(s) {
        url.searchParams.set('status', s);
    } else {
        url.searchParams.delete('status');
    }
    url.searchParams.delete('page');
    url.hash = 'tab-manage';
    window.location.href = url.toString();
}

function searchReports(e) {
    if(e.key === 'Enter') {
        const q = e.target.value;
        // Search logic integration here
    }
}

function loadNotifications() {
    fetch('../get_notifications.php')
    .then(res => res.json())
    .then(data => {
        const c = document.getElementById('notificationsContainer');
        if (!data.notifications.length) {
            c.innerHTML = `
                <div class="text-center py-5 opacity-50">
                    <i class="bi bi-check2-all display-1"></i>
                    <p class="mt-4 fs-5">No notifications yet.</p>
                </div>`;
            return;
        }
        c.innerHTML = data.notifications.map(n => `
            <div class="p-4 mb-3 rounded-4 transition-all ${n.is_read ? 'border opacity-60' : 'border-start border-4 shadow-sm'}" style="${n.is_read ? 'border-color: rgba(255,255,255,0.1) !important;' : 'border-color: var(--primary-color) !important;'}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold letter-spacing-1 text-uppercase small ${n.is_read ? 'opacity-50' : 'text-primary'}">${n.type_label}</span>
                    <small class="text-muted"><i class="bi bi-clock me-1"></i>${n.created_at}</small>
                </div>
                <p class="mb-0 fs-5 ${n.is_read ? 'opacity-60' : ''}">${n.message}</p>
            </div>
        `).join('');
    });
}

function uploadProfileImage(input) {
    if (input.files && input.files[0]) {
        const formData = new FormData();
        formData.append('profile_image', input.files[0]);
        
        // Simple toast or alert
        fetch('../update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                setTimeout(() => location.reload(), 500);
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function markAllAsRead() {
    fetch('../mark_notifications_read.php', {method: 'POST'})
    .then(() => {
        loadNotifications();
        // Remove the red badge from the sidebar button
        const badge = document.querySelector('[data-bs-target="#tab-notifications"] .badge');
        if (badge) badge.remove();
    });
}

document.querySelectorAll('[data-bs-target="#tab-notifications"]').forEach(el => {
    el.addEventListener('shown.bs.tab', () => {
        markAllAsRead();
    });
});
</script>

<style>
.profile-avatar-wrapper:hover .avatar-edit-overlay {
    opacity: 1 !important;
}
.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.3s ease; }

.border-emerald { border-color: var(--primary-color) !important; }
.ls-1 { letter-spacing: 0.05em; }
.avatar-circle-premium { transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.avatar-circle-premium:hover { transform: rotate(10deg) scale(1.05); }

@media (max-width: 992px) {
    .dashboard-grid { grid-template-columns: 1fr; }
}
</style>

<?php include '../includes/footer.php'; ?>
