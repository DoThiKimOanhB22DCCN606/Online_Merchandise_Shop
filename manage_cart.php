<?php
session_start();

// Ensure all expected POST variables are set before using them to avoid undefined index notices.
$product_id = $_POST['product_id'] ?? null;
$product_name = $_POST['product_name'] ?? null; // This seems to be product_desc from products table
$product_price = $_POST['product_price'] ?? null;
$product_category = $_POST['product_category'] ?? null; // This seems to be category_id
$product_qty = $_POST['product_qty'] ?? 1; // Default to 1 if not set (e.g., for Buy Now)
$product_img = $_POST['product_img'] ?? null;
$buy_now_redirect = isset($_POST['buy_now_redirect']) && $_POST['buy_now_redirect'] === 'true';

if (isset($_POST['add_to_cart']) || $buy_now_redirect) { // Process if it's add_to_cart or buy_now

    if ($product_id && $product_name && $product_price && $product_category && $product_img) {
        if (isset($_SESSION['mycart'])) {
            $item_id_column = array_column($_SESSION['mycart'], 'product_id');
            
            // Check if the product is already in the cart
            if (in_array($product_id, $item_id_column)) {
                
            } else {
                // Product not in cart, add it
                $count_cart = count($_SESSION['mycart']);
                $_SESSION['mycart'][$count_cart] = array(
                    'name' => $product_name,
                    'price' => $product_price,
                    'product_id' => $product_id,
                    'category' => $product_category,
                    'product_qty' => (int)$product_qty, // Ensure quantity is an integer
                    'product_img' => $product_img
                );
            }
        } else {
            // Cart is empty, add the first product
            $_SESSION['mycart'][0] = array(
                'name' => $product_name,
                'price' => $product_price,
                'product_id' => $product_id,
                'category' => $product_category,
                'product_qty' => (int)$product_qty,
                'product_img' => $product_img
            );
        }

        // Redirect based on the action
        if ($buy_now_redirect) {
            header('Location: cart.php');
            exit();
        } else {
            // Original behavior: redirect back to product detail page
            // Construct the URL carefully, ensuring product_id and product_category are valid
            $redirect_url = 'viewdetail.php?id=' . urlencode($product_id) . '&category=' . urlencode($product_category);
            // You might want to add a success message to the session here to display on viewdetail.php
            $_SESSION['message'] = "Product added to cart!";
            header('Location: ' . $redirect_url);
            exit();
        }

    } else {
        // Handle missing product data - redirect back or show an error
        $_SESSION['error_message'] = "Could not add product to cart. Missing information.";
        // Redirect to a safe page, like homepage or previous page if known
        header('Location: index.php'); 
        exit();
    }
} else {
    // If not an add_to_cart or buy_now action, redirect to homepage or show error
    $_SESSION['error_message'] = "Invalid action.";
    header('Location: index.php');
    exit();
}
?>
