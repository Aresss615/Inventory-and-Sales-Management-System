<?php

header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');
header('Access-Control-Allow-Method: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-With');

require __DIR__ . "/../config/auth.php";
require __DIR__ . "/../config/database.php";

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
    $row = mysqli_fetch_assoc($check_result);
    
    if ($row['count'] > 0) {
        http_response_code(409);
        echo json_encode([
            'status' => 409,
            'message' => 'Cannot delete category with existing products'
        ]);
        return;
    }
    
    $query = "DELETE FROM categories WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        http_response_code(200);
        echo json_encode(['status' => 200, 'message' => 'Category deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Failed to delete category']);
    }
}

?>
