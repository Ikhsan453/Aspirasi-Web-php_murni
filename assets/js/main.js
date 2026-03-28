// Main JavaScript for Sistem Aspirasi Web

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips and popovers
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Fade in animation on page load
    document.body.classList.add('fade-in');

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bootstrapAlert = new bootstrap.Alert(alert);
        setTimeout(function() {
            bootstrapAlert.close();
        }, 5000);
    });

    // Smooth scroll for anchor links
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Navbar collapse on link click
    const navbarCollapse = document.querySelector('.navbar-collapse');
    const navLinks = document.querySelectorAll('.navbar-collapse .nav-link');
    
    navLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            const bsCollapse = new bootstrap.Collapse(navbarCollapse, {
                toggle: true
            });
        });
    });

    // Add active class to current nav link
    const currentLocation = String(window.location);
    const menuItems = document.querySelectorAll('.navbar-nav .nav-link');
    
    menuItems.forEach(function(item) {
        if (item.href === currentLocation || currentLocation.includes(item.href)) {
            item.classList.add('active');
        }
    });

    // Form validation - hanya tambah class, jangan prevent submit
    const forms = document.querySelectorAll('form.needs-validation');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Loading spinner on form submit - hanya untuk form yang bukan login
    const submitButtons = document.querySelectorAll('button[type="submit"]:not(#submitBtn)');
    submitButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            if (this.form && this.form.checkValidity()) {
                const btn = this;
                setTimeout(function() {
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
                    btn.disabled = true;
                }, 200);
            }
        });
    });

    // Handle image preview
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById(input.id + '_preview');
                    if (preview) {
                        preview.src = event.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Confirmation dialog for delete actions
    const deleteLinks = document.querySelectorAll('a[data-confirm], button[data-confirm]');
    deleteLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Add animation to elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const animateElements = document.querySelectorAll('.feature-card, .card, .stats-section');
    animateElements.forEach(function(el) {
        observer.observe(el);
    });

    // Responsive table handling
    const tables = document.querySelectorAll('.table-responsive');
    tables.forEach(function(table) {
        // Add horizontal scroll indicator
        table.addEventListener('scroll', function() {
            if (this.scrollLeft > 0) {
                this.classList.add('scrolled-right');
            } else {
                this.classList.remove('scrolled-right');
            }
        });
    });
});

// Utility function to format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

// Utility function to show toast message
function showToast(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alertDiv);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}

// Utility function for AJAX requests
function fetchData(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    return fetch(url, { ...defaultOptions, ...options })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showToast('Terjadi kesalahan: ' + error.message, 'danger');
            throw error;
        });
}
