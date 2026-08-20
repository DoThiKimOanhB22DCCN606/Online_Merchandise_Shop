<?php
    include_once('./includes/headerNav.php'); // Should include session_start() and config.php
    // Any specific PHP logic for this page can go here if needed in the future.
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - <?php echo htmlspecialchars($_SESSION['web-name'] ?? 'Merch Shop'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* It's highly recommended to move these styles to an external CSS file */
        body {
            background-color: #f8f9fa; /* Light background for the page */
            color: #333;
            font-family: 'Inter', sans-serif; /* Assuming Inter font from previous examples */
        }
        .about-page-wrapper {
            padding-top: 2rem;
            padding-bottom: 3rem;
        }
        .about-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 1rem;
            background-color: #D19C97; /* Theme color accent */
            color: #fff;
            border-radius: 0 0 30px 30px; /* Soft curve at the bottom */
        }
        .about-header .page-title {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .about-header .page-subtitle {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .about-section {
            background-color: #fff;
            padding: 2.5rem; /* More padding inside sections */
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.07);
            margin-bottom: 2.5rem;
        }
        .about-section h2.section-title {
            font-size: 2rem;
            font-weight: 600;
            color: #D19C97; /* Theme color for section titles */
            text-align: center;
            position: relative;
        }
        .about-section h2.section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background-color: #D19C97;
            margin: 0.5rem auto 0;
        }
        .about-section p, .about-section ul {
            font-size: 1rem;
            line-height: 1.7;
            color: #555;
        }
        .about-section ul {
            padding-left: 1.5rem;
            margin-top: 1rem;
        }
        .about-section ul li {
            margin-bottom: 0.5rem;
        }

        .team-section .team-member {
            text-align: center;
            margin-bottom: 2rem;
        }
        .team-member img.team-member-img { /* Added class for team images */
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 4px solid #f0f0f0; /* Light border around image */
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .team-member h4 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }
        .team-member p.role {
            font-size: 0.9rem;
            color: #D19C97; /* Theme color for role */
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .values-section .value-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        .values-section .value-item i { /* For Font Awesome icons */
            font-size: 1.8rem;
            color: #D19C97;
            margin-right: 1rem;
            margin-top: 0.25rem;
            width: 30px; /* Fixed width for alignment */
        }
        .values-section .value-item div h5 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.3rem;
        }

        .about-image-placeholder {
            width: 100%;
            height: 250px;
            background-color: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #6c757d;
            font-style: italic;
            margin-bottom: 1.5rem;
            border: 1px dashed #ced4da;
        }

        @media (max-width: 767px) {
            .about-header .page-title {
                font-size: 2.2rem;
            }
            .about-header .page-subtitle {
                font-size: 1rem;
            }
            .about-section {
                padding: 1.5rem;
            }
            .about-section h2.section-title {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>

<header>
  <?php require_once './includes/desktopnav.php'; ?>
  <?php require_once './includes/mobilenav.php'; ?>
</header>
<hr class="d-none d-lg-block">

<div class="container about-page-wrapper">
    <div class="about-header">
        <h1 class="page-title">About <?php echo htmlspecialchars($_SESSION['web-name'] ?? 'Our Shop'); ?></h1>
        <p class="page-subtitle">
            Welcome to the official merch store for Sabrina Carpenter! Discover exclusive apparel, music, and unique collectibles inspired by Sabrina and her artistry.
        </p>
    </div>

    <div class="about-section">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2  style="color: dark; text-align:left; margin-left:0;">Our Story</h2>
                <p>
                    Born from a passion for Sabrina Carpenter's music and her incredible connection with fans, 
                    this shop was created to bring you closer to the world of Sabrina. We started as a small idea, fueled by fans, 
                    and have grown into a dedicated space where "Carpenters" can find official, high-quality merchandise.
                </p>
                <p>
                    Every item in our store is designed with love and attention to detail, reflecting Sabrina's unique style and the themes of her music. 
                    We aim to provide a seamless and enjoyable shopping experience for fans worldwide.
                </p>
            </div>
            <div class="col-md-6">
                <img src="img/sabcommunity.jpg">
            </div>
        </div>
    </div>

    <div class="about-section">
        <h2 >Our Mission</h2>
        <p class="text-center" style="max-width: 800px; margin-left:auto; margin-right:auto;">
            Our mission is simple: to provide Sabrina Carpenter fans with authentic, high-quality merchandise that they can cherish. 
            We strive to create a community hub where fans can celebrate their love for Sabrina and find unique items that resonate with her music and message. 
            We are committed to excellent customer service and ensuring every fan feels connected.
        </p>
    </div>

   
    <div class="about-section values-section">
        <h2 >Our Values</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="value-item">
                    <i class="fas fa-heart"></i> 
                    <div>
                        <h5>Authenticity</h5>
                        <p>All our merchandise is officially licensed and approved, ensuring genuine quality for true fans.</p>
                    </div>
                </div>
                <div class="value-item">
                    <i class="fas fa-star"></i> 
                    <div>
                        <h5>Quality</h5>
                        <p>We are committed to providing high-quality products that fans can enjoy for years to come.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="value-item">
                    <i class="fas fa-users"></i> 
                    <div>
                        <h5>Fan Focus</h5>
                        <p>Our fans are at the heart of everything we do. We strive to create a positive and engaging experience.</p>
                    </div>
                </div>
                <div class="value-item">
                    <i class="fas fa-handshake"></i> 
                    <div>
                        <h5>Community</h5>
                        <p>We aim to foster a sense of community among Sabrina Carpenter fans through shared passion and exclusive items.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include_once('./includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
