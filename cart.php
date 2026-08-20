<?php 

// --- Adjust Error Reporting ---
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE); //
ini_set('display_errors', 1); //
ini_set('log_errors', 1); //

if (session_status() == PHP_SESSION_NONE) { //
    if (!session_start()) { //
        error_log("Cart Page - FAILED TO START SESSION!"); //
    }
}

// --- Handle Cart Update (Quantity) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) { //
    error_log("Cart Page - Update Cart POST received: " . print_r($_POST, true)); //

    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) { //
        foreach ($_POST['quantities'] as $key => $new_quantity) { //
            if (isset($_SESSION['mycart']) && is_array($_SESSION['mycart']) && isset($_SESSION['mycart'][$key])) { //
                $new_quantity = filter_var($new_quantity, FILTER_VALIDATE_INT); //

                if ($new_quantity !== false && $new_quantity >= 0) { //
                    if ($new_quantity == 0) { //
                        unset($_SESSION['mycart'][$key]); //
                        error_log("Cart Page - Item removed (key: {$key}) due to quantity 0."); //
                    } else {
                        $_SESSION['mycart'][$key]['product_qty'] = $new_quantity; //
                        error_log("Cart Page - Item quantity updated (key: {$key}) to {$new_quantity}."); //
                    }
                } else {
                     error_log("Cart Page - Invalid quantity value received for key {$key}: " . (isset($_POST['quantities'][$key]) ? $_POST['quantities'][$key] : 'N/A')); //
                }
            } else {
                 error_log("Cart Page - Invalid key received in quantities update: " . $key); //
            }
        }
         if (isset($_SESSION['mycart']) && is_array($_SESSION['mycart'])) { //
             $_SESSION['mycart'] = array_values($_SESSION['mycart']); //
         } else {
             $_SESSION['mycart'] = []; //
         }
    } else {
         error_log("Cart Page - Update Cart POST received but 'quantities' array is missing or not an array."); //
    }
    header('Location: cart.php'); //
    exit; //
}
// --- End Handle Cart Update ---

// --- Handle Proceed to Checkout (Selected Items) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceed_to_checkout'])) {
    $selected_items_cart = [];
    $selected_grand_total = 0;
    $currency_code = isset($_SESSION['mycart'][0]['currency_code']) ? $_SESSION['mycart'][0]['currency_code'] : "USD"; // Get currency from an item or default

    if (isset($_POST['selected_items']) && is_array($_POST['selected_items']) && isset($_SESSION['mycart']) && is_array($_SESSION['mycart'])) {
        foreach ($_POST['selected_items'] as $selected_key) {
            // Validate selected_key (ensure it's a valid integer key from the original cart)
            $selected_key = filter_var($selected_key, FILTER_VALIDATE_INT);
            if ($selected_key !== false && isset($_SESSION['mycart'][$selected_key])) {
                $item = $_SESSION['mycart'][$selected_key];
                // Ensure quantity is also submitted for selected items (or use existing session quantity)
                $quantity = isset($_POST['quantities'][$selected_key]) ? filter_var($_POST['quantities'][$selected_key], FILTER_VALIDATE_INT) : $item['product_qty'];
                if ($quantity !== false && $quantity > 0) {
                    $item['product_qty'] = $quantity; // Update quantity if it was part of the form
                    $selected_items_cart[] = $item;
                    $price = isset($item['price']) ? filter_var($item['price'], FILTER_VALIDATE_FLOAT) : 0; //
                    if ($price !== false) { //
                        $selected_grand_total += $price * $quantity;
                    }
                }
            }
        }
    }

    if (!empty($selected_items_cart)) {
        $_SESSION['checkout_items'] = $selected_items_cart; // Store only selected items for checkout
        $_SESSION['cart_total'] = $selected_grand_total; // This will now be the total of selected items
        $_SESSION['currency_code'] = $currency_code; // Ensure currency is set
        header('Location: checkout.php');
        exit;
    } else {
        // No items selected, or error
        // Optionally, set an error message to display on cart.php
        $_SESSION['cart_message'] = "Please select items to proceed to checkout.";
        header('Location: cart.php');
        exit;
    }
}
// --- End Handle Proceed to Checkout ---


// Include header navigation AFTER session start and potential redirects
include_once('./includes/headerNav.php'); //
?>

