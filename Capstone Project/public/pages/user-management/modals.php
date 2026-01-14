<!-- Create/Edit User Modal -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 id="modalTitle" class="text-xl font-bold text-gray-900 dark:text-white">Add New User</h2>
        </div>

        <form id="userForm" class="p-6 space-y-4">
            <input type="hidden" id="userId" name="user_id">

            <!-- Email (Full Width) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email <span class="text-red-600">*</span>
                </label>
                <input type="email" id="email" name="email" required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Used for login and communication</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- First Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        First Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="firstName" name="first_name" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                </div>

                <!-- Last Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Last Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="lastName" name="last_name" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                    <input type="tel" id="phone" name="phone"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Role <span class="text-red-600">*</span>
                    </label>
                    <select id="role" name="role_name" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                        <option value="">Select Role</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo htmlspecialchars($role['role_name']); ?>">
                                <?php echo htmlspecialchars($role['role_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Department -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                    <input type="text" id="department" name="department"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                </div>

                <!-- Position -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Position</label>
                    <input type="text" id="position" name="position"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="passwordFields">
                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Password <span class="text-red-600" id="passwordRequired">*</span>
                    </label>
                    <input type="password" id="password" name="password"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Minimum 8 characters</p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Confirm Password <span class="text-red-600" id="confirmRequired">*</span>
                    </label>
                    <input type="password" id="confirmPassword" name="confirm_password"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select id="status" name="status"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-600">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeUserModal()"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button type="submit" id="submitBtn"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Save User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View User Modal -->
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">User Details</h2>
            <button onclick="closeViewModal()"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>

        <div id="viewContent" class="p-6">
            <!-- Content loaded via JavaScript -->
        </div>
    </div>
</div>

<script>
    let isEditMode = false;

    // Open create modal
    function openCreateModal() {
        isEditMode = false;
        document.getElementById('modalTitle').textContent = 'Add New User';
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('password').required = true;
        document.getElementById('confirmPassword').required = true;
        document.getElementById('passwordRequired').style.display = 'inline';
        document.getElementById('confirmRequired').style.display = 'inline';
        document.getElementById('role').disabled = false; // Enable role for new users
        document.getElementById('userModal').classList.remove('hidden');
        document.getElementById('userModal').classList.add('flex');
    }

    // Open edit modal
    function editUser(userId) {
        isEditMode = true;
        document.getElementById('modalTitle').textContent = 'Edit User';
        document.getElementById('userId').value = userId;
        document.getElementById('password').required = false;
        document.getElementById('confirmPassword').required = false;
        document.getElementById('passwordRequired').style.display = 'none';
        document.getElementById('confirmRequired').style.display = 'none';

        // Fetch user data
        fetch(`ajax/get_user.php?user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.data;
                    document.getElementById('email').value = user.email;
                    document.getElementById('firstName').value = user.first_name;
                    document.getElementById('lastName').value = user.last_name;
                    document.getElementById('phone').value = user.phone || '';
                    document.getElementById('role').value = user.role_name;
                    document.getElementById('department').value = user.department || '';
                    document.getElementById('position').value = user.position || '';
                    document.getElementById('status').value = user.status;

                    // Disable role dropdown if editing self
                    const currentUserId = <?php echo $_SESSION['user_id']; ?>;
                    if (userId == currentUserId) {
                        document.getElementById('role').disabled = true;
                        document.getElementById('role').title = 'You cannot change your own role';
                    } else {
                        document.getElementById('role').disabled = false;
                        document.getElementById('role').title = '';
                    }

                    document.getElementById('userModal').classList.remove('hidden');
                    document.getElementById('userModal').classList.add('flex');
                } else {
                    alert('Failed to load user data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
    }

    // Close user modal
    function closeUserModal() {
        document.getElementById('userModal').classList.add('hidden');
        document.getElementById('userModal').classList.remove('flex');
        document.getElementById('userForm').reset();
    }

    // View user
    function viewUser(userId) {
        fetch(`ajax/get_user.php?user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.data;
                    const content = `
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="bg-red-600 text-white rounded-full w-16 h-16 flex items-center justify-center font-bold text-2xl">
                                ${user.first_name.charAt(0)}${user.last_name.charAt(0)}
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">${user.first_name} ${user.last_name}</h3>
                                <p class="text-gray-600 dark:text-gray-400">${user.email}</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                                <p class="font-medium text-gray-900 dark:text-white">${user.email}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                                <p class="font-medium text-gray-900 dark:text-white">${user.phone || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Role</p>
                                <p class="font-medium text-gray-900 dark:text-white">${user.role_name}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Department</p>
                                <p class="font-medium text-gray-900 dark:text-white">${user.department || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Position</p>
                                <p class="font-medium text-gray-900 dark:text-white">${user.position || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full ${user.status === 'active' ? 'bg-green-100 text-green-800' :
                            user.status === 'suspended' ? 'bg-yellow-100 text-yellow-800' :
                                'bg-gray-100 text-gray-800'
                        }">
                                    ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Created</p>
                                <p class="font-medium text-gray-900 dark:text-white">${new Date(user.created_at).toLocaleDateString()}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Last Login</p>
                                <p class="font-medium text-gray-900 dark:text-white">${user.last_login ? new Date(user.last_login).toLocaleDateString() : 'Never'}</p>
                            </div>
                        </div>
                    </div>
                `;

                    document.getElementById('viewContent').innerHTML = content;
                    document.getElementById('viewModal').classList.remove('hidden');
                    document.getElementById('viewModal').classList.add('flex');
                } else {
                    alert('Failed to load user data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
    }

    // Close view modal
    function closeViewModal() {
        document.getElementById('viewModal').classList.add('hidden');
        document.getElementById('viewModal').classList.remove('flex');
    }

    // Delete user
    function deleteUser(userId) {
        if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            return;
        }

        fetch('ajax/delete_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User deleted successfully');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to delete user');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
    }

    // Handle form submission
    document.getElementById('userForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        // Validate passwords match
        if (data.password && data.password !== data.confirm_password) {
            alert('Passwords do not match');
            return;
        }

        // Remove confirm_password from data
        delete data.confirm_password;

        const url = isEditMode ? 'ajax/update_user.php' : 'ajax/create_user.php';
        const submitBtn = document.getElementById('submitBtn');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(isEditMode ? 'User updated successfully' : 'User created successfully');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to save user');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save User';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save User';
            });
    });

    // Clear filters function
    function clearFilters() {
        window.location.href = 'index.php';
    }

    // Toggle select all checkboxes
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateBulkActions();
    }

    // Update bulk actions bar
    function updateBulkActions() {
        const selected = document.querySelectorAll('.user-checkbox:checked');
        const count = selected.length;
        const bulkBar = document.getElementById('bulkActionsBar');
        const countSpan = document.getElementById('selectedCount');

        if (count > 0) {
            bulkBar.classList.remove('hidden');
            bulkBar.classList.add('flex');
            countSpan.textContent = count;
        } else {
            bulkBar.classList.add('hidden');
            bulkBar.classList.remove('flex');
            document.getElementById('selectAll').checked = false;
        }
    }

    // Bulk delete function
    function bulkDelete() {
        const selected = Array.from(document.querySelectorAll('.user-checkbox:checked'))
            .map(cb => cb.value);

        if (selected.length === 0) {
            alert('No users selected');
            return;
        }

        if (!confirm(`Are you sure you want to delete ${selected.length} user(s)? This action cannot be undone.`)) {
            return;
        }

        fetch('ajax/bulk_delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_ids: selected })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Failed to delete users');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
    }

    // Export CSV function
    function exportCSV() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = 'ajax/export_csv.php?' + params.toString();
    }
</script>