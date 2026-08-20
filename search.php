<?php
   include_once('./includes/headerNav.php'); // Should include config.php and functions.php

    // --- Initialize variables ---
    $search_term_display = ""; // For displaying in the title
    $query_search_term_unsafe = ''; // For use in GET links (URL encoding needed)
    $query_search_term_safe = '';   // For use in SQL (escaped)

    $selected_product_catags = $_GET['filter_product_catag'] ?? [];
    $min_price = $_GET['min_price'] ?? '';
    $max_price = $_GET['max_price'] ?? '';

    // --- Handle Search Term ---
    if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
        $query_search_term_unsafe = trim($_GET['search']);
        $search_term_display = htmlspecialchars($query_search_term_unsafe);
        $query_search_term_safe = mysqli_real_escape_string($conn, $query_search_term_unsafe);
    } elseif (isset($_POST['search']) && !empty(trim($_POST['search']))) { // Handle initial POST search
        $query_search_term_unsafe = trim($_POST['search']);
        $search_term_display = htmlspecialchars($query_search_term_unsafe);
        // Redirect POST search to GET to make filters and pagination work with GET
        $redirect_params = ['search' => $query_search_term_unsafe];
        header('Location: search.php?' . http_build_query($redirect_params));
        exit;
    }


    // --- Fetch distinct product_catag values for filter options ---
    $distinct_catags = [];
    $catag_query = "SELECT DISTINCT product_catag FROM products WHERE status = 1 AND product_catag IS NOT NULL AND product_catag != '' ORDER BY product_catag ASC";
    $catag_result = $conn->query($catag_query);
    if ($catag_result && $catag_result->num_rows > 0) {
        while ($cat_row = $catag_result->fetch_assoc()) {
            $distinct_catags[] = $cat_row['product_catag'];
        }
    }

    // --- Pagination variables ---
    $limit = 9; // Products per page (e.g., for a 3x3 grid)
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // --- Build SQL Query with Filters ---
    $search_result = null;
    $total_search_products = 0;
    $total_search_pages = 0;
    $where_clauses = ["p.status = 1"];
    $sql_params = [];
    $sql_param_types = "";

    if (!empty($query_search_term_safe)) {
        $where_clauses[] = "(p.product_title LIKE ? OR p.product_desc LIKE ?)";
        $search_like_term = "%" . $query_search_term_safe . "%";
        $sql_params[] = &$search_like_term; // Pass by reference for bind_param
        $sql_params[] = &$search_like_term;
        $sql_param_types .= "ss";
    }

    if (!empty($selected_product_catags) && is_array($selected_product_catags)) {
        $catag_placeholders = implode(',', array_fill(0, count($selected_product_catags), '?'));
        $where_clauses[] = "p.product_catag IN (" . $catag_placeholders . ")";
        foreach ($selected_product_catags as $catag) {
            $sql_params[] = &$catag; // Pass by reference
            $sql_param_types .= "s";
        }
    }

    if ($min_price !== '' && is_numeric($min_price)) {
        $where_clauses[] = "((p.discounted_price IS NOT NULL AND p.discounted_price >= ?) OR (p.discounted_price IS NULL AND p.product_price >= ?))";
        $sql_params[] = &$min_price;
        $sql_params[] = &$min_price;
        $sql_param_types .= "dd"; // Assuming prices can be decimals
    }
    if ($max_price !== '' && is_numeric($max_price)) {
        $where_clauses[] = "((p.discounted_price IS NOT NULL AND p.discounted_price <= ?) OR (p.discounted_price IS NULL AND p.product_price <= ?))";
        $sql_params[] = &$max_price;
        $sql_params[] = &$max_price;
        $sql_param_types .= "dd";
    }

    $where_sql = "";
    if (!empty($where_clauses)) {
        $where_sql = "WHERE " . implode(" AND ", $where_clauses);
    }

    // Main query to fetch products
    $main_query_sql = "SELECT 
                        p.*, 
                        AVG(r.rating) AS average_rating, 
                        COUNT(DISTINCT r.review_id) AS total_reviews
                     FROM products p
                     LEFT JOIN reviews r ON p.product_id = r.product_id AND r.status = 'approved'
                     {$where_sql}
                     GROUP BY p.product_id, p.product_catag, p.product_title, p.product_price, p.product_desc, p.product_date, p.product_img, p.product_left, p.product_author, p.category_id, p.section_id, p.discounted_price, p.image_1, p.image_2, p.created_at, p.updated_at, p.status
                     ORDER BY p.product_id DESC 
                     LIMIT ?, ?";
    
    $stmt_main = $conn->prepare($main_query_sql);
    if ($stmt_main) {
        $current_params = $sql_params; // Copy params for main query
        $current_params[] = &$offset;
        $current_params[] = &$limit;
        $current_param_types = $sql_param_types . "ii";
        
        if (!empty($current_param_types)) { // Only bind if there are params
             $stmt_main->bind_param($current_param_types, ...$current_params);
        }
       
        $stmt_main->execute();
        $search_result = $stmt_main->get_result();
        if (!$search_result) {
            error_log("Search query error: " . $stmt_main->error);
        }
        $stmt_main->close();
    } else {
        error_log("Error preparing main search statement: " . $conn->error);
    }


    // Count total matching products for pagination
    $count_query_sql = "SELECT COUNT(DISTINCT p.product_id) as total 
                        FROM products p 
                        {$where_sql}";
    $stmt_count = $conn->prepare($count_query_sql);
    if ($stmt_count) {
        if (!empty($sql_param_types)) { // Only bind if there are params for WHERE clause
             $stmt_count->bind_param($sql_param_types, ...$sql_params); // Use original $sql_params (without limit/offset)
        }
        $stmt_count->execute();
        $count_result_obj = $stmt_count->get_result();
        if ($count_result_obj) {
            $count_row = $count_result_obj->fetch_assoc();
            $total_search_products = $count_row['total'] ?? 0;
            $total_search_pages = ceil($total_search_products / $limit);
        } else {
            error_log("Count query error: " . $stmt_count->error);
        }
        $stmt_count->close();
    } else {
         error_log("Error preparing count statement: " . $conn->error);
    }

