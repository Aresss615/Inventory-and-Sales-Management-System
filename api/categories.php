<?php

header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-With');

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

ob_start();

register_shutdown_function(function() {
    $error = error_get_last();
    $content = ob_get_clean();

    if ($error) {
        http_response_code(500);
        header('Content-Type: application/json');
        $msg = isset($error['message']) ? $error['message'] : 'Shutdown error';
        echo json_encode(['status' => 500, 'message' => 'Internal Server Error', 'error' => $msg]);
        return;
    }

    if (trim($content) !== '') {
        $decoded = json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['status' => 500, 'message' => 'Internal Server Error', 'debug' => substr($content, 0, 2000)]);
            return;
        } else {
            echo $content;
            return;
        }
    }
});

require __DIR__ . "/../config/auth.php";
require __DIR__ . "/../config/database.php";

if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
    exit;
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod == "GET") {
    getCategories();
} elseif ($requestMethod == "POST") {
    checkRole(['admin', 'manager']);
    createCategory();
} elseif ($requestMethod == "PUT") {
    checkRole(['admin', 'manager']);
    if (isset($_GET['id'])) {
        updateCategory($_GET['id']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => 'Category ID required']);
    }
} elseif ($requestMethod == "DELETE") {
    checkRole(['admin']);
    if (isset($_GET['id'])) {
        deleteCategory($_GET['id']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => 'Category ID required']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 405, 'message' => 'Method not allowed']);
}

function getCategories() {
    global $conn;
    $query = "SELECT c.*, COUNT(p.id) as product_count 
              FROM categories c 
              LEFT JOIN products p ON c.id = p.category_id 
              GROUP BY c.id 
              ORDER BY c.id ASC";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
            http_response_code(200);
            echo json_encode($categories);
        } else {
            http_response_code(200);
            echo json_encode([]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Internal Server Error']);
    }
}

function createCategory() {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['name'])) {
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => 'Category name is required']);
        return;
    }
    
    $name = mysqli_real_escape_string($conn, $data['name']);
    $description = isset($data['description']) ? mysqli_real_escape_string($conn, $data['description']) : '';
    
    $check = "SELECT * FROM categories WHERE name = '$name'";
    $check_result = mysqli_query($conn, $check);
    if (mysqli_num_rows($check_result) > 0) {
        http_response_code(409);
        echo json_encode(['status' => 409, 'message' => 'Category already exists']);
        return;
    }
    
    $query = "INSERT INTO categories (name, description) VALUES ('$name', '$description')";
    if (mysqli_query($conn, $query)) {
        http_response_code(201);
        echo json_encode([
            'status' => 201,
            'message' => 'Category created successfully',
            'id' => mysqli_insert_id($conn)
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Failed to create category']);
    }
}

function updateCategory($id) {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['name'])) {
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => 'Category name is required']);
        return;
    }
    
    $id = mysqli_real_escape_string($conn, $id);
    $name = mysqli_real_escape_string($conn, $data['name']);
    $description = isset($data['description']) ? mysqli_real_escape_string($conn, $data['description']) : '';
    
    $check = "SELECT * FROM categories WHERE id = '$id'";
    $check_result = mysqli_query($conn, $check);
    if (mysqli_num_rows($check_result) == 0) {
        http_response_code(404);
        echo json_encode(['status' => 404, 'message' => 'Category not found']);
        return;
    }
    
    $check_name = "SELECT * FROM categories WHERE name = '$name' AND id != '$id'";
    $check_name_result = mysqli_query($conn, $check_name);
    if (mysqli_num_rows($check_name_result) > 0) {
        http_response_code(409);
        echo json_encode(['status' => 409, 'message' => 'Category name already exists']);
        return;
    }
    
    $query = "UPDATE categories SET name = '$name', description = '$description' WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        http_response_code(200);
        echo json_encode(['status' => 200, 'message' => 'Category updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Failed to update category']);
    }
}

function deleteCategory($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    
    $check = "SELECT COUNT(*) as count FROM products WHERE category_id = '$id'";
    $check_result = mysqli_query($conn, $check);
    if (!$check_result) {
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Failed to verify category products']);
        return;
    }
    $row = mysqli_fetch_assoc($check_result);
    if (!$row) {
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Failed to read product count']);
        return;
    }
    if ($row['count'] > 0) {
        http_response_code(409);
        echo json_encode([
            'status' => 409,
            'message' => 'Cannot delete category with existing products'
        ]);
        return;
    }

    $checkSales = "SELECT COUNT(*) as count FROM sales WHERE category_id = '$id'";
    $checkSalesResult = mysqli_query($conn, $checkSales);
    if (!$checkSalesResult) {
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Failed to verify category references in sales']);
        return;
    }
    $salesRow = mysqli_fetch_assoc($checkSalesResult);
    $salesCount = $salesRow['count'] ?? 0;

    $force = isset($_GET['force']) && in_array(strtolower($_GET['force']), ['1', 'true', 'yes'], true);

    if ($salesCount > 0 && !$force) {
        http_response_code(409);
        echo json_encode([
            'status' => 409,
            'message' => 'Cannot delete category: referenced by sales records',
            'references' => ['sales' => (int)$salesCount]
        ]);
        return;
    }

    if ($salesCount > 0 && $force) {
        mysqli_begin_transaction($conn);
        $delSales = "DELETE FROM sales WHERE category_id = '$id'";
        if (!mysqli_query($conn, $delSales)) {
            mysqli_rollback($conn);
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Failed to delete related sales records']);
            return;
        }
    }
    
    $query = "DELETE FROM categories WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        if ($salesCount > 0 && $force) {
            mysqli_commit($conn);
        }
        http_response_code(200);
        echo json_encode(['status' => 200, 'message' => 'Category deleted successfully']);
    } else {
        if ($salesCount > 0 && $force) {
            mysqli_rollback($conn);
        }
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Failed to delete category']);
    }
}

?>
