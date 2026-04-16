// Barangay EcoReport JavaScript Functions

// Global variables
let currentUser = null;
let notificationsInterval = null;

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Set up event listeners
    setupEventListeners();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Start notification polling if logged in
    if(typeof isLoggedIn !== 'undefined' && isLoggedIn()) {
        startNotificationPolling();
    }
    
    // Initialize form validations
    initializeFormValidations();
    
    // Set up keyboard shortcuts
    setupKeyboardShortcuts();
}

function setupEventListeners() {
    // Auto-save functionality for forms
    document.querySelectorAll('form[data-auto-save]').forEach(form => {
        setupAutoSave(form);
    });
    
    // Image upload preview
    document.querySelectorAll('input[type="file"][accept*="image"]').forEach(input => {
        setupImagePreview(input);
    });
    
    // Dynamic search
    document.querySelectorAll('[data-search]').forEach(input => {
        setupDynamicSearch(input);
    });
    
    // Lazy loading for images
    setupLazyLoading();
}

function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function initializeFormValidations() {
    // Custom validation rules
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Focus on first invalid field
                const firstInvalid = form.querySelector(':invalid');
                if(firstInvalid) {
                    firstInvalid.focus();
                    showFieldError(firstInvalid, 'Please fill in this field correctly');
                }
            }
            form.classList.add('was-validated');
        }, false);
    });
}

function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K for search
        if((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[type="search"], #searchReports, input[name="q"]');
            if(searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
        
        // Ctrl/Cmd + N for new report (residents only)
        if((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            const newReportBtn = document.querySelector('[href="#new-report"], button[data-action="new-report"]');
            if(newReportBtn) {
                newReportBtn.click();
            }
        }
        
        // Escape to close modals and dropdowns
        if(e.key === 'Escape') {
            // Close modals
            const openModal = document.querySelector('.modal.show');
            if(openModal) {
                bootstrap.Modal.getInstance(openModal).hide();
            }
            
            // Close dropdowns
            const openDropdown = document.querySelector('.dropdown-menu.show');
            if(openDropdown) {
                openDropdown.classList.remove('show');
            }
        }
    });
}

function setupAutoSave(form) {
    let saveTimeout;
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                saveFormData(form);
            }, 2000); // Auto-save after 2 seconds of inactivity
        });
    });
    
    // Load saved data on page load
    loadFormData(form);
}

function saveFormData(form) {
    const formData = new FormData(form);
    const formId = form.id || form.getAttribute('data-form-id');
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    localStorage.setItem(`form_${formId}`, JSON.stringify(data));
    
    // Show save indicator
    showSaveIndicator(form);
}

function loadFormData(form) {
    const formId = form.id || form.getAttribute('data-form-id');
    const savedData = localStorage.getItem(`form_${formId}`);
    
    if(savedData) {
        const data = JSON.parse(savedData);
        Object.keys(data).forEach(key => {
            const input = form.querySelector(`[name="${key}"]`);
            if(input) {
                input.value = data[key];
            }
        });
    }
}

function showSaveIndicator(form) {
    let indicator = form.querySelector('.auto-save-indicator');
    if(!indicator) {
        indicator = document.createElement('span');
        indicator.className = 'auto-save-indicator text-success small';
        indicator.innerHTML = '<i class="bi bi-check-circle"></i> Saved';
        form.appendChild(indicator);
    }
    
    indicator.style.display = 'inline';
    setTimeout(() => {
        indicator.style.display = 'none';
    }, 2000);
}

function setupImagePreview(input) {
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if(file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                let preview = input.nextElementSibling;
                if(!preview || !preview.classList.contains('image-preview')) {
                    preview = document.createElement('div');
                    preview.className = 'image-preview mt-2';
                    input.parentNode.insertBefore(preview, input.nextSibling);
                }
                
                preview.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" style="max-height: 200px;">
                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImagePreview(this)">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                `;
            };
            
            reader.readAsDataURL(file);
        }
    });
}

function removeImagePreview(button) {
    const preview = button.closest('.image-preview');
    const input = preview.previousElementSibling;
    
    if(input && input.type === 'file') {
        input.value = '';
    }
    
    preview.remove();
}

