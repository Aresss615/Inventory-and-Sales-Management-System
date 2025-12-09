<?php
session_start();
require __DIR__ . "/../config/database.php";
require __DIR__ . "/../config/auth.php";
require __DIR__ . "/../config/config.php";

$redirect_base = rtrim(defined('BASE_URL') ? BASE_URL : '', '/');
$app_index = $redirect_base === '' ? '/index.php' : $redirect_base . '/index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'save';
    
    if ($action === 'delete') {
        checkPermission('users_delete');
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        
        if ($id == $_SESSION['user_id']) {
            header('Location: ' . $app_index . '?page=users&error=cannot_delete_self');
            exit;
        }
        
        $query = "DELETE FROM users WHERE id = '$id'";
            if (mysqli_query($conn, $query)) {
                header('Location: ' . $app_index . '?page=users&success=deleted');
            } else {
                header('Location: ' . $app_index . '?page=users&error=delete_failed');
            }
        exit;
    } else {
        $id = isset($_POST['id']) && !empty($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : null;
        
        if ($id) {
            checkPermission('users_edit');
        } else {
            checkPermission('users_create');
        }
        
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $role_id = mysqli_real_escape_string($conn, $_POST['role_id']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : null;
        
        if ($id) {
            $query = "UPDATE users SET username='$username', full_name='$full_name', 
                      email='$email', role_id='$role_id', is_active='$is_active'";
            
            if ($password) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query .= ", password='$hashed_password'";
            }
            
            $query .= " WHERE id='$id'";
            
            if (mysqli_query($conn, $query)) {
                header('Location: ' . $app_index . '?page=users&success=updated');
            } else {
                header('Location: ' . $app_index . '?page=users&error=update_failed');
            }
        } else {
            if (!$password) {
                header('Location: ' . $app_index . '?page=users&error=password_required');
                exit;
            }
            
            $check = "SELECT * FROM users WHERE username='$username' OR email='$email'";
            $check_result = mysqli_query($conn, $check);
            if (mysqli_num_rows($check_result) > 0) {
                header('Location: index.php?page=users&error=user_exists');
                exit;
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $created_by = $_SESSION['user_id'];
            $query = "INSERT INTO users (username, password, email, full_name, role_id, is_active, created_by) 
                      VALUES ('$username', '$hashed_password', '$email', '$full_name', '$role_id', '$is_active', '$created_by')";
            
                if (mysqli_query($conn, $query)) {
                header('Location: ' . $app_index . '?page=users&success=created');
            } else {
                header('Location: ' . $app_index . '?page=users&error=create_failed');
            }
        }
        exit;
    }
}

header('Location: ' . $app_index . '?page=users');
exit;
?>
