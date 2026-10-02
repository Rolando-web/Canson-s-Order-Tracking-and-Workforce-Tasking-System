import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

// Configure standard Toast mixin
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    background: '#ffffff',
    color: '#1f2937',
    iconColor: undefined, // uses sweetalert default colored icons
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

// Global helpers
window.Swal = Swal;
window.Toast = Toast;

window.showToast = function(message, type = 'success') {
    if (!message) return;
    
    const validIcons = ['success', 'error', 'warning', 'info', 'question'];
    const icon = validIcons.includes(type) ? type : 'info';

    return Toast.fire({
        icon: icon,
        title: message
    });
};

window.toast = {
    success: (msg) => window.showToast(msg, 'success'),
    error: (msg) => window.showToast(msg, 'error'),
    warning: (msg) => window.showToast(msg, 'warning'),
    info: (msg) => window.showToast(msg, 'info')
};

// Check for pending toasts stored before a page redirect/reload
function checkStoredToasts() {
    try {
        const success = sessionStorage.getItem('toast_success');
        if (success) {
            sessionStorage.removeItem('toast_success');
            window.showToast(success, 'success');
        }

        const error = sessionStorage.getItem('toast_error');
        if (error) {
            sessionStorage.removeItem('toast_error');
            window.showToast(error, 'error');
        }

        const info = sessionStorage.getItem('toast_info');
        if (info) {
            sessionStorage.removeItem('toast_info');
            window.showToast(info, 'info');
        }

        const warning = sessionStorage.getItem('toast_warning');
        if (warning) {
            sessionStorage.removeItem('toast_warning');
            window.showToast(warning, 'warning');
        }
    } catch (e) {
        // sessionStorage might be restricted in some iframe/private browsing modes
    }
}

// Check for Laravel session flash toasts injected into the DOM
function checkSessionToasts() {
    const el = document.getElementById('session-toast-data');
    if (!el) return;

    const success = el.dataset.success;
    const error = el.dataset.error;
    const warning = el.dataset.warning;
    const info = el.dataset.info;
    const status = el.dataset.status;

    if (success) {
        window.showToast(success, 'success');
    } else if (error) {
        window.showToast(error, 'error');
    } else if (warning) {
        window.showToast(warning, 'warning');
    } else if (info) {
        window.showToast(info, 'info');
    } else if (status) {
        window.showToast(status, 'info');
    }
}

// Run on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        checkStoredToasts();
        checkSessionToasts();
    });
} else {
    checkStoredToasts();
    checkSessionToasts();
}
