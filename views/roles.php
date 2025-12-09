<?php require_once __DIR__ . '/../config/config.php'; ?>
<?php displayNotification(); ?>
<div class="controls" style="margin-bottom: 24px;">
    <button type="button" class="btn btn-primary" onclick="openRoleModal()">
        <i class="fas fa-plus"></i> Add New Role
    </button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Role Name</th>
                <th>Display Name</th>
                <th>Description</th>
                <th>Users</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $roles_query = "SELECT r.*, COUNT(DISTINCT u.id) as user_count 
                           FROM roles r 
                           LEFT JOIN users u ON r.id = u.role_id 
                           GROUP BY r.id 
                           ORDER BY r.is_system DESC, r.id ASC";
            $roles_result = mysqli_query($conn, $roles_query);
            if ($roles_result && mysqli_num_rows($roles_result) > 0):
                while ($role = mysqli_fetch_assoc($roles_result)):
            ?>
                <tr>
                    <td><strong><?php echo $role['id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($role['name']); ?></td>
                    <td><?php echo htmlspecialchars($role['display_name']); ?></td>
                    <td><?php echo htmlspecialchars($role['description']); ?></td>
                    <td><span class="badge badge-secondary"><?php echo $role['user_count']; ?> users</span></td>
                    <td>
                        <?php if ($role['is_system']): ?>
                            <span class="badge badge-warning">System</span>
                        <?php else: ?>
                            <span class="badge badge-success">Custom</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-info" onclick='viewRolePermissions(<?php echo json_encode($role); ?>)'>
                                <i class="fas fa-eye"></i> View
                            </button>
                            <?php if (!$role['is_system']): ?>
                            <button class="btn btn-sm btn-edit" onclick='editRole(<?php echo json_encode($role); ?>)'>
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <?php if ($role['user_count'] == 0): ?>
                            <button class="btn btn-sm btn-delete" onclick="deleteRole(<?php echo $role['id']; ?>, '<?php echo htmlspecialchars($role['display_name']); ?>')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php 
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                        No roles found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}
.badge-info {
    background-color: #3b82f6;
    color: white;
}
.badge-secondary {
    background-color: #64748b;
    color: white;
}
.badge-warning {
    background-color: #f59e0b;
    color: white;
}
.badge-success {
    background-color: #10b981;
    color: white;
}
</style>

<div id="roleModal" class="modal">
    <div class="modal-content" style="max-width: 700px; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header">
            <h2 class="modal-title" id="roleModalTitle">Add New Role</h2>
            <button class="modal-close" onclick="closeRoleModal()">&times;</button>
        </div>
        <form id="roleForm" method="POST" action="<?php echo API_URL; ?>/roles.php" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            <div style="flex: 1; overflow-y: auto; padding: 20px;">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="roleId">
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label for="roleName">Role Name (lowercase, no spaces)</label>
                <input type="text" name="name" id="roleName" pattern="[a-z_]+" required>
                <small style="color: #64748b;">Example: cashier, stock_manager</small>
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label for="roleDisplayName">Display Name</label>
                <input type="text" name="display_name" id="roleDisplayName" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label for="roleDescription">Description</label>
                <textarea name="description" id="roleDescription" rows="2"></textarea>
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label>Permissions</label>
                <div style="max-height: 250px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: #f8fafc;">
                    <?php
                    $permissions_query = "SELECT * FROM permissions ORDER BY module, display_name";
                    $permissions_result = mysqli_query($conn, $permissions_query);
                    $current_module = '';
                    while ($permission = mysqli_fetch_assoc($permissions_result)):
                        if ($current_module != $permission['module']):
                            if ($current_module != '') echo '</div>';
                            $current_module = $permission['module'];
                            echo '<div style="margin-bottom: 12px;">';
                            echo '<h4 style="color: #334155; margin-bottom: 6px; text-transform: capitalize; font-size: 13px; font-weight: 600;">' . ucfirst($permission['module']) . '</h4>';
                        endif;
                    ?>
                        <label style="display: flex; align-items: center; padding: 4px 0; cursor: pointer;">
                            <input type="checkbox" name="permissions[]" value="<?php echo $permission['id']; ?>" class="permission-checkbox" style="margin-right: 8px;">
                            <span style="flex: 1; font-size: 13px;">
                                <strong><?php echo $permission['display_name']; ?></strong>
                                <?php if ($permission['description']): ?>
                                    <br><small style="color: #64748b; font-size: 11px;"><?php echo $permission['description']; ?></small>
                                <?php endif; ?>
                            </span>
                        </label>
                    <?php 
                    endwhile;
                    echo '</div>';
                    ?>
                </div>
                <div style="margin-top: 6px;">
                    <button type="button" onclick="selectAllPermissions()" class="btn btn-sm btn-secondary">Select All</button>
                    <button type="button" onclick="deselectAllPermissions()" class="btn btn-sm btn-secondary">Deselect All</button>
                </div>
            </div>
            </div>
            
            <div class="modal-footer" style="flex-shrink: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeRoleModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Role</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteRoleModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h2 class="modal-title">Delete Role</h2>
            <button class="modal-close" onclick="closeDeleteRoleModal()">&times;</button>
        </div>
        <form method="POST" action="<?php echo API_URL; ?>/roles.php">
            <div style="padding: 20px;">
                <p style="color: #64748b; margin-bottom: 0;">Are you sure you want to delete the role "<strong id="deleteRoleName"></strong>"? This action cannot be undone.</p>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteRoleId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteRoleModal()">Cancel</button>
                <button type="submit" class="btn btn-delete">Delete</button>
            </div>
        </form>
    </div>
