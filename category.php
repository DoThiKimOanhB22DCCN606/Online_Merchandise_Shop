<!--  -->
<?php include_once('./includes/headerNav.php'); ?>
<?php require_once './includes/mobilenav.php'; ?>

<?php
// work on getting string with spaces from url
$category_ID = "";
if (isset($_GET['category'])) {
  $category_ID = $_GET['category'];
}


$items = get_items_by_category_items($category_ID);

?>

<div class="overlay" data-overlay></div>
<!--
    - HEADER
  -->
<header>
  <?php require_once './includes/desktopnav.php' ?>
  <?php require_once './includes/mobilenav.php'; ?>
</header>

<!--
    - MAIN
  -->

<main>

  <div class="product-container">
    <div class="container">
      <!--
          - SIDEBAR
        -->
      <!-- CATEGORY SIDE BAR MOBILE MENU -->
    <?php require_once 'includes/categorysidebar.php' ?>

    </div>
  </div>

  <!--
      - TESTIMONIALS, CTA & SERVICE
    -->

  <!--
      - BLOG
    -->

</main>

<?php require_once './includes/footer.php'; ?>
