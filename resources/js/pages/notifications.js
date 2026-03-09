window.markAsRead = function(id) {
    fetch(`/notifications/${id}/read`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const card = document.getElementById('notif-' + id);
            if (card) {
                card.classList.remove('border-emerald-200', 'bg-emerald-50/30');
                card.classList.add('border-gray-200');
                const dot = card.querySelector('.bg-emerald-500');
                if (dot) dot.remove();
                const btn = card.querySelector('button[onclick*="markAsRead"]');
                if (btn) btn.remove();
            }
            updateUnreadBadge(-1);
            showToast('Notification marked as read', 'success');
        }
    })
    .catch(() => showToast('Failed to update', 'error'));
}

window.markAllAsRead = function() {
    fetch('/notifications/read-all', {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.notification-card').forEach(card => {
                card.classList.remove('border-emerald-200', 'bg-emerald-50/30');
                card.classList.add('border-gray-200');
                const dot = card.querySelector('.bg-emerald-500');
                if (dot) dot.remove();
                const btn = card.querySelector('button[onclick*="markAsRead"]');
                if (btn) btn.remove();
            });
            const markAllBtn = document.querySelector('button[onclick="markAllAsRead()"]');
            if (markAllBtn) markAllBtn.remove();
            updateUnreadBadge(0, true);
            showToast('All notifications marked as read', 'success');
        }
    })
    .catch(() => showToast('Failed to update', 'error'));
}

function updateUnreadBadge(delta, reset = false) {
    const badge = document.querySelector('#navBellBadge');
    if (badge) {
        let current = parseInt(badge.textContent) || 0;
        let newCount = reset ? 0 : Math.max(0, current + delta);
        if (newCount > 0) {
            badge.textContent = newCount > 9 ? '9+' : newCount;
        } else {
            badge.classList.add('hidden');
        }
    }
}

window.showToast = function(msg, type = 'success') {
    const toast = document.getElementById('toast');
    const inner = document.getElementById('toastInner');
    const msgEl = document.getElementById('toastMsg');
    msgEl.textContent = msg;
    inner.className = 'flex items-center gap-2 px-4 py-3 rounded-xl shadow-lg text-white text-sm font-medium ' +
        (type === 'success' ? 'bg-emerald-600' : 'bg-red-600');
    toast.classList.remove('hidden');
    setTimeout(() => { toast.classList.remove('translate-y-2', 'opacity-0'); }, 10);
    setTimeout(() => {
        toast.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => toast.classList.add('hidden'), 300);
    }, 3000);
}
