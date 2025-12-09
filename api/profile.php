<?php
session_start();
require __DIR__ . "/../config/database.php";
require __DIR__ . "/../config/auth.php";

checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'update_profile';
    $user_id = $_SESSION['user_id'];
    
    if ($action === 'change_password') {
        $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
        $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
        $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            header('Location: index.php?page=profile&error=all_fields_required');
            exit;
        }
        
        if ($new_password !== $confirm_password) {
            header('Location: index.php?page=profile&error=passwords_dont_match');
            exit;
        }
        
        if (strlen($new_password) < 6) {
            header('Location: index.php?page=profile&error=password_too_short');
            exit;
        }
        
        $query = "SELECT password FROM users WHERE id = '$user_id'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            if (password_verify($current_password, $user['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET password = '$hashed_password', updated_at = NOW() WHERE id = '$user_id'";
                
                if (mysqli_query($conn, $update_query)) {
                    header('Location: index.php?page=profile&success=password_changed');
                } else {
                    header('Location: index.php?page=profile&error=update_failed');
                }
            } else {
                header('Location: index.php?page=profile&error=current_password_incorrect');
            }
        } else {
            header('Location: index.php?page=profile&error=user_not_found');
        }
        exit;
    } elseif ($action === 'update_profile') {
        $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        if (empty($full_name) || empty($email)) {
            header('Location: index.php?page=profile&error=all_fields_required');
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: index.php?page=profile&error=invalid_email');
            exit;
        }
        
        $check_query = "SELECT id FROM users WHERE email = '$email' AND id != '$user_id'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            header('Location: index.php?page=profile&error=email_exists');
            exit;
        }
        
        $update_query = "UPDATE users SET full_name = '$full_name', email = '$email', updated_at = NOW() WHERE id = '$user_id'";
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['user_full_name'] = $full_name;
            header('Location: index.php?page=profile&success=profile_updated');
        } else {
            header('Location: index.php?page=profile&error=update_failed');
        }
        exit;
    }
}

header('Location: index.php?page=profile');
exit;
?>
