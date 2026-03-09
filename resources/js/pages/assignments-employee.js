const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

window.updateMyAssignment = function(assignmentId, newStatus, btn) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Updating...';

    fetch(`/assignments/${assignmentId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ status: newStatus }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const label = newStatus === 'in_progress' ? 'In Progress' : 'Completed';
            showToast(`Assignment updated to ${label}!`, 'success');
            setTimeout(() => window.location.reload(), 800);
        } else {
            showToast(data.message || 'Failed to update assignment.', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

window.submitProgress = function(event, assignmentId) {
    event.preventDefault();
    const form = document.getElementById(`progressForm-${assignmentId}`);
    const formData = new FormData(form);

    const items = [];
    let index = 0;
    while (formData.has(`items[${index}][id]`)) {
        const addQty = parseInt(formData.get(`items[${index}][add_qty]`)) || 0;
        if (addQty > 0) {
            items.push({
                id: parseInt(formData.get(`items[${index}][id]`)),
                add_qty: addQty,
            });
        }
        index++;
    }

    if (items.length === 0) {
        showToast('Please enter at least 1 item quantity to add.', 'error');
        return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Saving...';

    fetch('/assignments/update-progress', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            assignment_id: assignmentId,
            items: items,
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.all_completed) {
                showToast('All items completed! Order is now Ready for Delivery.', 'success');
            } else {
                showToast('Progress updated successfully!', 'success');
            }
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.message || 'Failed to update progress.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function showToast(message, type) {
    const existing = document.getElementById('employeeToast');
    if (existing) existing.remove();

    const bgColor = type === 'success' ? 'bg-emerald-600' : 'bg-red-600';
    const toast = document.createElement('div');
    toast.id = 'employeeToast';
    toast.className = `${bgColor} text-white px-5 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2`;
    toast.innerHTML = `
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            ${type === 'success'
                ? '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>'
            }
        </svg>
        ${message}
    `;
    document.getElementById('toastContainer').appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}
