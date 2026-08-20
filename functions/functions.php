<?php
    require_once './includes/config.php';

    // get banner products and details
    function get_banner_details(){
        global $conn;
        $query = "SELECT * FROM banner WHERE banner.banner_status = 1";
        return $result = mysqli_query($conn, $query);
    }

    // get music category
    function get_music_category(){
        global $conn;
        $query = "SELECT * FROM music WHERE music.coloth_category_status = 1";
        return $result = mysqli_query($conn, $query);
    }

    // get merchandise category
    function get_merchandise_category(){
        global $conn;
        $query = "SELECT * FROM merchandise WHERE merchandise.footwear_category_status = 1";
        return $result = mysqli_query($conn, $query);
    }

    // get collections category
    function get_collections_category(){
        global $conn;
        $query = "SELECT * FROM collections WHERE collections.perfume_category_status = 1";
        return $result = mysqli_query($conn, $query);
    }

    // get category bar items (formerly get_top_rated_products based on original naming)
    function get_category_bar_products(){
        global $conn;
        $query = "SELECT * FROM category_bar WHERE category_bar.category_status = 1";
        return $result = mysqli_query($conn, $query);
    }

    // get categories 
    function get_categories(){
        global $conn;
        $query = "SELECT * FROM category WHERE category.status = 1";
        return $result = mysqli_query($conn, $query);
    }
    
    // get clothes category (Note: 'clothes' table was not in your SQL dump, ensure it exists if used)
    function get_clothes_category(){
        global $conn;
        $query = "SELECT * FROM clothes WHERE clothes.coloth_category_status = 1"; // Assuming 'clothes' table
        return $result = mysqli_query($conn, $query);
    }

    // get footwear category (Note: 'footwear' table was not in your SQL dump, ensure it exists if used)
    function get_footwear_category(){
        global $conn;
        $query = "SELECT * FROM footwear WHERE footwear.footwear_category_status = 1"; // Assuming 'footwear' table
        return $result = mysqli_query($conn, $query);
    }
    // ... (other specific category getters like jewelry, perfume, cosmetics, glasses, bags - ensure these tables exist if used) ...

    // get deal of the day
    function get_deal_of_day(){
        global $conn;
        $query = "SELECT * FROM deal_of_the_day WHERE deal_of_the_day.deal_status = 1";
        return $result = mysqli_query($conn, $query);
    }

    /**
     * Fetches products for the "All Products" section with pagination, 
     * including their average rating and review count.
     *
     * @param int $offset The starting point for fetching products.
     * @param int $limit The number of products to fetch.
     * @return mysqli_result|false The result set of the products or false on failure.
     */
    function get_new_products(int $offset, int $limit) { // This is the function for "All Products"
        global $conn; 

        if (!$conn) {
            error_log("Database connection not available in get_new_products.");
            return false;
        }

        $sql = "SELECT
                    p.product_id, p.product_catag, p.product_title, p.product_price, p.product_desc,
                    p.product_date, p.product_img, p.product_left, p.product_author, p.category_id,
                    p.section_id, p.discounted_price, p.image_1, p.image_2, 
                    p.created_at AS product_created_at, p.updated_at AS product_updated_at, p.status AS product_status,
                    AVG(r.rating) AS average_rating,
                    COUNT(DISTINCT r.review_id) AS total_reviews
                FROM
                    products p
                LEFT JOIN
                    reviews r ON p.product_id = r.product_id AND r.status = 'approved'
                WHERE
                    p.status = 1 -- Assuming 1 means active product
                GROUP BY
                    p.product_id, p.product_catag, p.product_title, p.product_price, p.product_desc,
                    p.product_date, p.product_img, p.product_left, p.product_author, p.category_id,
                    p.section_id, p.discounted_price, p.image_1, p.image_2, 
                    p.created_at, p.updated_at, p.status
                ORDER BY
                    p.product_id DESC 
                LIMIT ?, ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Error preparing statement for get_new_products (with reviews): " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("ii", $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            error_log("Error executing statement for get_new_products (with reviews): " . $stmt->error);
            return false;
        }
        return $result;
    }
    
    // get product through id from product table 
    function get_product($id){
        global $conn;
        // Sanitize ID to prevent SQL injection if it's not already an integer
        $product_id = (int)$id; 
        $query = "SELECT * FROM products WHERE products.product_id = $product_id";
        return $result = mysqli_query($conn, $query);
    }

    // get specific category items
    function get_items_by_category_items($category){
        global $conn;
        // Sanitize category input if it's coming directly from user input
        $safe_category = mysqli_real_escape_string($conn, $category);
        $query = "SELECT * FROM products WHERE products.product_catag = '$safe_category' AND products.status = 1";
        return $result = mysqli_query($conn, $query);
    }

    // get specific deal of the day by id
    function get_deal_of_day_by_id($id){
        global $conn; 
        $deal_id = (int)$id;
        $query = "SELECT * FROM deal_of_the_day WHERE deal_id = ? AND deal_status = 1";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $deal_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            return $result;
        } else {
            error_log("Error preparing statement in get_deal_of_day_by_id: " . $conn->error);
            return false;
        }
    }

    /**
     * Fetches the top N best-selling products, including their average rating and review count.
     *
     * @param mysqli $conn The database connection object.
     * @param int $limit The number of top products to fetch.
     * @return mysqli_result|false The result set of the best-selling products or false on failure.
     */
    function get_best_selling_products(mysqli $conn, int $limit = 5) { // Defaulted to 5 as in previous examples
        $sql = "SELECT
                    p.product_id, p.product_title, p.product_desc, p.product_img, p.product_price,
                    p.discounted_price, p.category_id,
                    SUM(oi.quantity) AS total_quantity_sold,
                    AVG(r.rating) AS average_rating,
                    COUNT(DISTINCT r.review_id) AS total_reviews
                FROM
                    products p
                JOIN
                    order_items oi ON p.product_id = oi.product_id
                LEFT JOIN
                    reviews r ON p.product_id = r.product_id AND r.status = 'approved'
                WHERE
                    p.status = 1
                GROUP BY
                    p.product_id, p.product_title, p.product_desc, p.product_img, p.product_price, p.discounted_price, p.category_id
                ORDER BY
                    total_quantity_sold DESC
                LIMIT ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Error preparing statement for best selling products (with reviews): " . $conn->error);
            return false;
        }
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            error_log("Error executing statement for best selling products (with reviews): " . $stmt->error);
            return false;
        }
        return $result;
    }

    /**
     * Generates star icons based on a rating.
     *
     * @param float|null $rating The average rating (e.g., 4.5). Can be null if no rating.
     * @param int $total_stars The total number of stars to display (e.g., 5).
     * @return string HTML string for star icons.
     */
    function display_stars(float $rating = null, int $total_stars = 5): string {
        if ($rating === null) {
            return "<span class='no-rating' style='font-size: 0.8em; color: #777;'>(No ratings yet)</span>";
        }
        $rating = max(0, min($total_stars, (float)$rating)); 
        
        $stars_html = "<div class='star-rating' title='Rating: " . number_format($rating, 1) . " out of {$total_stars}'>";
        
        $full_stars = floor($rating);
        $half_star = ($rating - $full_stars) >= 0.4 && ($rating - $full_stars) < 0.9; 
        $empty_stars = $total_stars - $full_stars - ($half_star ? 1 : 0);

        for ($i = 0; $i < $full_stars; $i++) {
            $stars_html .= "<ion-icon name='star'></ion-icon>";
        }
        if ($half_star) {
            $stars_html .= "<ion-icon name='star-half-outline'></ion-icon>";
        }
        for ($i = 0; $i < $empty_stars; $i++) {
            $stars_html .= "<ion-icon name='star-outline'></ion-icon>";
        }
        $stars_html .= "</div>";
        return $stars_html;
    }

?>
