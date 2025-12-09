<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . "/database.php";

function isApiRequest() {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (strpos($uri, '/api/') !== false) return true;
    if (stripos($accept, 'application/json') !== false) return true;
    return false;
}

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        if (isApiRequest()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
            exit;
        }
        header('Location: login.php');
        exit;
    }
}

function checkRole($allowed_roles = []) {
    checkLogin();
    if (!empty($allowed_roles) && !in_array($_SESSION['user_role'], $allowed_roles)) {
        if (isApiRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 403, 'message' => 'Forbidden']);
            exit;
        }
        header('Location: index.php?error=unauthorized');
        exit;
    }
}

function checkPermission($permission_name) {
    checkLogin();
    if (!hasPermission($permission_name)) {
        if (isApiRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 403, 'message' => 'Forbidden']);
            exit;
        }
        header('Location: index.php?error=unauthorized');
        exit;
    }
}

function hasPermission($permission_name) {
    if (!isLoggedIn()) {
        return false;
    }
    global $conn;
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT COUNT(*) as has_perm FROM users u
              JOIN role_permissions rp ON u.role_id = rp.role_id
              JOIN permissions p ON rp.permission_id = p.id
              WHERE u.id = '$user_id' AND p.name = '$permission_name'";
    
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['has_perm'] > 0;
    }
    return false;
}

function getUserPermissions($user_id = null) {
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    if (!$user_id) {
        return [];
    }
    
    global $conn;
    $query = "SELECT p.name, p.display_name, p.module FROM users u
              JOIN role_permissions rp ON u.role_id = rp.role_id
              JOIN permissions p ON rp.permission_id = p.id
              WHERE u.id = '$user_id'
              ORDER BY p.module, p.display_name";
    
    $result = mysqli_query($conn, $query);
    $permissions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $permissions[] = $row;
    }
    return $permissions;
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
    $query = "SELECT u.id, u.username, u.email, u.full_name, u.is_active, u.created_by,
              r.id as role_id, r.name as role, r.display_name as role_display_name
              FROM users u
              JOIN roles r ON u.role_id = r.id
              WHERE u.id = '$user_id'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function login($username, $password) {
    global $conn;
    $username = mysqli_real_escape_string($conn, $username);
    
    $query = "SELECT u.*, r.name as role FROM users u 
              JOIN roles r ON u.role_id = r.id 
              WHERE u.username = '$username' AND u.is_active = 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['role_id'] = $user['role_id'];
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

function canViewUsers() {
    return hasPermission('users_view');
}

function canViewRoles() {
    return hasPermission('roles_view');
}

function canViewCategories() {
    return hasPermission('categories_view');
}

function canViewProducts() {
    return hasPermission('products_view');
}

function canViewSales() {
    return hasPermission('sales_view');
}

function canViewReports() {
    return hasPermission('reports_view');
}

function canViewInventory() {
    return hasPermission('inventory_view');
}

function canViewDashboard() {
    return hasPermission('dashboard_view');
}

function canViewCoupons() {
    return hasPermission('coupons_view');
}

function canCreateCoupons() {
    return hasPermission('coupons_create');
}

function canEditCoupons() {
    return hasPermission('coupons_edit');
}

function canDeleteCoupons() {
    return hasPermission('coupons_delete');
}

?>
