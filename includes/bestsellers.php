<?php
// includes/bestsellers.php

// Ensure $conn is available. It should be from the parent file's includes (e.g., index.php -> headerNav.php -> functions.php -> config.php)
if (!isset($conn) || !$conn) {
    // Fallback for $conn - This is not ideal for production. Ensure $conn is properly initialized.
    // error_log("Database connection not available in bestsellers.php");
    // You might want to include config.php directly if this file can be accessed in a context where $conn isn't set.
    // include_once(__DIR__ . '/config.php'); 
}

// Fetch best-selling products using the function from functions.php
$best_selling_products_result = null;
if (isset($conn) && $conn && function_exists('get_best_selling_products')) {
    $best_selling_products_result = get_best_selling_products($conn, 3); // Get top 5
}

// Ensure the display_stars function is available. It should be in functions.php
// and functions.php should be included by headerNav.php or similar.
?>
<style>
    /* Add or ensure these styles are in your main CSS file */
    .bestsellers-section-container {
        padding: 2rem 1rem;
        background-color: #ffffff;
    }

    .bestsellers-section-title {
        text-align: center;
        font-size: 2rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 2rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .showcase-content .star-rating {
        color: #f8c200; /* Gold color for stars */
        font-size: 1rem; /* Adjust size as needed */
        margin-bottom: 5px;
        display: flex;     /* Aligns stars in a row */
        justify-content: center; /* Centers stars if the container is wider */
        align-items: center;
    }
    .showcase-content .star-rating ion-icon {
        margin: 0 1px; /* Tiny space between stars */
    }
    .showcase-content .no-rating {
        font-size: 0.8rem;
        color: #777;
        font-style: italic;
    }
    .showcase-content .total-reviews-count {
        font-size: 0.8rem;
        color: #555;
        margin-left: 5px; /* Space after stars */
    }
     /* Styles for .actions, .btn-action, .btn-view-details, .btn-buy-now should already be in your main CSS from previous step */
</style>

<div class="bestsellers-section-container">
    <div class="container">
        <h2 class="bestsellers-section-title">Best Sellers</h2>
        
        <?php if ($best_selling_products_result && $best_selling_products_result->num_rows > 0) : ?>
            <div class="product-grid">
                <?php while ($product = $best_selling_products_result->fetch_assoc()) : 
                    $product_actual_price_bs = $product['discounted_price'] ?? $product['product_price'];
                ?>
                    <div class="showcase">
                        <div class="showcase-banner">
                            <img src="./admin/upload/<?php echo htmlspecialchars($product['product_img']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['product_desc']); ?>" 
                                 width="300" class="product-img default" 
                                 onerror="this.onerror=null;this.src='https://placehold.co/300x300/EFEFEF/AAAAAA?text=Image+Not+Available';">
                            <img src="./admin/upload/<?php echo htmlspecialchars($product['product_img']); // Assuming hover image is the same ?>" 
                                 alt="<?php echo htmlspecialchars($product['product_desc']); ?>" 
                                 width="300" class="product-img hover"
                                 onerror="this.onerror=null;this.src='https://placehold.co/300x300/EFEFEF/AAAAAA?text=Image+Not+Available';">
                        </div>

                        <div class="showcase-content">
                            <a href="./viewdetail.php?id=<?php echo $product['product_id']; ?>&category=<?php echo $product['category_id']; ?>" class="showcase-category">
                                <?php echo htmlspecialchars($product['product_title']); ?>
                            </a>
                            <a href="./viewdetail.php?id=<?php echo $product['product_id']; ?>&category=<?php echo $product['category_id']; ?>">
                                <h3 class="showcase-title">
                                    <?php echo htmlspecialchars($product['product_desc']); ?>
                                </h3>
                            </a>

                            <?php if (function_exists('display_stars')) : ?>
                                <?php 
                                    echo display_stars($product['average_rating'] ?? null); 
                                    if (isset($product['total_reviews']) && $product['total_reviews'] > 0) {
                                        echo "<span class='total-reviews-count'>(" . $product['total_reviews'] . ")</span>";
                                    }
                                ?>
                            <?php endif; ?>

                            <div class="price-box">
                                <p class="price">$<?php echo htmlspecialchars($product_actual_price_bs); ?></p>
                                <?php if (isset($product['discounted_price']) && $product['discounted_price'] < $product['product_price']) : ?>
                                <del>$<?php echo htmlspecialchars($product['product_price']); ?></del>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-500" style="font-size: 0.8em; color: #6c757d; margin-top: 5px;">Sold: <?php echo htmlspecialchars($product['total_quantity_sold']); ?> units</p>
                            
                            <div class="actions">
                                <a href="./viewdetail.php?id=<?php echo $product['product_id']; ?>&category=<?php echo $product['category_id']; ?>" class="btn-action btn-view-details">View Details</a>
                                <form action="manage_cart.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['product_desc']); ?>">
                                    <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($product_actual_price_bs); ?>">
                                    <input type="hidden" name="product_category" value="<?php echo htmlspecialchars($product['category_id']); // Ensure this is the correct category identifier for cart logic ?>">
                                    <input type="hidden" name="product_qty" value="1">
                                    <input type="hidden" name="product_img" value="<?php echo htmlspecialchars($product['product_img']); ?>">
                                    <input type="hidden" name="buy_now_redirect" value="true">
                                    <button type="submit" name="add_to_cart" class="btn-action btn-buy-now">Buy Now</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <p class="text-center">No best-selling products to display at the moment.</p>
        <?php endif; ?>
        <?php
        // Clean up the result set
        if ($best_selling_products_result) {
            // $best_selling_products_result->free(); // Free the result set
            // The statement is usually closed within the function or when its scope ends.
        }
        ?>
    </div>
</div>