?>
<head>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <style>
        /* Ensure all these styles are in your main external CSS file */
        /* Sidebar Styles (from category_sidebar_styles artifact) */
        .sidebar { background-color: #ffffff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); display: flex; flex-direction: column; }
        .sidebar .sidebar-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e9ecef; flex-shrink: 0; }
        .sidebar .sidebar-title { font-size: 1.4rem; font-weight: 600; color: #343a40; margin: 0; }
        /* ... (Include all other sidebar styles from category_sidebar_styles artifact here) ... */
        .sidebar.has-scrollbar { max-height: 70vh; overflow-y: auto; }
        .sidebar.has-scrollbar::-webkit-scrollbar { width: 6px; }
        .sidebar.has-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .sidebar.has-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
        .sidebar.has-scrollbar::-webkit-scrollbar-thumb:hover { background: #aaa; }

        /* Search Page Layout & Filter Form Styles */
        .search-page-container .container { max-width: 1200px; margin: 0 auto; padding: 20px 15px; }
        .search-content-wrapper { display: flex; flex-direction: row; gap: 2rem; }
        .search-sidebar-column { flex: 0 0 280px; }
        .search-results-column { flex: 1 1 auto; min-width: 0; }
        .search-results-column .title { font-size: 1.8rem; font-weight: 600; color: #333; margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid #eee; }
        
        .filter-section { margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #eee; }
        .filter-section-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 0.75rem; color: #444; }
        .filter-form label { display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: #555; }
        .filter-form input[type="checkbox"] { margin-right: 0.5rem; vertical-align: middle; }
        .filter-form .form-group { margin-bottom: 1rem; }
        .filter-form input[type="number"] {
            width: calc(100% - 16px); /* Adjust for padding */
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .filter-form .price-range-group { display: flex; gap: 10px; }
        .filter-form .price-range-group > div { flex: 1; }
        .filter-form .btn-apply-filters, .filter-form .btn-reset-filters {
            width: 100%;
            padding: 10px;
            font-size: 0.9rem;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            margin-top: 0.5rem;
        }
        .btn-apply-filters { background-color: #D19C97; color: white; border: 1px solid #D19C97; }
        .btn-apply-filters:hover { background-color: #c5837c; }
        .btn-reset-filters { background-color: #6c757d; color: white; border: 1px solid #6c757d; }
        .btn-reset-filters:hover { background-color: #5a6268; }

        /* Product Grid & Card Styles (ensure these are in your main CSS or defined here) */
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; }
        /* ... (Include other product card styles from previous search.php version) ... */
        .showcase { background-color: #fff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; display: flex; flex-direction: column; }
        .showcase:hover { transform: translateY(-5px); box-shadow: 0 6px 12px rgba(0,0,0,0.1); }
        .showcase-banner { position: relative; overflow: hidden; }
        .showcase-banner .product-img { width: 100%; height: auto; aspect-ratio: 1 / 1; object-fit: cover; transition: transform 0.4s ease; }
        .showcase:hover .showcase-banner .product-img.default { transform: scale(1.05); }
        .showcase-content { padding: 1rem; text-align: center; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .showcase-category { display: block; font-size: 0.75rem; color: #D19C97; text-transform: uppercase; margin-bottom: 0.25rem; font-weight: 500; }
        .showcase-title { font-size: 1rem; font-weight: 600; color: #333; margin-bottom: 0.5rem; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; min-height: 2.6em; }
        .price-box { margin-bottom: 0.75rem; }
        .price-box .price { font-size: 1.1rem; font-weight: 600; color: #D19C97; }
        .price-box del { font-size: 0.85rem; color: #999; margin-left: 0.5rem; }
        .showcase-content .actions { margin-top: auto; display: flex; flex-direction: column; gap: 8px; padding-top: 10px; }
        .showcase-content .btn-action { width: 100%; padding: 10px 15px; font-size: 0.85rem; text-transform: uppercase; border-radius: 5px; text-decoration: none; transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease; cursor: pointer; font-weight: 500; }
        .btn-view-details { background-color: #f0f0f0; color: #333; border: 1px solid #ddd; }
        .btn-view-details:hover { background-color: #e0e0e0; border-color: #ccc; }
        .btn-buy-now { background-color: #D19C97; color: white; border: 1px solid #D19C97; }
        .btn-buy-now:hover { background-color: #c5837c; border-color: #c5837c; }
        .showcase-content .star-rating { color: #f8c200; font-size: 0.9rem; margin-bottom: 8px; display: flex; justify-content: center; align-items: center; }
        .showcase-content .star-rating ion-icon { margin: 0 1px; }
        .showcase-content .no-rating { font-size: 0.75rem; color: #888; font-style: italic; margin-bottom: 8px; }
        .showcase-content .total-reviews-count { font-size: 0.75rem; color: #666; margin-left: 4px; }

        .pag-cont-search { margin-top: 2.5rem; margin-bottom: 2rem; }
        .pagination { display: flex; justify-content: center; list-style: none; padding: 0; margin: 0; gap: 8px; }
        .pagination a { display: block; padding: 10px 15px; text-decoration: none; color: #D19C97; background-color: #fff; border: 1px solid #ddd; border-radius: 5px; transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease; }
        .pagination a:hover { background-color: #f8f0ef; border-color: #D19C97; }
        .pagination a.active { background-color: #D19C97; color: #ffffff; border-color: #D19C97; font-weight: bold; cursor: default; }
        .no-results-message { text-align: center; font-size: 1.2rem; color: #555; padding: 2rem; background-color: #f9f9f9; border-radius: 8px; margin-top: 1rem; }

        @media (max-width: 991px) { 
            .search-content-wrapper { flex-direction: column; }
            .search-sidebar-column { flex: 1 1 100%; max-width: 100%; margin-bottom: 2rem; }
            .sidebar.has-scrollbar { position: static; width: 100%; max-height: none; overflow-y: visible; }
            .sidebar .sidebar-menu-category-list { overflow-y: visible; max-height: none; }
        }
         @media (max-width: 767px) { 
            .product-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); }
            .search-results-column .title { font-size: 1.5rem; }
        }
    </style>
</head>

<div class="overlay" data-overlay></div>
<header>
 <?php require_once './includes/desktopnav.php' ?>
 <?php require_once './includes/mobilenav.php'; ?>
</header>

<main>
  <div class="search-page-container">
    <div class="container">
      <div class="search-content-wrapper">
        
        <div class="search-sidebar-column">
          <?php require_once './includes/categorysidebar.php'; // Your existing detailed category sidebar ?>

          <div class="filter-section sidebar"> <h3 class="sidebar-title">Filter </h3>
            <form action="search.php" method="GET" class="filter-form">
                <?php if (!empty($query_search_term_unsafe)): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($query_search_term_unsafe); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <h4 class="filter-section-title">Sex </h4>
                    <?php if (!empty($distinct_catags)): ?>
                        <?php foreach ($distinct_catags as $catag_option): ?>
                            <div>
                                <label>
                                    <input type="checkbox" name="filter_product_catag[]" value="<?php echo htmlspecialchars($catag_option); ?>"
                                        <?php if (in_array($catag_option, $selected_product_catags)) echo 'checked'; ?>>
                                    <?php echo htmlspecialchars(ucfirst($catag_option)); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No categories available for filtering.</p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <h4 class="filter-section-title">Price Range</h4>
                    <div class="price-range-group">
                        <div>
                            <label for="min_price">Min Price ($)</label>
                            <input type="number" id="min_price" name="min_price" value="<?php echo htmlspecialchars($min_price); ?>" placeholder="e.g., 10" min="0" step="0.01">
                        </div>
                        <div>
                            <label for="max_price">Max Price ($)</label>
                            <input type="number" id="max_price" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>" placeholder="e.g., 100" min="0" step="0.01">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-apply-filters">Apply Filters</button>
                <a href="search.php?search=<?php echo urlencode($query_search_term_unsafe); ?>" class="btn-reset-filters" style="margin-top: 10px;">Reset Filters</a>
            </form>
          </div>
        </div>

        <div class="search-results-column">
          <h2 class="title">
            <?php 
                if (!empty($search_term_display)) {
                    echo 'Search Results for: "' . $search_term_display . '"';
                } elseif (!empty($selected_product_catags) || $min_price !== '' || $max_price !== '') {
                    echo 'Filtered Products';
                } else {
                    echo 'All Products'; // Or prompt to search/filter
                }
            ?>
          </h2>

          <?php if ($search_result && $search_result->num_rows > 0) : ?>
            <div class="product-grid">
              <?php while ($row = $search_result->fetch_assoc()) :
                  $product_actual_price = $row['discounted_price'] ?? $row['product_price'];
              ?>
                <div class="showcase">
                  <div class="showcase-banner">
                     <a href="./viewdetail.php?id=<?php echo $row['product_id']; ?>&category=<?php echo htmlspecialchars($row['product_catag'] ?? ($row['category_id'] ?? '')); ?>">
                        <img src="./admin/upload/<?php echo htmlspecialchars($row['product_img']); ?>" alt="<?php echo htmlspecialchars($row['product_desc']); ?>" class="product-img default" onerror="this.onerror=null;this.src='https://placehold.co/300x300/EFEFEF/AAAAAA?text=Image+Not+Found';"/>
                    </a>
                  </div>
                  <div class="showcase-content">
                    <div>
                        <a href="./viewdetail.php?id=<?php echo $row['product_id']; ?>&category=<?php echo htmlspecialchars($row['product_catag'] ?? ($row['category_id'] ?? '')); ?>" class="showcase-category">
                            <?php echo htmlspecialchars($row['product_title']); ?>
                        </a>
                        <a href="./viewdetail.php?id=<?php echo $row['product_id']; ?>&category=<?php echo htmlspecialchars($row['product_catag'] ?? ($row['category_id'] ?? '')); ?>">
                            <h3 class="showcase-title"><?php echo htmlspecialchars($row['product_desc']); ?></h3>
                        </a>
                        
                        <?php if (function_exists('display_stars')) : ?>
                            <?php
                                $avg_rating = $row['average_rating'] ?? null;
                                $total_revs = $row['total_reviews'] ?? 0;
                                echo display_stars($avg_rating); 
                                if ($total_revs > 0) {
                                   echo "<span class='total-reviews-count'>(" . $total_revs . ")</span>";
                                }
                            ?>
                        <?php endif; ?>

                        <div class="price-box">
                            <p class="price">$<?php echo htmlspecialchars($product_actual_price); ?></p>
                            <?php if (isset($row['discounted_price']) && $row['discounted_price'] < $row['product_price']) : ?>
                                <del>$<?php echo htmlspecialchars($row['product_price']); ?></del>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="actions">
                        <a href="./viewdetail.php?id=<?php echo $row['product_id']; ?>&category=<?php echo htmlspecialchars($row['product_catag'] ?? ($row['category_id'] ?? '')); ?>" class="btn-action btn-view-details">View Details</a>
                        <form action="manage_cart.php" method="POST" style="display: block;">
                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['product_desc']); ?>">
                            <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($product_actual_price); ?>">
                            <input type="hidden" name="product_category" value="<?php echo htmlspecialchars($row['product_catag'] ?? ($row['category_id'] ?? '')); ?>">
                            <input type="hidden" name="product_qty" value="1">
                            <input type="hidden" name="product_img" value="<?php echo htmlspecialchars($row['product_img']); ?>">
                            <input type="hidden" name="buy_now_redirect" value="true">
                            <button type="submit" name="add_to_cart" class="btn-action btn-buy-now">Buy Now</button>
                        </form>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
          <?php elseif (!empty($query_search_term_safe) || !empty($selected_product_catags) || $min_price !== '' || $max_price !== '') : ?>
            <p class="no-results-message">No products found matching your criteria.</p>
          <?php else: ?>
            <p class="no-results-message">Please enter a search term or apply filters to find products.</p>
          <?php endif; ?>
          <?php if ($search_result instanceof mysqli_result) { $search_result->free(); } ?>
        </div>

        <?php if ($total_search_pages > 1) : ?>
          <div class="pag-cont-search">
            <ul class="pagination">
              <?php
                // Build base URL for pagination, including existing filters and search term
                $pagination_params = [];
                if (!empty($query_search_term_unsafe)) $pagination_params['search'] = $query_search_term_unsafe;
                if (!empty($selected_product_catags)) $pagination_params['filter_product_catag'] = $selected_product_catags;
                if ($min_price !== '') $pagination_params['min_price'] = $min_price;
                if ($max_price !== '') $pagination_params['max_price'] = $max_price;
              ?>
              <?php if ($page > 1) : ?>
                <li><a href="search.php?<?php echo http_build_query(array_merge($pagination_params, ['page' => $page - 1])); ?>">&laquo; Prev</a></li>
              <?php endif; ?>

              <?php for ($i = 1; $i <= $total_search_pages; $i++) : ?>
                <li><a href="search.php?<?php echo http_build_query(array_merge($pagination_params, ['page' => $i])); ?>" class="<?php if ($page == $i) echo 'active'; ?>"><?php echo $i; ?></a></li>
              <?php endfor; ?>

              <?php if ($page < $total_search_pages) : ?>
                <li><a href="search.php?<?php echo http_build_query(array_merge($pagination_params, ['page' => $page + 1])); ?>">Next &raquo;</a></li>
              <?php endif; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div> </div> </div> </div> </main>

<?php require_once './includes/footer.php'; ?>
