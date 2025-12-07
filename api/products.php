<?php

header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');
header('Access-Control-Allow-Method: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-With');

session_start();
require __DIR__ . "/../config/auth.php";
require __DIR__ . "/../includes/functions.php";

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
    exit;
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod == "GET") {
    if (isset($_GET['id'])) {
        $product = getProduct($_GET['id']);
        echo $product;
    } elseif (isset($_GET['barcode'])) {
        $product = getProductByBarcode($_GET['barcode']);
        echo $product;
    } else {
        $products = getProducts();
        echo $products;
    }
} elseif ($requestMethod == "POST") {
    checkRole(['admin', 'manager', 'staff']);
    $result = createProduct();
    echo $result;
} elseif ($requestMethod == "PUT") {
    checkRole(['admin', 'manager', 'staff']);
    if (isset($_GET['id'])) {
        $result = updateProduct($_GET['id']);
        echo $result;
    } else {
        $response = [
            'status' => 400,
            'message' => 'Product ID required',
        ];
        header("HTTP/1.0 400 Bad Request");
        echo json_encode($response);
    }
} elseif ($requestMethod == "DELETE") {
    checkRole(['admin', 'manager']);
    if (isset($_GET['id'])) {
        $result = deleteProduct($_GET['id']);
        echo $result;
    } else {
        $response = [
            'status' => 400,
            'message' => 'Product ID required',
        ];
        header("HTTP/1.0 400 Bad Request");
        echo json_encode($response);
    }
} else {
    $response = [
        'status' => 405,
        'message' => 'Method not allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($response);
}

?>
