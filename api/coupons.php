<?php

header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');
header('Access-Control-Allow-Method: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-With');

session_start();
require __DIR__ . "/../config/auth.php";
require __DIR__ . "/../config/database.php";

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
    exit;
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

    if ($requestMethod == "GET") {
    if (!hasPermission('coupons_view')) {
        http_response_code(403);
        echo json_encode(['status' => 403, 'message' => 'Forbidden']);
        exit;
    }
    if (isset($_GET['code'])) {
        $code = mysqli_real_escape_string($conn, $_GET['code']);
        $query = "SELECT * FROM coupons WHERE code = '$code' AND is_active = 1";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $coupon = mysqli_fetch_assoc($result);
            $now = date('Y-m-d H:i:s');
            
            if ($now >= $coupon['valid_from'] && ($coupon['valid_until'] === null || $now <= $coupon['valid_until'])) {
                http_response_code(200);
                echo json_encode(['status' => 200, 'data' => $coupon]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 404, 'message' => 'Coupon expired']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['status' => 404, 'message' => 'Coupon not found']);
        }
    } else {
        $query = "SELECT * FROM coupons WHERE is_active = 1 ORDER BY created_at DESC";
        $result = mysqli_query($conn, $query);
        
        $coupons = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $coupons[] = $row;
            }
        }
        
        http_response_code(200);
        echo json_encode(['status' => 200, 'data' => $coupons]);
    }
} elseif ($requestMethod == "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'create' && !hasPermission('coupons_create')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }
    if ($action == 'update' && !hasPermission('coupons_edit')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }
    if ($action == 'delete' && !hasPermission('coupons_delete')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }

    if ($action == 'create') {
        $code = strtoupper(mysqli_real_escape_string($conn, $_POST['code']));
        $discount_type = mysqli_real_escape_string($conn, $_POST['discount_type']);
        $discount_value = floatval($_POST['discount_value']);
        $min_purchase = floatval($_POST['min_purchase'] ?? 0);
        $max_discount = !empty($_POST['max_discount']) ? floatval($_POST['max_discount']) : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $valid_from = mysqli_real_escape_string($conn, $_POST['valid_from']);
        $valid_until = !empty($_POST['valid_until']) ? mysqli_real_escape_string($conn, $_POST['valid_until']) : null;
        
        $check_query = "SELECT id FROM coupons WHERE code = '$code'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Coupon code already exists']);
            exit;
        }
        
        $max_discount_sql = $max_discount !== null ? $max_discount : 'NULL';
        $valid_until_sql = $valid_until !== null ? "'$valid_until'" : 'NULL';
        
        $query = "INSERT INTO coupons (code, discount_type, discount_value, min_purchase, max_discount, is_active, valid_from, valid_until) 
                  VALUES ('$code', '$discount_type', $discount_value, $min_purchase, $max_discount_sql, $is_active, '$valid_from', $valid_until_sql)";
        
        if (mysqli_query($conn, $query)) {
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Coupon created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create coupon: ' . mysqli_error($conn)]);
        }
    } elseif ($action == 'update') {
        $id = intval($_POST['id']);
        $code = strtoupper(mysqli_real_escape_string($conn, $_POST['code']));
        $discount_type = mysqli_real_escape_string($conn, $_POST['discount_type']);
        $discount_value = floatval($_POST['discount_value']);
        $min_purchase = floatval($_POST['min_purchase'] ?? 0);
        $max_discount = !empty($_POST['max_discount']) ? floatval($_POST['max_discount']) : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $valid_from = mysqli_real_escape_string($conn, $_POST['valid_from']);
        $valid_until = !empty($_POST['valid_until']) ? mysqli_real_escape_string($conn, $_POST['valid_until']) : null;
        
        $check_query = "SELECT id FROM coupons WHERE code = '$code' AND id != $id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Coupon code already exists']);
            exit;
        }
        
        $max_discount_sql = $max_discount !== null ? $max_discount : 'NULL';
        $valid_until_sql = $valid_until !== null ? "'$valid_until'" : 'NULL';
        
        $query = "UPDATE coupons SET 
                  code = '$code', 
                  discount_type = '$discount_type', 
                  discount_value = $discount_value, 
                  min_purchase = $min_purchase, 
                  max_discount = $max_discount_sql, 
                  is_active = $is_active, 
                  valid_from = '$valid_from', 
                  valid_until = $valid_until_sql 
                  WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Coupon updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update coupon: ' . mysqli_error($conn)]);
        }
    } elseif ($action == 'delete') {
        $id = intval($_POST['id']);
        
        $query = "DELETE FROM coupons WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Coupon deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete coupon: ' . mysqli_error($conn)]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    $response = [
        'status' => 405,
        'message' => 'Method Not Allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($response);
}

?>
