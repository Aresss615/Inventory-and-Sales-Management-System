<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . "/database.php";

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function checkRole($allowed_roles = []) {
    checkLogin();
    if (!empty($allowed_roles) && !in_array($_SESSION['user_role'], $allowed_roles)) {
        header('Location: index.php?error=unauthorized');
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    global $conn;
    $user_id = $_SESSION['user_id'];
    $query = "SELECT id, username, email, full_name, role, is_active FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function login($username, $password) {
    global $conn;
    $username = mysqli_real_escape_string($conn, $username);
    
    $query = "SELECT * FROM users WHERE username = '$username' AND is_active = 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            return true;
        }
    }
    return false;
}

function logout() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

?>