</div>

<div id="viewPermissionsModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2 class="modal-title" id="viewPermissionsTitle">Role Permissions</h2>
            <button class="modal-close" onclick="closeViewPermissionsModal()">&times;</button>
        </div>
        <div id="permissionsContent" style="padding: 20px; max-height: 400px; overflow-y: auto;">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeViewPermissionsModal()">Close</button>
        </div>
    </div>
</div>

<style>
#permissionsContent input[type="checkbox"]:disabled {
    opacity: 1 !important;
    cursor: not-allowed;
}

#permissionsContent input[type="checkbox"]:disabled:checked {
    accent-color: #059669;
    filter: brightness(1.1);
}

#permissionsContent input[type="checkbox"]:disabled:not(:checked) {
    opacity: 0.5;
}
</style>

<script>
function openRoleModal() {
    document.getElementById('roleModal').style.display = 'flex';
    document.getElementById('roleModalTitle').textContent = 'Add New Role';
    document.getElementById('roleForm').reset();
    document.getElementById('roleId').value = '';
    deselectAllPermissions();
}

function closeRoleModal() {
    document.getElementById('roleModal').style.display = 'none';
}

function editRole(role) {
    document.getElementById('roleModal').style.display = 'flex';
    document.getElementById('roleModalTitle').textContent = 'Edit Role';
    document.getElementById('roleId').value = role.id;
    document.getElementById('roleName').value = role.name;
    document.getElementById('roleDisplayName').value = role.display_name;
    document.getElementById('roleDescription').value = role.description;
    
    fetch('<?php echo API_URL; ?>/roles.php?action=get_permissions&role_id=' + role.id)
        .then(response => response.json())
        .then(permissions => {
            deselectAllPermissions();
            permissions.forEach(permId => {
                const checkbox = document.querySelector(`input[name="permissions[]"][value="${permId}"]`);
                if (checkbox) checkbox.checked = true;
            });
        });
}

function deleteRole(id, name) {
    document.getElementById('deleteRoleId').value = id;
    document.getElementById('deleteRoleName').textContent = name;
    document.getElementById('deleteRoleModal').style.display = 'flex';
}

function closeDeleteRoleModal() {
    document.getElementById('deleteRoleModal').style.display = 'none';
}

function selectAllPermissions() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
}

function deselectAllPermissions() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
}

function viewRolePermissions(role) {
    document.getElementById('viewPermissionsModal').style.display = 'flex';
    document.getElementById('viewPermissionsTitle').textContent = role.display_name + ' Permissions';
    fetch('<?php echo API_URL; ?>/roles.php?action=get_permissions&role_id=' + role.id)
        .then(response => response.json())
        .then(permissionIds => {
            const content = document.getElementById('permissionsContent');
            content.innerHTML = '<p style="color: #64748b; margin-bottom: 16px;">' + role.description + '</p>';
            <?php
            $permissions_result = mysqli_query($conn, "SELECT * FROM permissions ORDER BY module, display_name");
            $perms_by_module = [];
            while ($perm = mysqli_fetch_assoc($permissions_result)) {
                $perms_by_module[$perm['module']][] = $perm;
            }
            echo 'const allPermissions = ' . json_encode($perms_by_module) . ';';
            ?>
            const permissionIdsStr = permissionIds.map(id => String(id));
            for (const [module, perms] of Object.entries(allPermissions)) {
                content.innerHTML += '<h4 style="color: #334155; margin-top: 16px; margin-bottom: 8px; text-transform: capitalize; font-size: 14px; font-weight: 600;">' + module + '</h4>';
                content.innerHTML += '<div style="margin-bottom: 12px;">';
                perms.forEach(p => {
                    const isChecked = permissionIdsStr.includes(String(p.id));
                    content.innerHTML += '<label style="display: flex; align-items: center; padding: 6px 0; cursor: default;">';
                    content.innerHTML += '<input type="checkbox" ' + (isChecked ? 'checked' : '') + ' disabled style="margin-right: 8px;">';
                    content.innerHTML += '<span style="flex: 1; font-size: 13px; color: ' + (isChecked ? '#1e293b' : '#64748b') + ';"><strong>' + p.display_name + '</strong>';
                    if (p.description) {
                        content.innerHTML += '<br><small style="color: #64748b; font-size: 11px;">' + p.description + '</small>';
                    }
                    content.innerHTML += '</span></label>';
                });
                content.innerHTML += '</div>';
            }
        });
}

function closeViewPermissionsModal() {
    document.getElementById('viewPermissionsModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>
