<?php

require __DIR__ . "/../config/database.php";
require __DIR__ . "/../config/auth.php";

function getProducts()
{
    global $conn;
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              ORDER BY p.id ASC";
    $query_run = mysqli_query($conn, $query);
    
    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
            header("HTTP/1.0 200 OK");
            return json_encode($res);
        } else {
            $data = [
                'status' => 404,
                'message' => 'No products found',
            ];
            header("HTTP/1.0 404 Not Found");
            return json_encode($data);
        }
    } else {
        $data = [
            'status' => 500,
            'message' => 'Internal Server Error',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        return json_encode($data);
    }
}

function getProduct($id)
{
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.id = '$id'";
    $query_run = mysqli_query($conn, $query);
    
    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_assoc($query_run);
            header("HTTP/1.0 200 OK");
            return json_encode($res);
        } else {
            $data = [
                'status' => 404,
                'message' => 'Product not found',
            ];
            header("HTTP/1.0 404 Not Found");
            return json_encode($data);
        }
    } else {
        $data = [
            'status' => 500,
            'message' => 'Internal Server Error',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        return json_encode($data);
    }
}

function getProductByBarcode($barcode)
{
    global $conn;
    $barcode = mysqli_real_escape_string($conn, $barcode);
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.barcode = '$barcode'";
    $query_run = mysqli_query($conn, $query);
    
    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_assoc($query_run);
            header("HTTP/1.0 200 OK");
            return json_encode($res);
        } else {
            $data = [
                'status' => 404,
                'message' => 'Product not found',
            ];
            header("HTTP/1.0 404 Not Found");
            return json_encode($data);
        }
    } else {
        $data = [
            'status' => 500,
            'message' => 'Internal Server Error',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        return json_encode($data);
    }
}

function createProduct()
{
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['sku']) || empty($data['name']) || empty($data['barcode']) || 
        empty($data['category_id']) || !isset($data['stock']) || !isset($data['price']) || !isset($data['minStock'])) {
        $response = [
            'status' => 400,
            'message' => 'All fields required',
        ];
        header("HTTP/1.0 400 Bad Request");
        return json_encode($response);
    }
    
    $sku = mysqli_real_escape_string($conn, $data['sku']);
    $name = mysqli_real_escape_string($conn, $data['name']);
    $barcode = mysqli_real_escape_string($conn, $data['barcode']);
    $category_id = mysqli_real_escape_string($conn, $data['category_id']);
    $stock = mysqli_real_escape_string($conn, $data['stock']);
    $price = mysqli_real_escape_string($conn, $data['price']);
    $minStock = mysqli_real_escape_string($conn, $data['minStock']);
    $created_by = $_SESSION['user_id'] ?? null;
    
    $check = "SELECT * FROM products WHERE barcode = '$barcode'";
    $check_run = mysqli_query($conn, $check);
    if (mysqli_num_rows($check_run) > 0) {
        $response = [
            'status' => 409,
            'message' => 'Barcode already exists',
        ];
        header("HTTP/1.0 409 Conflict");
        return json_encode($response);
    }
    
    $query = "INSERT INTO products (sku, name, barcode, category_id, stock, price, minStock, created_by) 
              VALUES ('$sku', '$name', '$barcode', '$category_id', '$stock', '$price', '$minStock', " . 
              ($created_by ? "'$created_by'" : "NULL") . ")";
    $query_run = mysqli_query($conn, $query);
    
    if ($query_run) {
        $response = [
            'status' => 201,
            'message' => 'Product created successfully',
            'id' => mysqli_insert_id($conn)
        ];
        header("HTTP/1.0 201 Created");
        return json_encode($response);
    } else {
        $response = [
            'status' => 500,
            'message' => 'Internal Server Error',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        return json_encode($response);
    }
}

function updateProduct($id)
{
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['sku']) || empty($data['name']) || empty($data['barcode']) || 
        empty($data['category_id']) || !isset($data['stock']) || !isset($data['price']) || !isset($data['minStock'])) {
        $response = [
            'status' => 400,
            'message' => 'All fields required',
        ];
        header("HTTP/1.0 400 Bad Request");
        return json_encode($response);
    }
    
    $id = mysqli_real_escape_string($conn, $id);
    $sku = mysqli_real_escape_string($conn, $data['sku']);
    $name = mysqli_real_escape_string($conn, $data['name']);
    $barcode = mysqli_real_escape_string($conn, $data['barcode']);
    $category_id = mysqli_real_escape_string($conn, $data['category_id']);
    $stock = mysqli_real_escape_string($conn, $data['stock']);
    $price = mysqli_real_escape_string($conn, $data['price']);
    $minStock = mysqli_real_escape_string($conn, $data['minStock']);
    
    $check = "SELECT * FROM products WHERE barcode = '$barcode' AND id != '$id'";
    $check_run = mysqli_query($conn, $check);
    if (mysqli_num_rows($check_run) > 0) {
        $response = [
            'status' => 409,
            'message' => 'Barcode already exists',
        ];
        header("HTTP/1.0 409 Conflict");
        return json_encode($response);
    }
    
    $query = "UPDATE products SET sku='$sku', name='$name', barcode='$barcode', 
              category_id='$category_id', stock='$stock', price='$price', minStock='$minStock' 
              WHERE id='$id'";
    $query_run = mysqli_query($conn, $query);
    
    if ($query_run) {
        $response = [
            'status' => 200,
            'message' => 'Product updated successfully',
        ];
        header("HTTP/1.0 200 OK");
        return json_encode($response);
    } else {
        $response = [
            'status' => 500,
            'message' => 'Internal Server Error',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        return json_encode($response);
    }
}

