<?php
// This is a standalone example file: shopbycategory_pink.php
// In a real application, you would include your config file here
require_once 'includes/config.php';
// And fetch categories from your database.

// Sample category data for 3 categories (matching the provided image)
$featured_categories = [
    [
        'id' => 1,
        'name' => 'Cute Apparel',
        // 'image' property is no longer used for display, but kept for data structure
        'image' => 'placeholder_apparel.png',
        'block_bg_color' => '#F5D9E8', // A light pink, adjust as needed
        'link' => 'search.php?search_query=' . urlencode('Cute Apparel') // Updated link
    ],
    [
        'id' => 2,
        'name' => 'Sweet Accessories',
        'image' => 'placeholder_accessories.png',
        'block_bg_color' => '#F3E0EC', // A slightly different light pink, adjust
        'link' => 'search.php?search_query=' . urlencode('Sweet Accessories') // Updated link
    ],
    [
        'id' => 3,
        'name' => 'Vinyl',
        'image' => 'placeholder_vinyl.png',
        'block_bg_color' => '#EDE6ED', // A very light, almost lavender pink, adjust
        'link' => 'search.php?search_query=' . urlencode('Vinyl') // Updated link
    ]
];

// Define your theme's pink color
$themePinkColor = '#D9468C'; // Main pink for text (adjust to match image, e.g. #C7377A from image text)
$themePageBg = '#FCF5F9';    // Very light pink for the page background (adjust to match image)


// Function to darken a hex color (optional, if not using Tailwind's hover variants)
function darken_color($hex, $percent) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, $r - $r * ($percent / 100)));
    $g = max(0, min(255, $g - $g * ($percent / 100)));
    $b = max(0, min(255, $b - $b * ($percent / 100)));

    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) .
           str_pad(dechex($g), 2, '0', STR_PAD_LEFT) .
           str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}
$hoverTextPinkColor = darken_color($themePinkColor, 10); // Darker pink for text hover

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop by Category - Merch Shop (Image Match)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom styles to match the image */
        body {
            font-family: 'Inter', sans-serif; /* Tailwind's default font, or your site's font */
            background-color: <?php echo $themePageBg; ?>;
        }
        .category-section-title {
            color: <?php echo $themePinkColor; ?>;
            font-weight: 700; /* Bold */
            font-size: 1.75rem; /* approx 28px, adjust as needed */
            text-align: center;
            margin-bottom: 1rem; /* Space below title */
            position: relative;
            padding-bottom: 0.5rem;
        }
        .category-section-title::after { /* Underline for the title */
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 0;
            width: 50px; /* Width of the underline */
            height: 2px; /* Thickness of the underline */
            background-color: <?php echo $themePinkColor; ?>;
            border-radius: 1px;
        }

        .category-card-link {
            display: block;
            background-color: #ffffff; /* White background for the bottom part */
            border-radius: 0.5rem; /* rounded-lg */
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); /* shadow-lg */
            overflow: hidden;
            text-decoration: none;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .category-card-link:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); /* shadow-xl */
        }

        .category-block {
            height: 200px; /* Adjust height as needed to match image proportions */
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 1rem;
        }
        .category-block-text {
            font-weight: 700; /* Bold */
            font-size: 1.5rem; /* approx 24px, adjust as needed */
            color: <?php echo $themePinkColor; ?>;
        }
        .category-card-link:hover .category-block-text {
             color: <?php echo $hoverTextPinkColor; ?>;
        }

        .category-name-below {
            padding: 0.75rem; /* p-3 */
            text-align: center;
            font-weight: 600; /* semibold */
            font-size: 1.125rem; /* text-lg, approx 18px */
            color: <?php echo $themePinkColor; ?>;
        }
         .category-card-link:hover .category-name-below {
             color: <?php echo $hoverTextPinkColor; ?>;
        }

        /* Placeholder Nav and Footer styling */
        .navbar-brand-text:hover { color: <?php echo $themePinkColor; ?>; }
        .footer-bg { background-color: <?php echo darken_color($themePinkColor, 40); ?>; } /* Darker pink for footer */

    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <a href="index.php" class="text-2xl font-bold text-gray-800 navbar-brand-text">MerchShop</a>
                </div>
                <div>
                    <a href="#" class="px-4 py-2 text-gray-700 hover:text-pink-600">Home</a>
                    <a href="#" class="px-4 py-2 text-gray-700 hover:text-pink-600">Products</a>
                    <a href="#" class="px-4 py-2 text-gray-700 hover:text-pink-600">Cart</a>
                </div>
            </div>
        </div>
    </nav>

    <section class="py-10 md:py-14">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="category-section-title">
                Shop Our Popular Categories
            </h2>
            <?php if (!empty($featured_categories)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 md:gap-8 mt-8">
                    <?php foreach ($featured_categories as $category): ?>
                        <div class="category-card-wrapper">
                            <a href="<?php echo htmlspecialchars($category['link']); ?>" class="category-card-link group">
                                <div class="category-block" style="background-color: <?php echo htmlspecialchars($category['block_bg_color']); ?>;">
                                    <h3 class="category-block-text">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </h3>
                                </div>
                                <div class="category-name-below">
                                    <h4> <?php echo htmlspecialchars($category['name']); ?>
                                    </h4>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-500 mt-8">Our categories are being updated. Please check back soon!</p>
            <?php endif; ?>
        </div>
    </section>

    <footer class="footer-bg text-white py-10 mt-16">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; <?php echo date("Y"); ?> MerchShop. All Rights Reserved.</p>
            <p class="text-sm text-pink-200 mt-1">Designed with <i class="fas fa-heart"></i> by You!</p>
        </div>
    </footer>

</body>
</html>
