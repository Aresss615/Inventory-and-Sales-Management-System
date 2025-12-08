<?php
session_start();
require __DIR__ . "/../config/database.php";
require __DIR__ . "/../config/auth.php";

checkLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'save') {
    checkRole(['admin', 'manager', 'staff']);
    
    $id = isset($_POST['id']) && !empty($_POST['id']) ? $_POST['id'] : null;
    $sku = mysqli_real_escape_string($conn, $_POST['sku']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $barcode = mysqli_real_escape_string($conn, $_POST['barcode']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category']);
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $minStock = mysqli_real_escape_string($conn, $_POST['minStock']);
    $created_by = $_SESSION['user_id'];
    
    if ($id) {
        $check = "SELECT * FROM products WHERE barcode = '$barcode' AND id != '$id'";
        $check_run = mysqli_query($conn, $check);
        if (mysqli_num_rows($check_run) > 0) {
            header('Location: index.php?page=inventory&error=barcode_exists');
            exit;
        }
        
        $query = "UPDATE products SET sku='$sku', name='$name', barcode='$barcode', 
                  category_id='$category_id', stock='$stock', price='$price', minStock='$minStock' 
                  WHERE id='$id'";
        mysqli_query($conn, $query);
        header('Location: index.php?page=inventory&success=updated');
    } else {
        $check = "SELECT * FROM products WHERE barcode = '$barcode'";
        $check_run = mysqli_query($conn, $check);
        if (mysqli_num_rows($check_run) > 0) {
            header('Location: index.php?page=inventory&error=barcode_exists');
            exit;
        }
        
        $query = "INSERT INTO products (sku, name, barcode, category_id, stock, price, minStock, created_by) 
                  VALUES ('$sku', '$name', '$barcode', '$category_id', '$stock', '$price', '$minStock', '$created_by')";
        mysqli_query($conn, $query);
        header('Location: index.php?page=inventory&success=created');
    }
} elseif ($action === 'delete') {
    checkRole(['admin', 'manager']);
    
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $query = "DELETE FROM products WHERE id='$id'";
    mysqli_query($conn, $query);
    header('Location: index.php?page=inventory&success=deleted');
}

exit;
?>