function deleteProduct($id)
{
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $query = "DELETE FROM products WHERE id='$id'";
    $query_run = mysqli_query($conn, $query);
    
    if ($query_run) {
        $response = [
            'status' => 200,
            'message' => 'Product deleted successfully',
        ];
        header("HTTP/1.0 200 OK");
        return json_encode($response);
    } else {
        $response = [
            'status' => 500,
            'message' => 'Internal Server Error',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        return json_encode($response);
    }
}

function getSales()
{
    global $conn;
    $query = "SELECT s.*, c.name as category_name, u.username as sold_by_username 
              FROM sales s 
              LEFT JOIN categories c ON s.category_id = c.id 
              LEFT JOIN users u ON s.sold_by = u.id 
              ORDER BY s.sale_date DESC, s.id DESC";
    $query_run = mysqli_query($conn, $query);
    
    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
            header("HTTP/1.0 200 OK");
            return json_encode($res);
        } else {
            $data = [
                'status' => 404,
                'message' => 'No sales found',
            ];
            header("HTTP/1.0 404 Not Found");
            return json_encode($data);
        }
    } else {
        $data = [
            'status' => 500,
            'message' => 'Internal Server Error',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        return json_encode($data);
    }
}

function createSale()
{
    global $conn;
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
    
    if (empty($data['product_id']) || empty($data['qty']) || !isset($data['price'])) {
        $response = [
            'status' => 400,
            'message' => 'Product ID, quantity and price are required',
        ];
        header("HTTP/1.0 400 Bad Request");
        return json_encode($response);
    }
    
    $product_id = mysqli_real_escape_string($conn, $data['product_id']);
    $qty = mysqli_real_escape_string($conn, $data['qty']);
    $price = mysqli_real_escape_string($conn, $data['price']);
    $sale_date = isset($data['sale_date']) ? mysqli_real_escape_string($conn, $data['sale_date']) : date('Y-m-d');
    $sold_by = $_SESSION['user_id'] ?? null;
    
    $product_query = "SELECT p.name, p.category_id, p.stock FROM products p WHERE p.id = '$product_id'";
    $product_result = mysqli_query($conn, $product_query);
    
    if (!$product_result || mysqli_num_rows($product_result) == 0) {
        $response = [
            'status' => 404,
            'message' => 'Product not found',
        ];
        header("HTTP/1.0 404 Not Found");
        return json_encode($response);
    }
    
    $product = mysqli_fetch_assoc($product_result);
    
    if ($product['stock'] < $qty) {
        $response = [
            'status' => 400,
            'message' => 'Insufficient stock',
        ];
        header("HTTP/1.0 400 Bad Request");
        return json_encode($response);
    }
    
    $product_name = mysqli_real_escape_string($conn, $product['name']);
    $category_id = $product['category_id'];
    $total = $qty * $price;
    
    mysqli_begin_transaction($conn);
    
    try {
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
        
        mysqli_commit($conn);
        
        $response = [
            'status' => 201,
            'message' => 'Sale created successfully',
            'id' => mysqli_insert_id($conn)
        ];
        header("HTTP/1.0 201 Created");
        return json_encode($response);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $response = [
            'status' => 500,
            'message' => 'Internal Server Error: ' . $e->getMessage(),
        ];
        header("HTTP/1.0 500 Internal Server Error");
        return json_encode($response);
    }
}

?>
