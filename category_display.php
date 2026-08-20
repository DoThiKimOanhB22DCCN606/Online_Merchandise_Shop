<?php
// category_display.php

// Include header, database configuration, and functions
include_once('./includes/headerNav.php'); // This should include config.php and functions.php

// Get the category type from the URL
$category_type_slug = $_GET['type'] ?? '';

if (empty($category_type_slug)) {
    echo "<div class='container'><p class='text-center'>No category specified.</p></div>";
    include_once('./includes/footer.php');
    exit;
}

// Sanitize the input
$category_type_slug = htmlspecialchars(strip_tags($category_type_slug));

// Determine the actual product_title to query based on the slug
$product_title_query_value = '';
$page_title = '';

switch ($category_type_slug) {
    case 'vinyl':
        $product_title_query_value = 'vinyl';
        $page_title = 'Vinyl Products';
        break;
    case 'accessories':
        $product_title_query_value = 'accessories';
        $page_title = 'Accessories';
        break;
    case 'apparel':
        $product_title_query_value = "Apparel\n"; // Handle the newline character
        $page_title = 'Apparel';
        break;
    case 'cd':
        $product_title_query_value = 'cd';
        $page_title = 'CDs';
        break;
    default:
        echo "<div class='container'><p class='text-center'>Invalid category specified.</p></div>";
        include_once('./includes/footer.php');
        exit;
}

// Pagination variables
$limit = 8; // Number of products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch products for the selected category
// Ensure $conn is available from included files
if (!isset($conn) || !$conn) {
    // Fallback, though ideally $conn is set via includes/config.php
    echo "<div class='container'><p class='text-center'>Database connection error.</p></div>";
    include_once('./includes/footer.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM products WHERE product_title = ? AND status = 1 LIMIT ? OFFSET ?");
$status = 1; // Assuming status 1 means active product
$stmt->bind_param("sii", $product_title_query_value, $limit, $offset);
$stmt->execute();
$products_result = $stmt->get_result();

// Count total products for pagination
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE product_title = ? AND status = 1");
$count_stmt->bind_param("s", $product_title_query_value);
$count_stmt->execute();
$total_products_row = $count_stmt->get_result()->fetch_assoc();
$total_products = $total_products_row['total'];
$total_pages = ceil($total_products / $limit);

?>

<header>
  <?php require_once './includes/desktopnav.php'; ?>
  <?php require_once './includes/mobilenav.php'; ?>
</header>

<main>
    <div class="container" style="padding-top: 20px; padding-bottom: 20px;">
        <h1 class="text-center mb-4" style="font-size: 2.5rem; font-weight: 600;"><?php echo htmlspecialchars($page_title); ?></h1>

        <?php if ($products_result && $products_result->num_rows > 0) : ?>
            <div class="product-grid">
                <?php while ($row = $products_result->fetch_assoc()) : ?>
                    <div class="showcase">
                        <div class="showcase-banner">
                            <img src="./admin/upload/<?php echo htmlspecialchars($row['product_img']); ?>" alt="<?php echo htmlspecialchars($row['product_desc']); ?>" width="300" class="product-img default" onerror="this.onerror=null;this.src='https://placehold.co/300x300/EFEFEF/AAAAAA?text=Image+Not+Found';" />
                            <img src="./admin/upload/<?php echo htmlspecialchars($row['product_img']); // Assuming hover image is same ?>" alt="<?php echo htmlspecialchars($row['product_desc']); ?>" width="300" class="product-img hover" onerror="this.onerror=null;this.src='https://placehold.co/300x300/EFEFEF/AAAAAA?text=Image+Not+Found';" />
                            </div>

                        <div class="showcase-content">
                            <a href="./viewdetail.php?id=<?php echo $row['product_id']; ?>&category=<?php echo $row['category_id']; ?>" class="showcase-category">
                                <?php echo htmlspecialchars($row['product_title']); // This will be 'vinyl', 'accessories', etc. ?>
                            </a>
                            <a href="./viewdetail.php?id=<?php echo $row['product_id']; ?>&category=<?php echo $row['category_id']; ?>">
                                <h3 class="showcase-title">
                                    <?php echo htmlspecialchars($row['product_desc']); // This is the actual product name ?>
                                </h3>
                            </a>
                            <div class="price-box">
                                <p class="price">
                                    $<?php echo htmlspecialchars($row['discounted_price'] ?? $row['product_price']); ?>
                                </p>
                                <?php if (isset($row['discounted_price']) && $row['discounted_price'] < $row['product_price']) : ?>
                                <del>
                                    $<?php echo htmlspecialchars($row['product_price']); ?>
                                </del>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($total_pages > 1) : ?>
            <nav class="main-pagination" style="margin-top: 20px;">
              <ul class="pagination-ul">
                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                  <?php $active = ($page == $i) ? "page-active" : ""; ?>
                  <li class="page-item-number <?php echo $active; ?>">
                    <a class="page-number-link" href="category_display.php?type=<?php echo urlencode($category_type_slug); ?>&page=<?php echo $i; ?>">
                      <?php echo $i; ?>
                    </a>
                  </li>
                <?php endfor; ?>
              </ul>
            </nav>
            <?php endif; ?>

        <?php else : ?>
            <p class="text-center">No products found in this category.</p>
        <?php endif; ?>
        <?php
            $stmt->close();
            $count_stmt->close();
            // mysqli_close($conn); // Close connection if footer doesn't need it
        ?>
    </div>
</main>

<?php include_once('./includes/footer.php'); ?>
```

**Important Notes for `category_display.php`:**
1.  **File Creation**: Create a new file named `category_display.php` in the same directory as your `index.php`.
2.  **Includes**: It reuses your existing `headerNav.php` (which should set up the database connection `$conn` via `config.php` and load `functions.php`) and `footer.php`.
3.  **Product Display**: It uses a similar product grid structure as your `index.php`. Ensure the CSS classes (`product-grid`, `showcase`, etc.) are defined in your `style.css` to make it look consistent.
4.  **`product_title` Handling**: It specifically handles the "Apparel\n" case. If your data for `product_title` becomes more consistent (e.g., no trailing newlines), you can simplify the `switch` statement.
5.  **Pagination**: Basic pagination has been added.
6.  **Error Handling & Security**: Basic checks and `htmlspecialchars()` are used. Robust error handling and further input sanitization are always recommended for production applications.

**Next Steps:**
1.  Add the CSS from the `index.php` example to your main stylesheet (e.g., `style.css`).
2.  Create the category images and place them in the specified paths (e.g., `images/category/`) or update the paths in the `$categories` array in `index.php`.
3.  Test the new category section on your homepage and ensure the links take you to `category_display.php` with the correct products shown.
4.  You might want to refine the styling of both the category section and the `category_display.php` page to match your site's overall design.

This should give you a good starting point for your category navigation! Let me know if you have any questions or need further adjustmen