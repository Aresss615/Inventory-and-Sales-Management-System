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

$discount_type = $data['discount_type'] ?? null;
$coupon_code = $data['coupon_code'] ?? null;
$payment_method = $data['payment_method'] ?? 'cash';
$payment_received = floatval($data['payment_received'] ?? 0);

mysqli_begin_transaction($conn);

try {
    $transaction_number = 'TXN-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    
    $subtotal = 0;
    $total_items = 0;
    foreach ($data['items'] as $item) {
        $subtotal += $item['qty'] * $item['price'];
        $total_items += $item['qty'];
    }
    
    $discount_amount = 0;
    if ($discount_type === 'PWD' || $discount_type === 'SENIOR') {
        $discount_amount = $subtotal * 0.10;
    }
    
    $coupon_discount = 0;
    if ($coupon_code) {
        $coupon_query = "SELECT * FROM coupons WHERE code = '" . mysqli_real_escape_string($conn, $coupon_code) . "' AND is_active = 1";
        $coupon_result = mysqli_query($conn, $coupon_query);
        
        if ($coupon_result && mysqli_num_rows($coupon_result) > 0) {
            $coupon = mysqli_fetch_assoc($coupon_result);
            $now = date('Y-m-d H:i:s');
            
            if ($now >= $coupon['valid_from'] && ($coupon['valid_until'] === null || $now <= $coupon['valid_until'])) {
                if ($subtotal >= $coupon['min_purchase']) {
                    if ($coupon['discount_type'] === 'percentage') {
                        $coupon_discount = ($subtotal - $discount_amount) * ($coupon['discount_value'] / 100);
                        if ($coupon['max_discount'] && $coupon_discount > $coupon['max_discount']) {
                            $coupon_discount = $coupon['max_discount'];
                        }
                    } else {
                        $coupon_discount = $coupon['discount_value'];
                    }
                }
            }
        }
    }
    
    $total_amount = $subtotal - $discount_amount - $coupon_discount;
    
    $card_fee = 0;
    if ($payment_method === 'card') {
        $card_fee = $total_amount * 0.01;
    }
    
    $final_amount = $total_amount + $card_fee;
    $change_amount = $payment_received - $final_amount;
    
    $trans_query = "INSERT INTO transactions (transaction_number, subtotal, discount_type, discount_amount, coupon_code, coupon_discount, total_amount, total_items, payment_method, card_fee, final_amount, payment_received, change_amount, sold_by) 
                    VALUES ('$transaction_number', '$subtotal', " . 
                    ($discount_type ? "'$discount_type'" : "NULL") . ", '$discount_amount', " .
                    ($coupon_code ? "'" . mysqli_real_escape_string($conn, $coupon_code) . "'" : "NULL") . ", '$coupon_discount', '$total_amount', '$total_items', '$payment_method', '$card_fee', '$final_amount', '$payment_received', '$change_amount', " . 
                    ($sold_by ? "'$sold_by'" : "NULL") . ")";
    
    if (!mysqli_query($conn, $trans_query)) {
        throw new Exception('Failed to create transaction record');
    }
    
    $transaction_id = mysqli_insert_id($conn);
    
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
        
        $query = "INSERT INTO sales (transaction_id, product_id, product_name, qty, price, total, category_id, sale_date, sold_by) 
                  VALUES ('$transaction_id', '$product_id', '$product_name', '$qty', '$price', '$total', '$category_id', '$sale_date', " . 
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
    
    $_SESSION['notification'] = [
        'message' => 'Sale completed successfully! Transaction: ' . $transaction_number . ' - ' . $success_count . ' item(s) processed.',
        'type' => 'success'
    ];
    
    http_response_code(200);
    echo json_encode([
        'status' => 200,
        'message' => 'Sale completed successfully',
        'transaction_number' => $transaction_number,
        'transaction_id' => $transaction_id,
        'items_processed' => $success_count,
        'subtotal' => $subtotal,
        'discount_amount' => $discount_amount,
        'coupon_discount' => $coupon_discount,
        'total_amount' => $total_amount,
        'card_fee' => $card_fee,
        'final_amount' => $final_amount,
        'change_amount' => $change_amount
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
