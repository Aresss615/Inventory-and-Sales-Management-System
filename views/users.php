<?php require_once __DIR__ . '/../config/config.php'; ?>
<?php displayNotification(); ?>
<?php
checkPermission('users_view');
$can_create = hasPermission('users_create');
$can_edit = hasPermission('users_edit');
$can_delete = hasPermission('users_delete');
?>

<div class="controls" style="margin-bottom: 24px;">
    <?php if ($can_create): ?>
    <button type="button" class="btn btn-primary" onclick="openUserModal()">
        <i class="fas fa-plus"></i> Add New Staff/User
    </button>
    <?php endif; ?>
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
                <th>Created By</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $users_query = "SELECT u.*, r.display_name as role_display, r.name as role_name, 
                           creator.username as creator_name
                           FROM users u
                           JOIN roles r ON u.role_id = r.id
                           LEFT JOIN users creator ON u.created_by = creator.id
                           ORDER BY u.id ASC";
            $users_result = mysqli_query($conn, $users_query);
            if ($users_result && mysqli_num_rows($users_result) > 0):
                while ($user = mysqli_fetch_assoc($users_result)):
                    $role_badges = [
                        'admin' => 'badge-danger',
                        'manager' => 'badge-warning',
                        'cashier' => 'badge-info',
                        'stock_manager' => 'badge-primary'
                    ];
                    $badge = $role_badges[$user['role_name']] ?? 'badge-secondary';
            ?>
                <tr>
                    <td><strong><?php echo $user['id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($user['role_display']); ?></span></td>
                    <td>
                        <?php if ($user['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $user['creator_name'] ? htmlspecialchars($user['creator_name']) : 'System'; ?></td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($can_edit): ?>
                            <button class="btn btn-sm btn-edit" onclick='editUser(<?php echo json_encode($user); ?>)'>
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <?php endif; ?>
                            <?php if ($can_delete && $user['id'] != $current_user['id']): ?>
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
                    <td colspan="9" style="text-align: center; padding: 40px; color: #94a3b8;">
                        No users found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="userModal" class="modal">
    <div class="modal-content" style="max-width: 600px; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header">
            <h2 class="modal-title" id="userModalTitle">Add New User</h2>
            <button class="modal-close" onclick="closeUserModal()">&times;</button>
        </div>
        <form id="userForm" method="POST" action="<?php echo API_URL; ?>/users.php" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            <div style="padding: 20px; overflow-y: auto; flex: 1;">
                <input type="hidden" name="id" id="userId">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="userUsername">Username</label>
                    <input type="text" name="username" id="userUsername" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="userFullName">Full Name</label>
                    <input type="text" name="full_name" id="userFullName" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="userEmail">Email</label>
                    <input type="email" name="email" id="userEmail" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="userRole">Role</label>
                    <select name="role_id" id="userRole" required>
                        <?php
                        $roles_query = "SELECT * FROM roles ORDER BY is_system DESC, display_name ASC";
                        $roles_result = mysqli_query($conn, $roles_query);
                        while ($role = mysqli_fetch_assoc($roles_result)):
                        ?>
                            <option value="<?php echo $role['id']; ?>">
                                <?php echo htmlspecialchars($role['display_name']); ?>
                                <?php if ($role['is_system']): ?>(System)<?php endif; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group" id="passwordGroup" style="margin-bottom: 16px;">
                    <label for="userPassword">Password</label>
                    <input type="password" name="password" id="userPassword" minlength="6">
                    <small style="color: #64748b;">Leave blank to keep current password (when editing)</small>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>
                        <input type="checkbox" name="is_active" id="userActive" value="1" checked>
                        Active
                    </label>
                </div>
            </div>
            <div class="modal-footer" style="flex-shrink: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save User</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteUserModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h2 class="modal-title">Delete User</h2>
            <button class="modal-close" onclick="closeDeleteUserModal()">&times;</button>
        </div>
        <form method="POST" action="<?php echo API_URL; ?>/users.php">
            <div style="padding: 20px;">
                <p style="color: #64748b; margin-bottom: 0;">Are you sure you want to delete user "<strong id="deleteUserName"></strong>"? This action cannot be undone.</p>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteUserId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteUserModal()">Cancel</button>
                <button type="submit" class="btn btn-delete">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUserModal() {
    document.getElementById('userModalTitle').textContent = 'Add New Staff/User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('userPassword').required = true;
    document.getElementById('userModal').style.display = 'flex';
}

function editUser(user) {
    document.getElementById('userModalTitle').textContent = 'Edit User';
    document.getElementById('userId').value = user.id;
    document.getElementById('userUsername').value = user.username;
    document.getElementById('userFullName').value = user.full_name;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userRole').value = user.role_id;
    document.getElementById('userActive').checked = user.is_active == 1;
    document.getElementById('userPassword').required = false;
    document.getElementById('userPassword').value = '';
    document.getElementById('userModal').style.display = 'flex';
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

function deleteUser(id, username) {
    document.getElementById('deleteUserId').value = id;
    document.getElementById('deleteUserName').textContent = username;
    document.getElementById('deleteUserModal').style.display = 'flex';
}

function closeDeleteUserModal() {
    document.getElementById('deleteUserModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>
