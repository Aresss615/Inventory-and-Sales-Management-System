<?php require_once __DIR__ . '/../config/config.php'; ?>
<div class="controls" style="margin-bottom: 24px;">
    <button type="button" class="btn btn-primary" onclick="openUserModal()">
        <i class="fas fa-plus"></i> Add New User
    </button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $users_result = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC");
            if ($users_result && mysqli_num_rows($users_result) > 0):
                while ($user = mysqli_fetch_assoc($users_result)):
                    $role_badges = [
                        'admin' => 'badge-danger',
                        'manager' => 'badge-warning',
                        'staff' => 'badge-info'
                    ];
                    $badge = $role_badges[$user['role']] ?? 'badge-info';
            ?>
                <tr>
                    <td><strong><?php echo $user['id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><span class="badge <?php echo $badge; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                    <td>
                        <?php if ($user['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-edit" onclick='editUser(<?php echo json_encode($user); ?>)'>
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <?php if ($user['id'] != $current_user['id']): ?>
                            <button class="btn btn-sm btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php 
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                        No users found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="userModalTitle">Add New User</h2>
            <button class="modal-close" onclick="closeUserModal()">&times;</button>
        </div>
        <form id="userForm" method="POST" action="<?php echo API_URL; ?>/users.php">
            <input type="hidden" name="id" id="userId">
            <div class="form-group">
                <label for="userUsername">Username</label>
                <input type="text" name="username" id="userUsername" required>
            </div>
            <div class="form-group">
                <label for="userFullName">Full Name</label>
                <input type="text" name="full_name" id="userFullName" required>
            </div>
            <div class="form-group">
                <label for="userEmail">Email</label>
                <input type="email" name="email" id="userEmail" required>
            </div>
            <div class="form-group">
                <label for="userRole">Role</label>
                <select name="role" id="userRole" required>
                    <option value="staff">Staff</option>
                    <option value="manager">Manager</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group" id="passwordGroup">
                <label for="userPassword">Password</label>
                <input type="password" name="password" id="userPassword" minlength="6">
                <small style="color: #64748b;">Leave blank to keep current password (when editing)</small>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" id="userActive" value="1" checked>
                    Active
                </label>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-cancel" onclick="closeUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save User</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUserModal() {
    document.getElementById('userModalTitle').textContent = 'Add New User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('userPassword').required = true;
    document.getElementById('userModal').classList.add('active');
}

function editUser(user) {
    document.getElementById('userModalTitle').textContent = 'Edit User';
    document.getElementById('userId').value = user.id;
    document.getElementById('userUsername').value = user.username;
    document.getElementById('userFullName').value = user.full_name;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userRole').value = user.role;
    document.getElementById('userActive').checked = user.is_active == 1;
    document.getElementById('userPassword').required = false;
    document.getElementById('userPassword').value = '';
    document.getElementById('userModal').classList.add('active');
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('active');
}

function deleteUser(id, username) {
    if (!confirm(`Are you sure you want to delete user "${username}"?`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?php echo API_URL; ?>/users.php';
    
    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    idInput.value = id;
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'delete';
    
    form.appendChild(idInput);
    form.appendChild(actionInput);
    document.body.appendChild(form);
    form.submit();
}
</script>
