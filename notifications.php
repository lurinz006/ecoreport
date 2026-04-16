<?php
/**
 * Notifications Tab - Official Dashboard
 */
?>
<div class="glass-card p-5 border-0 mb-5 shadow-sm fade-in">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h4 class="fw-bold mb-0 text-gradient fs-2" style="font-family: 'Outfit', sans-serif;">System Alerts & Inbox</h4>
        <button class="btn btn-light rounded-pill px-4 py-2 border border-light shadow-sm" onclick="markAllAsRead()">
            <i class="bi bi-check-all me-2 text-primary fs-5"></i>Mark all as read
        </button>
    </div>
    
    <div id="notificationsContainer" style="min-height: 300px;">
        <div class="text-center py-5 opacity-25">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>
