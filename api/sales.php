<?php

header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');
header('Access-Control-Allow-Method: GET, POST');
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
    $sales = getSales();
    echo $sales;
} elseif ($requestMethod == "POST") {
    $result = createSale();
    echo $result;
} else {
    $response = [
        'status' => 405,
        'message' => 'Method Not Allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($response);
}

?>
