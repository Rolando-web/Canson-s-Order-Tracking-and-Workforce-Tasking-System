const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

window.switchAssignmentTab = function(tab) {
    const activeContainer = document.getElementById('activeAssignments');
    const completedContainer = document.getElementById('completedAssignments');
    const activeBtn = document.getElementById('tabActive');
    const completedBtn = document.getElementById('tabCompleted');

    if (tab === 'active') {
        activeContainer.classList.remove('hidden');
        completedContainer.classList.add('hidden');
        activeBtn.className = 'px-4 py-2 rounded-md text-sm font-semibold transition-colors bg-emerald-600 text-white';
        completedBtn.className = 'px-4 py-2 rounded-md text-sm font-semibold transition-colors text-gray-600 hover:bg-gray-200';
    } else {
        activeContainer.classList.add('hidden');
        completedContainer.classList.remove('hidden');
        completedBtn.className = 'px-4 py-2 rounded-md text-sm font-semibold transition-colors bg-emerald-600 text-white';
        activeBtn.className = 'px-4 py-2 rounded-md text-sm font-semibold transition-colors text-gray-600 hover:bg-gray-200';
    }
}

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
                showToast('All items completed! Phase is now ready for delivery.', 'success');
                moveCardToCompleted(assignmentId);
            } else {
                showToast('Progress updated successfully!', 'success');
                setTimeout(() => window.location.reload(), 1000);
            }
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

/**
 * Move a completed assignment card from Active tab to Completed tab in real-time.
 */
function moveCardToCompleted(assignmentId) {
    const card = document.querySelector(`[data-assignment-id="${assignmentId}"]`);
    if (!card) {
        setTimeout(() => window.location.reload(), 1000);
        return;
    }

    const activeContainer = document.getElementById('activeAssignments');
    const completedContainer = document.getElementById('completedAssignments');

    // Animate card out
    card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
    card.style.opacity = '0';
    card.style.transform = 'translateX(-20px)';

    setTimeout(() => {
        // Remove from active
        card.remove();

        // Check if active is now empty, show "No Active Work" message
        const remainingActive = activeContainer.querySelectorAll('[data-assignment-id]');
        if (remainingActive.length === 0) {
            const emptyState = document.createElement('div');
            emptyState.className = 'flex flex-col items-center justify-center py-10 text-center';
            emptyState.innerHTML = `
                <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-sm font-bold text-gray-400">No Active Work</h3>
                <p class="text-xs text-gray-400 mt-1">All your tasks are completed. Great job!</p>
            `;
            activeContainer.appendChild(emptyState);
        }

        // Build a completed card from the data
        const orderId = card.dataset.orderId || '';
        const phase = card.dataset.phase || '';

        const completedCard = document.createElement('div');
        completedCard.className = 'border border-gray-200 rounded-xl p-2 sm:p-5 border-l-4 border-l-emerald-400 bg-white shadow-sm';
        completedCard.style.opacity = '0';
        completedCard.style.transform = 'translateX(20px)';
        completedCard.style.transition = 'opacity 0.4s ease, transform 0.4s ease';

        completedCard.innerHTML = `
            <div class="flex items-start justify-between gap-2 mb-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-sm font-bold text-gray-800">
                            Order #${orderId}
                            ${phase ? `<span class="text-gray-400 font-normal">&bull; Phase ${phase}</span>` : ''}
                        </h4>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700">
                            Completed
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Completed — Ready for delivery
                </div>
            </div>
        `;

        // Remove "No Completed Work Yet" empty state if present
        const emptyCompleted = completedContainer.querySelector('.flex.flex-col.items-center');
        if (emptyCompleted && emptyCompleted.textContent.includes('No Completed Work Yet')) {
            emptyCompleted.remove();
        }

        completedContainer.prepend(completedCard);

        // Animate in
        requestAnimationFrame(() => {
            completedCard.style.opacity = '1';
            completedCard.style.transform = 'translateX(0)';
        });

        // Auto-switch to Completed tab after a brief delay
        setTimeout(() => {
            switchAssignmentTab('completed');
        }, 600);

        // Update tab badge counts
        updateTabBadges();

    }, 450);
}

/**
 * Update the tab badge counters after moving a card.
 */
function updateTabBadges() {
    const activeContainer = document.getElementById('activeAssignments');
    const completedContainer = document.getElementById('completedAssignments');
    const activeBtn = document.getElementById('tabActive');
    const completedBtn = document.getElementById('tabCompleted');

    const activeCount = activeContainer.querySelectorAll('[data-assignment-id]').length;
    const completedCount = completedContainer.querySelectorAll('.border-l-emerald-400').length;

    // Update Active tab badge
    const activeBadge = activeBtn.querySelector('span');
    if (activeCount > 0) {
        if (activeBadge) {
            activeBadge.textContent = activeCount;
        } else {
            const badge = document.createElement('span');
            badge.className = 'ml-1 inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold bg-white/20';
            badge.textContent = activeCount;
            activeBtn.appendChild(badge);
        }
    } else if (activeBadge) {
        activeBadge.remove();
    }

    // Update Completed tab badge
    const completedBadge = completedBtn.querySelector('span');
    if (completedCount > 0) {
        if (completedBadge) {
            completedBadge.textContent = completedCount;
        } else {
            const badge = document.createElement('span');
            badge.className = 'ml-1 inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold bg-gray-300/50 text-gray-600';
            badge.textContent = completedCount;
            completedBtn.appendChild(badge);
        }
    }
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
