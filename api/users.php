<?php
session_start();
require __DIR__ . "/../config/database.php";
require __DIR__ . "/../config/auth.php";

checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'save';
    
    if ($action === 'delete') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        
        if ($id == $_SESSION['user_id']) {
            header('Location: index.php?page=users&error=cannot_delete_self');
            exit;
        }
        
        $query = "DELETE FROM users WHERE id = '$id'";
        if (mysqli_query($conn, $query)) {
            header('Location: index.php?page=users&success=deleted');
        } else {
            header('Location: index.php?page=users&error=delete_failed');
        }
        exit;
    } else {
        $id = isset($_POST['id']) && !empty($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : null;
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : null;
        
        if ($id) {
            $query = "UPDATE users SET username='$username', full_name='$full_name', 
                      email='$email', role='$role', is_active='$is_active'";
            
            if ($password) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query .= ", password='$hashed_password'";
            }
            
            $query .= " WHERE id='$id'";
            
            if (mysqli_query($conn, $query)) {
                header('Location: index.php?page=users&success=updated');
            } else {
                header('Location: index.php?page=users&error=update_failed');
            }
        } else {
            if (!$password) {
                header('Location: index.php?page=users&error=password_required');
                exit;
            }
            
            $check = "SELECT * FROM users WHERE username='$username' OR email='$email'";
            $check_result = mysqli_query($conn, $check);
            if (mysqli_num_rows($check_result) > 0) {
                header('Location: index.php?page=users&error=user_exists');
                exit;
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, password, email, full_name, role, is_active) 
                      VALUES ('$username', '$hashed_password', '$email', '$full_name', '$role', '$is_active')";
            
            if (mysqli_query($conn, $query)) {
                header('Location: index.php?page=users&success=created');
            } else {
                header('Location: index.php?page=users&error=create_failed');
            }
        }
        exit;
    }
}

header('Location: index.php?page=users');
exit;
?>
