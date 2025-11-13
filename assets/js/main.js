// Enhanced JavaScript for My Store
document.addEventListener('DOMContentLoaded', function() {
    
    // Dark mode toggle functionality
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const moonIcon = document.getElementById('moon-icon');
    const sunIcon = document.getElementById('sun-icon');
    const body = document.body;
    
    // Check for saved dark mode preference
    const savedDarkMode = localStorage.getItem('darkMode');
    if (savedDarkMode === 'true') {
        body.classList.add('dark-mode');
        moonIcon.style.display = 'none';
        sunIcon.style.display = 'block';
    }
    
    // Dark mode toggle event
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'true');
                moonIcon.style.display = 'none';
                sunIcon.style.display = 'block';
            } else {
                localStorage.setItem('darkMode', 'false');
                moonIcon.style.display = 'block';
                sunIcon.style.display = 'none';
            }
            
            // Add click animation
            this.style.transform = 'scale(0.9)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    }
    
    // OTP Input Enhancement
    const otpInput = document.getElementById('otp');
    if (otpInput) {
        // Auto-focus OTP input
        otpInput.focus();
        
        // Auto-advance and format OTP input
        otpInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length > 6) {
                value = value.slice(0, 6); // Limit to 6 digits
            }
            e.target.value = value;
        });
        
        // Handle paste event
        otpInput.addEventListener('paste', function(e) {
            e.preventDefault();
            let pastedText = (e.clipboardData || window.clipboardData).getData('text');
            pastedText = pastedText.replace(/\D/g, '').slice(0, 6);
            this.value = pastedText;
        });
    }
    
    // Auto-focus username/email input on login page
    const usernameInput = document.getElementById('usernameOrEmail');
    if (usernameInput) {
        usernameInput.focus();
    }
    
    // Enhanced form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredInputs = form.querySelectorAll('input[required]');
            let isValid = true;
            
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#c33';
                    input.style.boxShadow = '0 0 0 3px rgba(195, 51, 51, 0.1)';
                    
                    // Add shake animation for invalid inputs
                    input.style.animation = 'shake 0.5s ease-in-out';
                    setTimeout(() => {
                        input.style.animation = '';
                    }, 500);
                } else {
                    input.style.borderColor = '';
                    input.style.boxShadow = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                // Show error message
                const existingError = form.querySelector('.error-message');
                if (!existingError) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    errorDiv.textContent = 'Please fill in all required fields.';
                    form.insertBefore(errorDiv, form.firstChild);
                }
            }
        });
    });
    
    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe elements for scroll animations
    const animatedElements = document.querySelectorAll('.product-card, .category-section, .login-container');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(el);
    });
    
    // Enhanced header scroll effect
    let lastScrollTop = 0;
    const header = document.querySelector('header');
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scrolling down
            header.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up
            header.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    });
    
    // Add loading animation for page transitions
    window.addEventListener('beforeunload', function() {
        document.body.style.opacity = '0.8';
        document.body.style.transition = 'opacity 0.2s ease-out';
    });
    
    // Enhanced button click effects
    const buttons = document.querySelectorAll('.btn, button');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Create ripple effect
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Add CSS for ripple effect
    const style = document.createElement('style');
    style.textContent = `
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(style);
    
    // Performance optimization: Throttle scroll events
    let ticking = false;
    function updateHeader() {
        ticking = false;
    }
    
    function requestTick() {
        if (!ticking) {
            requestAnimationFrame(updateHeader);
            ticking = true;
        }
    }
    
    window.addEventListener('scroll', requestTick);
    
    // Add keyboard navigation support
    document.addEventListener('keydown', function(e) {
        // Escape key to close modals or reset focus
        if (e.key === 'Escape') {
            const activeElement = document.activeElement;
            if (activeElement && activeElement.classList.contains('modal')) {
                activeElement.style.display = 'none';
            }
        }
        
        // Tab key navigation enhancement
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });
    
    // Remove keyboard navigation class on mouse use
    document.addEventListener('mousedown', function() {
        document.body.classList.remove('keyboard-navigation');
    });
    
    // Add loading states for forms - but allow form submission
    const submitButtons = document.querySelectorAll('form button[type="submit"]');
    submitButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.form && this.form.checkValidity()) {
                // Show processing state but don't disable
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                // Allow form to submit normally
                setTimeout(() => {
                    this.form.submit();
                }, 100);
            }
        });
    });
    
    console.log('Enhanced My Store JavaScript loaded successfully! 🚀');
});

// ========================================
// ADMIN PANEL & DASHBOARD ENHANCEMENTS
// ========================================

// Admin Dashboard Animations
function initAdminDashboard() {
    const adminCards = document.querySelectorAll('.admin-stat-card');
    const adminTables = document.querySelectorAll('.admin-table-container');
    const adminForms = document.querySelectorAll('.admin-form');
    
    // Animate admin cards on scroll
    const adminObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                entry.target.style.animationDelay = `${index * 0.1}s`;
                entry.target.style.animation = 'fadeInUp 0.8s ease-out forwards';
            }
        });
    }, { threshold: 0.1 });
    
    adminCards.forEach(card => adminObserver.observe(card));
    adminTables.forEach(table => adminObserver.observe(table));
    adminForms.forEach(form => adminObserver.observe(form));
    
    // Enhanced admin table interactions
    const adminTableRows = document.querySelectorAll('.admin-table tbody tr');
    adminTableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'all 0.3s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Admin search functionality
    const adminSearchInput = document.querySelector('.admin-search input');
    if (adminSearchInput) {
        adminSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.admin-table tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    row.style.animation = 'fadeInUp 0.3s ease-out';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Admin action button effects
    const adminActionBtns = document.querySelectorAll('.admin-action-btn');
    adminActionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Create ripple effect
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Admin form enhancements
    adminForms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
                this.parentElement.style.transition = 'all 0.3s ease';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
        
        // Form submission loading state
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('.admin-form-btn');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
                
                // Re-enable after 3 seconds (adjust as needed)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 3000);
            }
        });
    });
    
    // Admin navigation active state
    const adminNavLinks = document.querySelectorAll('.admin-nav a');
    adminNavLinks.forEach(link => {
        if (link.href === window.location.href) {
            link.classList.add('active');
        }
        
        link.addEventListener('click', function() {
            adminNavLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Admin stats counter animation
    const adminStatNumbers = document.querySelectorAll('.admin-stat-number');
    adminStatNumbers.forEach(stat => {
        const target = parseInt(stat.textContent);
        const increment = target / 50;
        let current = 0;
        
        const updateCounter = () => {
            if (current < target) {
                current += increment;
                stat.textContent = Math.ceil(current);
                requestAnimationFrame(updateCounter);
            } else {
                stat.textContent = target;
            }
        };
        
        // Start counter when card is visible
        const statObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    statObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        statObserver.observe(stat);
    });
    
    // Admin table sorting (if needed)
    const adminTableHeaders = document.querySelectorAll('.admin-table th[data-sortable]');
    adminTableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const table = this.closest('.admin-table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const columnIndex = Array.from(this.parentElement.children).indexOf(this);
            const isAscending = this.classList.contains('sort-asc');
            
            // Remove existing sort classes
            adminTableHeaders.forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            
            // Sort rows
            rows.sort((a, b) => {
                const aValue = a.children[columnIndex].textContent;
                const bValue = b.children[columnIndex].textContent;
                
                if (isAscending) {
                    return aValue.localeCompare(bValue);
                } else {
                    return bValue.localeCompare(aValue);
                }
            });
            
            // Reorder rows
            rows.forEach(row => tbody.appendChild(row));
            
            // Update sort indicator
            this.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
        });
    });
}

// Initialize admin dashboard when DOM is loaded
if (document.querySelector('.admin-container')) {
    initAdminDashboard();
}

// Admin theme toggle enhancement
const adminThemeToggle = document.querySelector('#admin-theme-toggle');
if (adminThemeToggle) {
    adminThemeToggle.addEventListener('click', function() {
        document.body.classList.toggle('admin-dark-mode');
        localStorage.setItem('adminTheme', document.body.classList.contains('admin-dark-mode') ? 'dark' : 'light');
        
        // Add toggle animation
        this.style.transform = 'rotate(180deg)';
        setTimeout(() => {
            this.style.transform = 'rotate(0deg)';
        }, 300);
    });
    
    // Load saved admin theme
    const savedAdminTheme = localStorage.getItem('adminTheme');
    if (savedAdminTheme === 'dark') {
        document.body.classList.add('admin-dark-mode');
    }
}

// Admin export functionality
function exportAdminData(format = 'csv') {
    const table = document.querySelector('.admin-table');
    if (!table) return;
    
    const rows = Array.from(table.querySelectorAll('tr'));
    let data = '';
    
    rows.forEach(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        const rowData = cells.map(cell => cell.textContent.trim()).join(',');
        data += rowData + '\n';
    });
    
    if (format === 'csv') {
        const blob = new Blob([data], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'admin-data.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }
}

// Add export buttons if needed
const adminExportBtn = document.querySelector('#admin-export');
if (adminExportBtn) {
    adminExportBtn.addEventListener('click', () => exportAdminData('csv'));
}

console.log('Admin Panel & Dashboard enhancements loaded successfully! 🚀');