<div class="overlay" data-overlay></div>
<header>
    <?php require_once './includes/desktopnav.php' ?> <?php // ?>
    <?php require_once './includes/mobilenav.php'; ?> <?php // ?>

    <style>
        /* --- Keep all your existing CSS styles here --- */
        :root{
            --main-maroon: #CE5959; /* */
            --deep-maroon: #89375F; /* */
            --bittersweet: #ff6961; /* */
        }
        table {
            width: 85%; margin: 20px auto; border-collapse: collapse; box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* */
        }
        th, td {
            border: 1px solid #ddd; padding: 12px 15px; text-align: center; vertical-align: middle; /* */
        }
        th {
            background-color: var(--main-maroon); color: white; font-weight: bold; /* */
        }
        tr:nth-child(even) {
            background-color: #f2f2f2; /* */
        }
        td img.cart-product-image {
            max-width: 80px; height: auto; display: block; margin-left: auto; margin-right: auto; border-radius: 4px; /* */
        }
        td input.quantity-input {
            width: 60px; /* Adjust width as needed */ /* */
            padding: 5px 8px; /* */
            text-align: center; /* */
            border: 1px solid #ccc; /* */
            border-radius: 4px; /* */
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.1); /* */
        }
        td input[type="checkbox"].item-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        th input[type="checkbox"]#select-all-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .cart-total-section {
            width: 85%; margin: 20px auto; text-align: right; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; /* */
        }
        .cart-total-section p {
            font-size: 1.2em; font-weight: bold; color: var(--main-maroon); margin: 0; /* */
        }
        .cart-actions { /* Container for update button */ /* */
             width: 85%; /* */
             margin: 15px auto; /* */
             /* text-align: right; Align button to the right */ /* */
             display: flex; /* Use flexbox for alignment */
             justify-content: space-between; /* Space out buttons */
             align-items: center;
        }
        .update-cart-btn, .checkout-btn { /* Added .checkout-btn */
            padding: 10px 20px; /* */
            background-color: var(--main-maroon); /* */
            color: white; /* */
            border: none; /* */
            border-radius: 5px; /* */
            cursor: pointer; /* */
            font-size: 1em; /* */
            font-weight: bold; /* */
            transition: background-color 0.2s ease; /* */
        }
        .update-cart-btn:hover, .checkout-btn:hover { /* Added .checkout-btn */
             background-color: #6a2a4a; /* Darker shade on hover */ /* */
        }
        .cart-message {
            text-align: center;
            color: red;
            margin: 10px 0;
        }


        @media screen and (max-width: 794px) { /* */
            /* .child-register-btn { margin-top: 30px; } */ /* */
            /* .child-register-btn p { width: 100%; margin-left: auto; margin-right: auto; } */ /* */
            table, .cart-total-section, /*.child-register-btn,*/ .cart-actions { width: 95%; } /* */
            th, td { padding: 8px 10px; } /* */
            td img.cart-product-image { max-width: 60px; } /* */
            td input.quantity-input { width: 50px; } /* */
            .cart-actions { flex-direction: column; gap: 10px; }
            .update-cart-btn, .checkout-btn { width: 100%; }
        }
         @media screen and (max-width: 480px) { /* */
            th, td { font-size: 0.9em; } /* */
            /* .child-register-btn p a { font-size: 16px; } */ /* */
            /* .update-cart-btn { width: 100%; margin-top: 10px;} */ /* */
            /* .cart-actions { text-align: center; } */ /* */
        }
    </style>
</header>

