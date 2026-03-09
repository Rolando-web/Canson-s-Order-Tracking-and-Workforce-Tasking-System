let currentMode = 'create';
let currentEmployeeId = null;

window.openModal = function(mode, id = null, firstName = '', lastName = '', role = 'employee', contact = '') {
    currentMode = mode;
    currentEmployeeId = id;

    const modal = document.getElementById('employeeModal');
    const title = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('modalSubmitBtn');

    document.getElementById('empFirstName').value = firstName;
    document.getElementById('empLastName').value = lastName;
    document.getElementById('empRole').value = role;
    document.getElementById('empContact').value = contact;
    document.getElementById('empPassword').value = '';

    if (mode === 'edit') {
        title.textContent = 'Edit Employee';
        submitBtn.textContent = 'Update Employee';
        document.getElementById('empPassword').placeholder = 'Leave blank to keep current';
        document.getElementById('passwordHint').textContent = 'Leave blank to keep the current password';
    } else {
        title.textContent = 'Add Employee';
        submitBtn.textContent = 'Save Employee';
        document.getElementById('empPassword').placeholder = 'Enter password';
        document.getElementById('passwordHint').textContent = 'Minimum 6 characters';
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

window.closeModal = function() {
    const modal = document.getElementById('employeeModal');
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

window.saveEmployee = function() {
    const firstName = document.getElementById('empFirstName').value.trim();
    const lastName = document.getElementById('empLastName').value.trim();
    const role = document.getElementById('empRole').value;
    const contact = document.getElementById('empContact').value.trim();
    const password = document.getElementById('empPassword').value;

    if (!firstName || !lastName) {
        alert('Please enter both first name and last name.');
        return;
    }

    if (currentMode === 'create' && password.length < 6) {
        alert('Password must be at least 6 characters.');
        return;
    }

    if (currentMode === 'edit' && password && password.length < 6) {
        alert('Password must be at least 6 characters.');
        return;
    }

    const submitBtn = document.getElementById('modalSubmitBtn');
    submitBtn.disabled = true;
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Saving...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    let url = '/employees';
    let method = 'POST';
    const body = {
        empFirstName: firstName,
        empLastName: lastName,
        empRole: role,
        empContact: contact,
    };

    if (password) {
        body.password = password;
    }

    if (currentMode === 'edit' && currentEmployeeId) {
        url = '/employees/' + currentEmployeeId;
        method = 'PUT';
    }

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(body),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            showToast(currentMode === 'edit' ? 'Employee updated successfully!' : 'Employee added successfully!', 'success');
            setTimeout(() => window.location.reload(), 800);
        } else {
            const msg = data.message || 'Failed to save employee. Please try again.';
            showToast(msg, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

window.deleteEmployee = function(id, name) {
    if (!confirm('Are you sure you want to remove ' + name + '?')) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch('/employees/' + id, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Employee removed successfully!', 'success');
            setTimeout(() => window.location.reload(), 800);
        } else {
            showToast('Failed to remove employee.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred.', 'error');
    });
}

window.showToast = function(message, type) {
    const existing = document.getElementById('employeeToast');
    if (existing) existing.remove();

    const bgColor = type === 'success' ? 'bg-emerald-600' : 'bg-red-600';
    const toast = document.createElement('div');
    toast.id = 'employeeToast';
    toast.className = `fixed top-6 right-6 z-[100] ${bgColor} text-white px-5 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2`;
    toast.innerHTML = `
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            ${type === 'success'
                ? '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>'
            }
        </svg>
        ${message}
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});
