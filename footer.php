<!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="js/script.js"></script>
    
    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Form validation
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
        
        // Improved Loading state for forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.classList.add('disabled');
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
                    
                    // Fallback to restore button if page doesn't redirect
                    setTimeout(() => {
                        if(submitBtn.innerHTML.includes('Processing')) {
                            submitBtn.innerHTML = originalText;
                            submitBtn.classList.remove('disabled');
                        }
                    }, 8000);
                }
            });
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Auto-resize textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });
        
        // File upload preview
        document.querySelectorAll('input[type="file"][accept*="image"]').forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if(file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Create or update preview
                        let preview = document.getElementById('imagePreview');
                        if(!preview) {
                            preview = document.createElement('img');
                            preview.id = 'imagePreview';
                            preview.className = 'img-fluid rounded mt-3';
                            preview.style.maxHeight = '200px';
                            input.parentNode.appendChild(preview);
                        }
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
        
        // Confirm before logout
        document.querySelectorAll('a[href="logout.php"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if(!confirm('Are you sure you want to logout?')) {
                    e.preventDefault();
                }
            });
        });
        
        // Search functionality
        function performSearch(query) {
            if(query.trim()) {
                window.location.href = 'search.php?q=' + encodeURIComponent(query.trim());
            }
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+K for search
            if(e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('#searchReports, input[name="q"]');
                if(searchInput) {
                    searchInput.focus();
                }
            }
            
            // Escape to close modals
            if(e.key === 'Escape') {
                const openModal = document.querySelector('.modal.show');
                if(openModal) {
                    bootstrap.Modal.getInstance(openModal).hide();
                }
            }
        });
        
        // Dynamic content loading
        function loadContent(url, containerId, callback) {
            const container = document.getElementById(containerId);
            if(container) {
                container.innerHTML = '<div class="text-center py-4"><div class="loading"></div> Loading...</div>';
                
                fetch(url)
                .then(response => response.text())
                .then(html => {
                    container.innerHTML = html;
                    if(callback) callback();
                })
                .catch(error => {
                    container.innerHTML = '<div class="alert alert-danger">Error loading content</div>';
                    console.error('Error:', error);
                });
            }
        }
        
        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(notification);
                bsAlert.close();
            }, 5000);
        }
        
        // Export functions for global use
        window.performSearch = performSearch;
        window.loadContent = loadContent;
        window.showNotification = showNotification;
    </script>
</body>
</html>