<main>
    <div class="product-container">
        <div class="container">
            <?php
            if (isset($_SESSION['cart_message'])) {
                echo '<p class="cart-message">' . htmlspecialchars($_SESSION['cart_message']) . '</p>';
                unset($_SESSION['cart_message']); // Clear the message after displaying
            }
            ?>
            <form action="cart.php" method="post" id="cart-form">
                <table>
                    <thead> <tr>
                        <th><input type="checkbox" id="select-all-checkbox" title="Select All"></th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody> <?php
                        $grand_total_all_items = 0; // Initialize grand total for all items
                        $currency_code = "USD"; // Define currency code //

                        if (!empty($_SESSION['mycart']) && is_array($_SESSION['mycart'])) { //
                            foreach ($_SESSION['mycart'] as $key => $value) { //
                                $price = isset($value['price']) ? filter_var($value['price'], FILTER_VALIDATE_FLOAT) : false; //
                                $quantity = (isset($value['product_qty']) && filter_var($value['product_qty'], FILTER_VALIDATE_INT) !== false && $value['product_qty'] >= 1) //
                                            ? (int)$value['product_qty'] //
                                            : 1; //
                                $name = isset($value['name']) ? htmlspecialchars($value['name']) : 'Unknown Item'; //
                                $img = isset($value['product_img']) ? htmlspecialchars($value['product_img']) : ''; //

                                if ($price !== false) { //
                                    $sub_total = $price * $quantity; //
                                    $grand_total_all_items += $sub_total; // Add item subtotal to grand total //
                                    // For currency display, assuming USD for now. You might want to make $currency_code dynamic from session or item.
                                    ?>
                                    <tr data-item-key="<?php echo $key; ?>" data-item-price="<?php echo $price; ?>">
                                        <td>
                                            <input type="checkbox" class="item-checkbox" name="selected_items[]" value="<?php echo $key; ?>">
                                        </td>
                                        <td>
                                            <?php if (!empty($img)): ?>
                                            <img class="cart-product-image"
                                                 src="./admin/upload/<?php echo $img; ?>"
                                                 alt="<?php echo $name; ?>"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <span style="display:none;">Img N/A</span>
                                            <?php else: ?>
                                            <span>No Image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $name; ?></td>
                                        <td><?php echo "$" . number_format($price, 2); ?></td>
                                        <td>
                                            <input type="number"
                                                   class="quantity-input"
                                                   name="quantities[<?php echo $key; ?>]"
                                                   value="<?php echo $quantity; ?>"
                                                   min="0" required data-key="<?php echo $key; ?>"> </td>
                                        <td class="item-subtotal"><?php echo "$" . number_format($sub_total, 2); ?></td>
                                        </tr>
                                    <?php
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="6">Invalid item data (missing price) for '<?php echo $name; ?>'. This item was not included in total.</td> </tr> <?php // ?>
                                    <?php
                                    error_log("Cart Page - Invalid item data (price missing): " . print_r($value, true)); //
                                }
                            }

                            // Store the total of ALL items in a separate session variable if needed for other purposes,
                            // but for checkout, we will use the total of selected items.
                            // $_SESSION['full_cart_total_for_display'] = $grand_total_all_items;


                        } else {
                            $_SESSION['mycart'] = []; //
                            ?>
                            <tr>
                                <td colspan="6">No items available in cart</td> </tr> <?php // ?>
                            <?php
                               if (isset($_SESSION['cart_total'])) unset($_SESSION['cart_total']); //
                               if (isset($_SESSION['currency_code'])) unset($_SESSION['currency_code']); //
                               error_log("Cart Page - Cart was initially empty or invalid, session variables unset."); //
                        }
                        ?>
                    </tbody>
                </table>

                <div class="cart-actions">
                    <?php if (!empty($_SESSION['mycart'])): ?>
                    <button type="submit" name="update_cart" class="update-cart-btn">Update Cart Quantities</button>
                    <button type="submit" name="proceed_to_checkout" class="checkout-btn" id="checkout-btn-actual">Proceed to Checkout (Selected)</button>
                    <?php endif; ?>
                </div>

            </form>
            <div class="cart-total-section">
                <p>Selected Items Total: <span id="dynamic-grand-total">$0.00</span></p>
                <?php /* Original grand total display can be kept for reference or removed
                if (isset($_SESSION['full_cart_total_for_display']) && $_SESSION['full_cart_total_for_display'] > 0): ?>
                <p>Full Cart Total: <?php echo "$" . number_format($_SESSION['full_cart_total_for_display'], 2); ?></p>
                <?php endif; */ ?>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const dynamicGrandTotalElement = document.getElementById('dynamic-grand-total');
    const checkoutButton = document.getElementById('checkout-btn-actual');

    function updateSelectedTotal() {
        let currentSelectedTotal = 0;
        itemCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const itemRow = checkbox.closest('tr');
                const price = parseFloat(itemRow.dataset.itemPrice);
                const quantityInput = itemRow.querySelector('.quantity-input');
                const quantity = parseInt(quantityInput.value, 10);

                if (!isNaN(price) && !isNaN(quantity) && quantity > 0) {
                    currentSelectedTotal += price * quantity;
                }
            }
        });
        dynamicGrandTotalElement.textContent = '$' + currentSelectedTotal.toFixed(2);
        
        // Disable checkout button if no items are selected or total is zero
        if (checkoutButton) {
            checkoutButton.disabled = currentSelectedTotal <= 0;
        }
    }

    function updateItemSubtotal(quantityInput) {
        const itemRow = quantityInput.closest('tr');
        const price = parseFloat(itemRow.dataset.itemPrice);
        const quantity = parseInt(quantityInput.value, 10);
        const subtotalCell = itemRow.querySelector('.item-subtotal');

        if (!isNaN(price) && !isNaN(quantity) && quantity >= 0) {
            const subtotal = price * quantity;
            subtotalCell.textContent = '$' + subtotal.toFixed(2);
        } else if (quantity === 0) {
            subtotalCell.textContent = '$0.00';
        }
        updateSelectedTotal(); // Update grand total when a quantity changes
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateSelectedTotal();
        });
    }

    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedTotal);
    });

    quantityInputs.forEach(input => {
        input.addEventListener('input', function() { // Use 'input' for immediate feedback
            updateItemSubtotal(this);
        });
        input.addEventListener('change', function() { // Fallback for browsers or methods that don't fire 'input' (e.g., spinners)
            updateItemSubtotal(this);
        });
    });

    // Initial calculation on page load
    updateSelectedTotal();
});
</script>

<?php require_once './includes/footer.php'; ?> <?php // ?>