function setupDynamicSearch(input) {
    let searchTimeout;
    const targetSelector = input.getAttribute('data-search');
    const target = document.querySelector(targetSelector);
    
    if(!target) return;
    
    input.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if(query.length < 2) {
            target.innerHTML = '<p class="text-muted">Enter at least 2 characters to search</p>';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            performSearch(query, target);
        }, 300);
    });
}

function performSearch(query, target) {
    target.innerHTML = '<div class="text-center py-4"><div class="loading"></div> Searching...</div>';
    
    fetch(`search_ajax.php?q=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            renderSearchResults(data.results, target);
        } else {
            target.innerHTML = '<div class="alert alert-danger">Search failed: ' + data.message + '</div>';
        }
    })
    .catch(error => {
        console.error('Search error:', error);
        target.innerHTML = '<div class="alert alert-danger">Search failed. Please try again.</div>';
    });
}

function renderSearchResults(results, target) {
    if(results.length === 0) {
        target.innerHTML = '<p class="text-muted text-center">No results found</p>';
        return;
    }
    
    const html = results.map(result => `
        <div class="search-result-item p-3 border-bottom hover-bg-light" onclick="viewSearchResult(${result.id})">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1">${escapeHtml(result.title)}</h6>
                    <p class="mb-1 text-muted">${escapeHtml(result.description.substring(0, 100))}...</p>
                    <small class="text-muted">
                        <span class="badge bg-secondary">${result.incident_type_label}</span>
                        ${getStatusBadge(result.status)}
                        <span class="ms-2">${formatDate(result.created_at)}</span>
                    </small>
                </div>
                <div>
                    ${getPriorityBadge(result.priority)}
                </div>
            </div>
        </div>
    `).join('');
    
    target.innerHTML = html;
}

function viewSearchResult(id) {
    viewReport(id);
}

function setupLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

function startNotificationPolling() {
    // Check for new notifications every 30 seconds
    notificationsInterval = setInterval(() => {
        checkNewNotifications();
    }, 30000);
}

function checkNewNotifications() {
    fetch('check_notifications.php')
    .then(response => response.json())
    .then(data => {
        if(data.success && data.new_count > 0) {
            showNotificationBadge(data.new_count);
            showNotificationToast(`You have ${data.new_count} new notification(s)`);
        }
    })
    .catch(error => {
        console.error('Notification check error:', error);
    });
}

function showNotificationBadge(count) {
    let badge = document.querySelector('.notification-badge');
    if(!badge) {
        const navLink = document.querySelector('[href="#notifications"]');
        if(navLink) {
            badge = document.createElement('span');
            badge.className = 'notification-badge';
            navLink.style.position = 'relative';
            navLink.appendChild(badge);
        }
    }
    
    if(badge) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'flex';
    }
}

function showNotificationToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification position-fixed top-0 end-0 m-3';
    toast.style.cssText = 'z-index: 9999;';
    toast.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-header">
                <i class="bi bi-bell-fill text-primary me-2"></i>
                <strong class="me-auto">New Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Utility functions
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showFieldError(field, message) {
    // Remove existing error
    const existingError = field.parentNode.querySelector('.field-error');
    if(existingError) {
        existingError.remove();
    }
    
    // Add new error
    const error = document.createElement('div');
    error.className = 'field-error text-danger small mt-1';
    error.textContent = message;
    field.parentNode.appendChild(error);
    
    // Remove error after 5 seconds
    setTimeout(() => {
        error.remove();
    }, 5000);
}

function clearFieldErrors(form) {
    form.querySelectorAll('.field-error').forEach(error => error.remove());
}

// Report management functions
function viewReport(reportId) {
    if(typeof get_report !== 'undefined') {
        get_report(reportId);
    }
}

function updateReportStatus(reportId) {
    if(typeof updateReportStatus !== 'undefined') {
        updateReportStatus(reportId);
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if(notificationsInterval) {
        clearInterval(notificationsInterval);
    }
});

// Export functions for global use
window.BarangayEcoReport = {
    escapeHtml,
    formatDate,
    showFieldError,
    clearFieldErrors,
    viewReport,
    updateReportStatus,
    performSearch,
    showNotificationBadge,
    showNotificationToast
};
