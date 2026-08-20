<?php
    include_once('./includes/headerNav.php'); // Should include session_start() and config.php
    // Any specific PHP logic for this page can go here.
    // For example, handling success/error messages from form submission if not done via AJAX.
    $form_message = '';
    $form_message_type = '';

    if (isset($_SESSION['contact_form_status'])) {
        if ($_SESSION['contact_form_status'] == 'success') {
            $form_message = "Thank you for your message! We'll get back to you soon.";
            $form_message_type = 'success';
        } else {
            $form_message = "Sorry, there was an error sending your message. Please try again later.";
            $form_message_type = 'danger';
        }
        unset($_SESSION['contact_form_status']); // Clear the session variable
    }
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo htmlspecialchars($_SESSION['web-name'] ?? 'Merch Shop'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* It's highly recommended to move these styles to an external CSS file */
        body {
            background-color: #f8f9fa; /* Light background for the page */
        }
        .contact-page-wrapper {
            padding-top: 2rem;
            padding-bottom: 3rem;
        }
        .contact-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .contact-header .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #343a40;
            margin-bottom: 0.5rem;
        }
        .contact-header .page-subtitle {
            font-size: 1.1rem;
            color: #6c757d;
            max-width: 600px;
            margin: 0 auto;
        }

        .contact-form-section, .contact-info-section {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .contact-form-section h3, .contact-info-section h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }
        .form-control {
            border-radius: 0.3rem;
            border: 1px solid #ced4da;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
        }
        .form-control:focus {
            border-color: #D19C97; /* Theme color */
            box-shadow: 0 0 0 0.2rem rgba(209, 156, 151, 0.25);
        }
        textarea.form-control {
            min-height: 120px;
        }
        .btn-submit-contact {
            background-color: #D19C97; /* Theme color */
            border-color: #D19C97;
            color: #fff;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 0.3rem;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .btn-submit-contact:hover {
            background-color: #c5837c;
            border-color: #bf736a;
        }

        .contact-info-list {
            list-style: none;
            padding-left: 0;
        }
        .contact-info-list li {
            display: flex;
            align-items: flex-start; /* Align icon with first line of text */
            margin-bottom: 1rem;
            font-size: 1rem;
            color: #555;
        }
        .contact-info-list li i { /* Font Awesome icons */
            font-size: 1.2rem;
            color: #D19C97; /* Theme color */
            margin-right: 1rem;
            width: 20px; /* Fixed width for icon alignment */
            text-align: center;
            margin-top: 0.15rem; /* Align icon slightly better with text */
        }
        .contact-info-list li strong {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .map-placeholder {
            width: 100%;
            height: 300px;
            background-color: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #6c757d;
            font-style: italic;
            border: 1px dashed #ced4da;
        }
        .alert-custom {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.3rem;
        }
        .alert-custom-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        .alert-custom-danger {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }

        @media (max-width: 767px) {
            .contact-form-section, .contact-info-section {
                padding: 1.5rem;
            }
            .contact-header .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<header>
  <?php require_once './includes/desktopnav.php'; ?>
  <?php require_once './includes/mobilenav.php'; ?>
</header>
<hr class="d-none d-lg-block"> <div class="container contact-page-wrapper">
    <div class="contact-header">
        <h1 class="page-title">Get In Touch</h1>
        <p class="page-subtitle">
            We'd love to hear from you! Whether you have a question about our products, an order, or anything else, our team is ready to answer all your questions.
        </p>
    </div>

    <?php if ($form_message): ?>
        <div class="alert-custom <?php echo ($form_message_type == 'success') ? 'alert-custom-success' : 'alert-custom-danger'; ?>" role="alert">
            <?php echo htmlspecialchars($form_message); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-7 mb-4 mb-lg-0">
            <div class="contact-form-section">
                <h3>Send Us a Message</h3>
                <form id="contactForm" name="sentMessage" action="mail/contact.php" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" required 
                               data-validation-required-message="Please enter your name.">
                        <p class="help-block text-danger"></p> </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="your.email@example.com" required
                               data-validation-required-message="Please enter your email address.">
                        <p class="help-block text-danger"></p>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject of your message" required
                               data-validation-required-message="Please enter a subject.">
                        <p class="help-block text-danger"></p>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" placeholder="Your message here..." required
                                  data-validation-required-message="Please enter your message."></textarea>
                        <p class="help-block text-danger"></p>
                    </div>
                    <div id="success"></div> <button type="submit" class="btn btn-submit-contact">Send Message</button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="contact-info-section">
                <h3>Contact Information</h3>
                <ul class="contact-info-list">
                 
                    <li>
                        <i class="fas fa-phone-alt"></i>
                        <div>
                            <strong>Phone:</strong>
                            +1 (555) 123-4567 <br>
                            
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <div>
                            <strong>Email:</strong>
                            support@merchshop.com <br>
                           
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include_once('./includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
