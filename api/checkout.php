<?php

header('Content-Type: application/json');
session_start();
require __DIR__ . "/../config/auth.php";
require __DIR__ . "/../config/database.php";

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(['status' => 405, 'message' => 'Method Not Allowed']);
    exit;
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'message' => 'Invalid cart items']);
    exit;
}

$sold_by = $_SESSION['user_id'] ?? null;
$sale_date = date('Y-m-d');
$errors = [];
$success_count = 0;

mysqli_begin_transaction($conn);

try {
    foreach ($data['items'] as $item) {
        if (empty($item['product_id']) || empty($item['qty']) || !isset($item['price'])) {
            throw new Exception('Invalid item data');
        }
        
        $product_id = mysqli_real_escape_string($conn, $item['product_id']);
        $qty = mysqli_real_escape_string($conn, $item['qty']);
        $price = mysqli_real_escape_string($conn, $item['price']);
        
        $product_query = "SELECT p.name, p.category_id, p.stock FROM products p WHERE p.id = '$product_id'";
        $product_result = mysqli_query($conn, $product_query);
        
        if (!$product_result || mysqli_num_rows($product_result) == 0) {
            throw new Exception('Product not found: ID ' . $product_id);
        }
        
        $product = mysqli_fetch_assoc($product_result);
        
        if ($product['stock'] < $qty) {
            throw new Exception('Insufficient stock for: ' . $product['name']);
        }
        
        $product_name = mysqli_real_escape_string($conn, $product['name']);
        $category_id = $product['category_id'];
        $total = $qty * $price;
        
        $query = "INSERT INTO sales (product_id, product_name, qty, price, total, category_id, sale_date, sold_by) 
                  VALUES ('$product_id', '$product_name', '$qty', '$price', '$total', '$category_id', '$sale_date', " . 
                  ($sold_by ? "'$sold_by'" : "NULL") . ")";
        
        if (!mysqli_query($conn, $query)) {
            throw new Exception('Failed to create sale');
        }
        
        $update_stock = "UPDATE products SET stock = stock - $qty WHERE id = '$product_id'";
        if (!mysqli_query($conn, $update_stock)) {
            throw new Exception('Failed to update stock');
        }
        
        $success_count++;
    }
    
    mysqli_commit($conn);
    
    http_response_code(200);
    echo json_encode([
        'status' => 200,
        'message' => 'Sale completed successfully',
        'items_processed' => $success_count
    ]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'message' => $e->getMessage()
    ]);
}

?>
