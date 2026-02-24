@extends('partials.app', ['title' => 'Employees - Canson', 'activePage' => 'employees'])

@push('styles')
    @vite('resources/css/pages/employees.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/employees.js')
@endpush

@section('nav')
    <div class="flex items-center justify-between w-full">
        <h1 class="text-lg font-semibold text-emerald-600">Canson <span class="text-gray-700 font-normal">Manager</span></h1>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ now()->format('l, F d, Y') }}</span>
            <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white text-sm font-bold">AD</div>
        </div>
    </div>
@endsection

@section('content')
<div class="employees-page">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Employees</h2>
            <p class="text-gray-500 mt-1 text-sm">Manage your team members and their account information</p>
        </div>
        <button onclick="openModal('create')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 self-start">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Add Employee
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 flex-none rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                    </svg>
                </div>
                <div class="grow">
                    <p class="text-2xl font-bold text-gray-900 text-end">{{ $totalEmployees ?? 0 }}</p>
                    <p class="text-sm text-gray-500 text-end">Total Employees</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 flex-none rounded-xl bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="grow">
                    <p class="text-2xl font-bold text-gray-900 text-end">{{ $activeCount ?? 0 }}</p>
                    <p class="text-sm text-gray-500 text-end">Active</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 flex-none rounded-xl bg-gray-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <div class="grow">
                    <p class="text-2xl font-bold text-gray-900 text-end">{{ $inactiveCount ?? 0 }}</p>
                    <p class="text-sm text-gray-500 text-end">Inactive</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <input type="text" placeholder="Search employees..." class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <select class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option>All Status</option>
                <option>Active</option>
                <option>Inactive</option>
            </select>
        </div>
    </div>

    {{-- Employees Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3.5 font-semibold text-gray-600">Employee</th>
                        <th class="px-6 py-3.5 font-semibold text-gray-600">Role</th>
                        <th class="px-6 py-3.5 font-semibold text-gray-600">Department</th>
                        <th class="px-6 py-3.5 font-semibold text-gray-600">Contact Number</th>
                        <th class="px-6 py-3.5 font-semibold text-gray-600">Status</th>
                        <th class="px-6 py-3.5 font-semibold text-gray-600 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($employees as $emp)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full {{ $emp['color'] }} flex items-center justify-center text-white text-xs font-bold">
                                        {{ strtoupper(substr($emp['first'], 0, 1) . substr($emp['last'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $emp['first'] }} {{ $emp['last'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $roleConfig = match($emp['role']) {
                                        'employee' => ['label' => 'Employee', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                        'admin' => ['label' => 'Admin', 'color' => 'bg-purple-50 text-purple-700 border-purple-200'],
                                        'super_admin' => ['label' => 'Super Admin', 'color' => 'bg-red-50 text-red-700 border-red-200'],
                                        default => ['label' => 'Unknown', 'color' => 'bg-gray-50 text-gray-600 border-gray-200'],
                                    };
                                @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium border {{ $roleConfig['color'] }}">{{ $roleConfig['label'] }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($emp['role'] === 'employee')
                                    @php
                                        $deptConfig = match($emp['department'] ?? 'Worker') {
                                            'Worker' => ['label' => 'Worker', 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                            'Driver' => ['label' => 'Driver', 'color' => 'bg-orange-50 text-orange-700 border-orange-200'],
                                            default => ['label' => 'Worker', 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                        };
                                    @endphp
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium border {{ $deptConfig['color'] }}">{{ $deptConfig['label'] }}</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $emp['contact'] }}</td>
                            <td class="px-6 py-4">
                                @if($emp['status'] === 'Active')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">Active</span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="openModal('edit', {{ $emp['id'] }}, '{{ addslashes($emp['first']) }}', '{{ addslashes($emp['last']) }}', '{{ $emp['role'] }}', '{{ $emp['contact'] }}', '{{ $emp['department'] ?? 'Worker' }}')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <button onclick="deleteEmployee({{ $emp['id'] }}, '{{ addslashes($emp['first'] . ' ' . $emp['last']) }}')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Create / Edit Employee Modal --}}
<div id="employeeModal" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>

    {{-- Modal Content --}}
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative" onclick="event.stopPropagation()">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 id="modalTitle" class="text-lg font-bold text-gray-900">Add Employee</h3>
                <button onclick="closeModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">First Name</label>
                        <input id="empFirstName" type="text" placeholder="Juan"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Last Name</label>
                        <input id="empLastName" type="text" placeholder="Dela Cruz"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Role</label>
                    <select id="empRole" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="employee">Employee</option>
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <div id="departmentFieldWrapper">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
                    <select id="empDepartment" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="Worker">Worker</option>
                        <option value="Driver">Driver</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Workers handle orders. Drivers handle delivery (can also help with orders).</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Number</label>
                    <input id="empContact" type="text" placeholder="0917-123-4567"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>
                <div id="passwordField">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <input id="empPassword" type="password" placeholder="Enter password"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <p id="passwordHint" class="text-xs text-gray-400 mt-1"></p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200">
                <button onclick="closeModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    Cancel
                </button>
                <button id="modalSubmitBtn" onclick="saveEmployee()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Save Employee
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentMode = 'create';
    let currentEmployeeId = null;

    function openModal(mode, id = null, firstName = '', lastName = '', role = 'employee', contact = '', department = 'Worker') {
        currentMode = mode;
        currentEmployeeId = id;

        const modal = document.getElementById('employeeModal');
        const title = document.getElementById('modalTitle');
        const submitBtn = document.getElementById('modalSubmitBtn');

        document.getElementById('empFirstName').value = firstName;
        document.getElementById('empLastName').value = lastName;
        document.getElementById('empRole').value = role;
        document.getElementById('empDepartment').value = department;
        document.getElementById('empContact').value = contact;
        document.getElementById('empPassword').value = '';

        // Show department field only for employee role
        toggleDepartmentField(role);

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

    function closeModal() {
        const modal = document.getElementById('employeeModal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // Show/hide department field based on role
    function toggleDepartmentField(role) {
        const wrapper = document.getElementById('departmentFieldWrapper');
        wrapper.style.display = role === 'employee' ? 'block' : 'none';
    }

    document.getElementById('empRole').addEventListener('change', function() {
        toggleDepartmentField(this.value);
    });

    function saveEmployee() {
        const firstName = document.getElementById('empFirstName').value.trim();
        const lastName = document.getElementById('empLastName').value.trim();
        const role = document.getElementById('empRole').value;
        const department = document.getElementById('empDepartment').value;
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
            empDepartment: role === 'employee' ? department : null,
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

    function deleteEmployee(id, name) {
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

    function showToast(message, type) {
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
</script>
@endpush
@endsection
