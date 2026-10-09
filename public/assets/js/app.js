/**
 * Asoftmedia Internship Management System (AIMS) - Main JS
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Auto-dismiss alerts after 6 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 6000);
    });

    // 2. Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 3. Mobile Responsive Sidebar Drawer & Backdrop
    const toggleButtons = document.querySelectorAll('#btnToggleSidebar, .btn-sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    let backdrop = document.querySelector('.sidebar-backdrop');

    if (sidebar && !backdrop) {
        backdrop = document.createElement('div');
        backdrop.className = 'sidebar-backdrop';
        document.body.appendChild(backdrop);
    }

    const toggleSidebar = () => {
        if (sidebar) sidebar.classList.toggle('show');
        if (backdrop) backdrop.classList.toggle('show');
    };

    toggleButtons.forEach(btn => btn.addEventListener('click', toggleSidebar));
    if (backdrop) {
        backdrop.addEventListener('click', () => {
            if (sidebar) sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        });
    }

    // Auto-close sidebar on mobile link click
    if (sidebar) {
        sidebar.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('show');
                    if (backdrop) backdrop.classList.remove('show');
                }
            });
        });
    }

    // 4. Register PWA Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => {
                    // ServiceWorker successfully registered
                })
                .catch(err => {
                    console.warn('PWA Registration error:', err);
                });
        });
    }
});
