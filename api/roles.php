<?php
session_start();
require __DIR__ . "/../config/database.php";
require __DIR__ . "/../config/auth.php";

checkPermission('roles_view');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'save';
    
    if ($action === 'delete') {
        checkPermission('roles_delete');
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        
        $check_query = "SELECT is_system FROM roles WHERE id = '$id'";
        $check_result = mysqli_query($conn, $check_query);
        $role = mysqli_fetch_assoc($check_result);
        
        if ($role['is_system']) {
            header('Location: index.php?page=roles&error=cannot_delete_system_role');
            exit;
        }
        
        $user_check = "SELECT COUNT(*) as user_count FROM users WHERE role_id = '$id'";
        $user_result = mysqli_query($conn, $user_check);
        $user_data = mysqli_fetch_assoc($user_result);
        
        if ($user_data['user_count'] > 0) {
            header('Location: index.php?page=roles&error=role_has_users');
            exit;
        }
        
        $query = "DELETE FROM roles WHERE id = '$id'";
        if (mysqli_query($conn, $query)) {
            header('Location: index.php?page=roles&success=deleted');
        } else {
            header('Location: index.php?page=roles&error=delete_failed');
        }
        exit;
        
    } elseif ($action === 'save') {
        checkPermission('roles_create');
        
        $id = isset($_POST['id']) && !empty($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : null;
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $display_name = mysqli_real_escape_string($conn, $_POST['display_name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
        
        if ($id) {
            checkPermission('roles_edit');
            
            $check_query = "SELECT is_system FROM roles WHERE id = '$id'";
            $check_result = mysqli_query($conn, $check_query);
            $role = mysqli_fetch_assoc($check_result);
            
            if ($role['is_system']) {
                header('Location: index.php?page=roles&error=cannot_edit_system_role');
                exit;
            }
            
            $query = "UPDATE roles SET name='$name', display_name='$display_name', 
                      description='$description' WHERE id='$id'";
            
            if (mysqli_query($conn, $query)) {
                mysqli_query($conn, "DELETE FROM role_permissions WHERE role_id = '$id'");
                foreach ($permissions as $permission_id) {
                    $permission_id = mysqli_real_escape_string($conn, $permission_id);
                    mysqli_query($conn, "INSERT INTO role_permissions (role_id, permission_id) VALUES ('$id', '$permission_id')");
                }
                header('Location: index.php?page=roles&success=updated');
            } else {
                header('Location: index.php?page=roles&error=update_failed');
            }
        } else {
            $query = "INSERT INTO roles (name, display_name, description, is_system) 
                      VALUES ('$name', '$display_name', '$description', 0)";
            
            if (mysqli_query($conn, $query)) {
                $role_id = mysqli_insert_id($conn);
                
                foreach ($permissions as $permission_id) {
                    $permission_id = mysqli_real_escape_string($conn, $permission_id);
                    mysqli_query($conn, "INSERT INTO role_permissions (role_id, permission_id) VALUES ('$role_id', '$permission_id')");
                }
                header('Location: index.php?page=roles&success=created');
            } else {
                header('Location: index.php?page=roles&error=create_failed');
            }
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_permissions') {
    $role_id = isset($_GET['role_id']) ? mysqli_real_escape_string($conn, $_GET['role_id']) : null;
    
    if ($role_id) {
        $query = "SELECT permission_id FROM role_permissions WHERE role_id = '$role_id'";
        $result = mysqli_query($conn, $query);
        $permissions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $permissions[] = $row['permission_id'];
        }
        header('Content-Type: application/json');
        echo json_encode($permissions);
        exit;
    }
}
?>
