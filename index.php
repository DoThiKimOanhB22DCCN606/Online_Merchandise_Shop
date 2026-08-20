<?php
  include_once('./includes/headerNav.php'); // Should include functions.php where get_new_products and display_stars are
  $banner_products = get_banner_details();
  $categories = [
      ['name' => 'Vinyl', 'slug' => 'vinyl', 'image' => 'images/vinyl.jpg'],
      ['name' => 'Accessories', 'slug' => 'accessories', 'image' => 'images/accessories.jpg'],
      ['name' => 'Apparel', 'slug' => 'apparel', 'image' => 'images/apparel.jpg'],
      ['name' => 'CD', 'slug' => 'cd', 'image' => 'images/cd.jpg'],
  ];
?>
<head>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <style>
      /* Ensure these styles are in your main CSS file or a shared <style> block in headerNav.php */
      .showcase-content .actions { margin-top: 10px; display: flex; gap: 10px; justify-content: center; }
      .showcase-content .btn-action { padding: 8px 12px; font-size: 0.8rem; text-transform: uppercase; border-radius: 4px; text-decoration: none; transition: background-color 0.3s ease, color 0.3s ease; cursor: pointer; border: 1px solid transparent; }
      .btn-view-details { background-color: #555; color: white; border-color: #555; }
      .btn-view-details:hover { background-color: #333; }
      .btn-buy-now { background-color: #D19C97; color: white; border-color: #D19C97; }
      .btn-buy-now:hover { background-color: #c5837c; }
      .category-section-container { padding: 2rem 1rem; background-color: #f9f9f9; }
      .category-section-title { text-align: center; font-size: 2rem; font-weight: 600; color: #333; margin-bottom: 2rem; text-transform: uppercase; letter-spacing: 1px; }
      .category-grid { display: flex; flex-wrap: wrap; gap: 1.5rem; justify-content: center; }
      .category-card { background-color: #fff; border: 1px solid #eee; border-radius: 8px; overflow: hidden; text-decoration: none; color: #333; width: calc(25% - 1.5rem); min-width: 200px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); transition: transform 0.3s ease, box-shadow 0.3s ease; display: flex; flex-direction: column; }
      .category-card:hover { transform: translateY(-5px); box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12); }
      .category-card-image-container { width: 100%; height: 200px; overflow: hidden; }
      .category-card-image { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; }
      .category-card:hover .category-card-image { transform: scale(1.05); }
      .category-card-content { padding: 1rem; text-align: center; }
      .category-card-name { font-size: 1.25rem; font-weight: 600; margin: 0; }
      @media (max-width: 992px) { .category-card { width: calc(50% - 1rem); } }
      @media (max-width: 576px) { .category-card { width: 100%; } .category-section-title { font-size: 1.5rem; } .category-card-name { font-size: 1rem; } }

      /* Star Rating Styles */
      .showcase-content .star-rating { color: #f8c200; font-size: 1rem; margin-bottom: 5px; display: flex; justify-content: center; align-items: center; }
      .showcase-content .star-rating ion-icon { margin: 0 1px; }
      .showcase-content .no-rating { font-size: 0.8rem; color: #777; font-style: italic; }
      .showcase-content .total-reviews-count { font-size: 0.8rem; color: #555; margin-left: 5px; }
    </style>
</head>

<div class="overlay" data-overlay></div>
<header>
  <?php require_once './includes/desktopnav.php' ?>
  <?php require_once './includes/mobilenav.php'; ?>
</header>

<main>
  <div class="banner">
    <div class="container">
      <div class="slider-container has-scrollbar">
        <?php
        if ($banner_products && $banner_products instanceof mysqli_result) {
            while ($row = mysqli_fetch_assoc($banner_products)) {
        ?>
          <div class="slider-item">
            <img src="images/carousel/<?php echo htmlspecialchars($row['banner_image']); ?>" alt="<?php echo htmlspecialchars($row['banner_title']); ?>" class="banner-img" onerror="this.onerror=null;this.src='https://placehold.co/1200x400/EFEFEF/AAAAAA?text=Image+Not+Found';" />
            <div class="banner-content">
              <p class="banner-subtitle"><?php echo htmlspecialchars($row['banner_subtitle']); ?></p>
              <h2 class="banner-title"><?php echo htmlspecialchars($row['banner_title']); ?></h2>
              <p class="banner-text">starting at &dollar;<b><?php echo htmlspecialchars($row['banner_items_price']); ?></b>.00</p>
              <a href="category_display.php?type=vinyl" class="banner-btn">Shop now</a>
            </div>
          </div>
        <?php
            }
        }
        ?>
      </div>
    </div>
  </div>

  <div class="category-section-container">
    <div class="container">
      <h2 class="category-section-title">Shop by Category</h2>
      <div class="category-grid">
        <?php foreach ($categories as $category) : ?>
          <a href="category_display.php?type=<?php echo urlencode($category['slug']); ?>" class="category-card">
            <div class="category-card-image-container">
              <img src="<?php echo htmlspecialchars($category['image']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?> Category" class="category-card-image" onerror="this.onerror=null;this.src='https://placehold.co/300x200/EFEFEF/AAAAAA?text=<?php echo urlencode($category['name']); ?>';">
            </div>
            <div class="category-card-content">
              <h3 class="category-card-name"><?php echo htmlspecialchars($category['name']); ?></h3>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <?php require_once './includes/dealoftheday.php' ?>
  <?php require_once './includes/bestsellers.php'; // Include best sellers section ?>

  <div class="product-main">
    <h2 class="title">All Products</h2>
    <div class="product-grid">
      <?php
      $limit = 8;
      $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
      $offset = ($page - 1) * $limit;
      
      if (!isset($conn) || !$conn) {
          include_once('./includes/config.php'); 
      }
      
      $all_products_result = null; // Initialize to null
      if (function_exists('get_new_products')) { // Check if function exists before calling
          $all_products_result = get_new_products($offset, $limit);
      }

      if ($all_products_result && $all_products_result->num_rows > 0) {
          while ($row = mysqli_fetch_assoc($all_products_result)) {
              $product_actual_price = $row['discounted_price'] ?? $row['product_price'];
      ?>
            <div class="showcase">
              <div class="showcase-banner">
                <img src="./admin/upload/<?php echo htmlspecialchars($row['product_img']); ?>" alt="<?php echo htmlspecialchars($row['product_desc']); ?>" width="300" class="product-img default" onerror="this.onerror=null;this.src='https://placehold.co/300x300/EFEFEF/AAAAAA?text=Image+Not+Found';" />
                <img src="./admin/upload/<?php echo htmlspecialchars($row['product_img']); ?>" alt="<?php echo htmlspecialchars($row['product_desc']); ?>" width="300" class="product-img hover" onerror="this.onerror=null;this.src='https://placehold.co/300x300/EFEFEF/AAAAAA?text=Image+Not+Found';" />
              </div>
              <div class="showcase-content">
                <a href="./viewdetail.php?id=<?php echo $row['product_id']; ?>&category=<?php echo $row['category_id']; ?>" class="showcase-category">
                  <?php echo htmlspecialchars($row['product_title']); ?>
                </a>
                <a href="./viewdetail.php?id=<?php echo $row['product_id']; ?>&category=<?php echo $row['category_id']; ?>">
                  <h3 class="showcase-title">
                    <?php echo htmlspecialchars($row['product_desc']); ?>
                  </h3>
                </a>

                <?php if (function_exists('display_stars')) : ?>
                    <?php 
                        // Ensure 'average_rating' and 'total_reviews' keys exist
                        $avg_rating = isset($row['average_rating']) ? $row['average_rating'] : null;
                        $total_revs = isset($row['total_reviews']) ? $row['total_reviews'] : 0;

                        echo display_stars($avg_rating); 
                        if ($total_revs > 0) {
                            echo "<span class='total-reviews-count'>(" . $total_revs . ")</span>";
                        }
                    ?>
                <?php else: ?>
                    <p class="no-rating">(Rating display function not available)</p>
                <?php endif; ?>

                <div class="price-box">
                  <p class="price">$<?php echo htmlspecialchars($product_actual_price); ?></p>
                  <?php if (isset($row['discounted_price']) && $row['discounted_price'] < $row['product_price']) : ?>
                    <del>$<?php echo htmlspecialchars($row['product_price']); ?></del>
                  <?php endif; ?>
                </div>
                <div class="actions">
                    <a href="./viewdetail.php?id=<?php echo $row['product_id']; ?>&category=<?php echo $row['category_id']; ?>" class="btn-action btn-view-details">View Details</a>
                    <form action="manage_cart.php" method="POST" style="display: inline;">
                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['product_desc']); ?>">
                        <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($product_actual_price); ?>">
                        <input type="hidden" name="product_category" value="<?php echo htmlspecialchars($row['category_id']); ?>">
                        <input type="hidden" name="product_qty" value="1">
                        <input type="hidden" name="product_img" value="<?php echo htmlspecialchars($row['product_img']); ?>">
                        <input type="hidden" name="buy_now_redirect" value="true">
                        <button type="submit" name="add_to_cart" class="btn-action btn-buy-now">Buy Now</button>
                    </form>
                </div>
              </div>
            </div>
      <?php
          }
      } else {
          echo "<p class='text-center col-12'>No products found.</p>";
      }
      // Clean up the result set for all_products_result
      if ($all_products_result instanceof mysqli_result) {
           $all_products_result->free(); // Free the result set
      }
      ?>
    </div>
  </div>
  
  <?php
  if (isset($conn) && $conn) {
      $count_sql = "SELECT COUNT(*) as total_products FROM products WHERE status = 1";
      $count_result = mysqli_query($conn, $count_sql);
      if ($count_result) {
          $count_row = mysqli_fetch_assoc($count_result);
          $total_products = $count_row['total_products'];
          $total_page = ceil($total_products / $limit);

          if ($total_page > 1) {
  ?>
            <nav class="main-pagination" style="margin-left: 10px; padding-bottom: 20px;">
              <ul class="pagination-ul">
                <?php for ($i = 1; $i <= $total_page; $i++) {
                    $active = ($page == $i) ? "page-active" : "";
                ?>
                  <li class="page-item-number <?php echo $active; ?>">
                    <a class="page-number-link " href="index.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                  </li>
                <?php } ?>
              </ul>
            </nav>
  <?php
          }
           if ($count_result instanceof mysqli_result) $count_result->free();
      }
  }
  ?>
</main>

<?php require_once './includes/footer.php'; ?>
<script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
<df-messenger
  intent="WELCOME"
  chat-title="bot"
  agent-id="df9827c8-a592-429c-a048-e8b7909704ee"
  language-code="en"
></df-messenger>
