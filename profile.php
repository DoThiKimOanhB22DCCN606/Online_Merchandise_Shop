<?php
   include_once('./includes/headerNav.php');
   //this restriction will secure the pages path injection
   if(!(isset($_SESSION['id']))){
      header("location:index.php?UnathorizedUser");
     }
    // Ensure $conn is available, if not, include config.php
    if (!isset($conn) || !$conn instanceof mysqli) {
        include_once('./includes/config.php');
    }

    // Fetch user data
    $user_id = $_SESSION['id'];
    $sql8 ="SELECT * FROM customer WHERE customer_id=?;";
    $stmt8 = $conn->prepare($sql8);
    $stmt8->bind_param("i", $user_id);
    $stmt8->execute();
    $result8 = $stmt8->get_result();
    $row8 = $result8->fetch_assoc();
    $stmt8->close();

    // Update session variables (it's good practice to re-fetch from DB on profile page load)
    $_SESSION['customer_name'] = $row8['customer_fname'] ?? 'N/A';
    $_SESSION['customer_email'] = $row8['customer_email'] ?? 'N/A';
    $_SESSION['customer_phone'] = $row8['customer_phone'] ?? 'N/A';
    $_SESSION['customer_address'] = $row8['customer_address'] ?? 'N/A';
    $_SESSION['customer_role'] = $row8['customer_role'] ?? 'normal';


    // Handle form submissions for updates
    $update_message = '';
    $update_success = false;

    if(isset($_POST['save_profile'])){
        if(!empty($_POST['name']) && !empty($_POST['email'])){
            $newName = $_POST['name'];
            $newEmail = $_POST['email'];
            $updateSql = "UPDATE customer SET customer_fname = ?, customer_email = ? WHERE customer_id = ?";
            $stmtUpdate = $conn->prepare($updateSql);
            $stmtUpdate->bind_param("ssi", $newName, $newEmail, $user_id);
            if ($stmtUpdate->execute()) {
                $_SESSION['customer_name'] = $newName; // Update session immediately
                $_SESSION['customer_email'] = $newEmail;
                $update_message = "Profile updated successfully!";
                $update_success = true;
            } else {
                $update_message = "Error updating profile: " . $stmtUpdate->error;
            }
            $stmtUpdate->close();
        } else {
            $update_message = "Name and Email cannot be empty for profile update.";
        }
    } elseif (isset($_POST['save_address'])) {
        if(!empty($_POST['address'])){
            $newAddress = $_POST['address'];
            $updateSql = "UPDATE customer SET customer_address = ? WHERE customer_id = ?";
            $stmtUpdate = $conn->prepare($updateSql);
            $stmtUpdate->bind_param("si", $newAddress, $user_id);
            if ($stmtUpdate->execute()) {
                $_SESSION['customer_address'] = $newAddress;
                $update_message = "Address updated successfully!";
                $update_success = true;
            } else {
                $update_message = "Error updating address: " . $stmtUpdate->error;
            }
            $stmtUpdate->close();
        } else {
            $update_message = "Address cannot be empty.";
        }
    } elseif (isset($_POST['save_contact'])) {
        if(!empty($_POST['number'])){
            $newNumber = $_POST['number'];
            $updateSql = "UPDATE customer SET customer_phone = ? WHERE customer_id = ?";
            $stmtUpdate = $conn->prepare($updateSql);
            $stmtUpdate->bind_param("si", $newNumber, $user_id);
            if ($stmtUpdate->execute()) {
                $_SESSION['customer_phone'] = $newNumber;
                $update_message = "Contact number updated successfully!";
                $update_success = true;
            } else {
                $update_message = "Error updating contact: " . $stmtUpdate->error;
            }
            $stmtUpdate->close();
        } else {
            $update_message = "Contact number cannot be empty.";
        }
    }
    // $conn->close(); // Close connection at the very end of the script, or let PHP handle it.
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo htmlspecialchars($_SESSION['web-name'] ?? 'Merch Shop'); ?></title>
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
      integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65"
      crossorigin="anonymous"
    />
    <style>
      /* General Page Styles */
      body {
        background-color: #f4f6f9; /* Light grey background */
        color: #333;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      }
      .profile-page-container {
         padding-top: 2rem;
         padding-bottom: 3rem;
      }
      .profile-header {
         text-align: center;
         margin-bottom: 2.5rem;
         font-size: 2.25rem;
         font-weight: 600;
         color: #343a40;
      }
      .profile-header .role-highlight {
         color: #D19C97; /* Theme color */
         font-weight: 700;
      }

      /* Card Styling */
      .profile-card {
        background-color: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 0.5rem; /* Softer radius */
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        height: 100%; /* Make cards in a row same height */
        display: flex;
        flex-direction: column;
      }
      .profile-card .card-body {
        padding: 1.5rem; /* More padding */
        display: flex;
        flex-direction: column;
        flex-grow: 1; /* Allow card body to grow */
      }
      .profile-card .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1rem;
      }
      .profile-card .card-text {
        color: #555;
        margin-bottom: 0.75rem; /* Space between text lines */
        line-height: 1.6;
        word-break: break-word; /* Prevent long text from breaking layout */
      }
      .profile-card .card-text strong {
        color: #333;
      }

      /* Buttons */
      .profile-card .btn {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        border-radius: 0.3rem;
        text-transform: uppercase;
        font-weight: 500;
        margin-top: auto; /* Push buttons to the bottom of card body */
      }
      .profile-card .btn-primary {
        background-color: #D19C97;
        border-color: #D19C97;
        color: #fff;
      }
      .profile-card .btn-primary:hover {
        background-color: #c5837c;
        border-color: #c5837c;
      }
      .profile-card .btn-secondary { /* For edit/save buttons */
        background-color: #6c757d;
        border-color: #6c757d;
        color: #fff;
        margin-top: 0.5rem; /* Space above edit/save */
      }
      .profile-card .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
      }
      .profile-card .admin-panel-btn { /* Specific style for admin panel button */
          background-color: #28a745; /* Green */
          border-color: #28a745;
      }
       .profile-card .admin-panel-btn:hover {
          background-color: #218838;
          border-color: #1e7e34;
      }


      /* Edit Forms */
      .edit-form {
        display: none; /* Hidden by default, toggled by JS */
        margin-top: 1rem;
        padding: 1rem;
        background-color: #f9f9f9;
        border-radius: 0.3rem;
        border: 1px solid #eee;
      }
      .edit-form .form-control {
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
      }
      .edit-form .btn-save { /* Specific save button if needed, or use .btn-secondary */
         width: 100%;
      }

      /* Alert Messages */
      .update-alert {
        margin-top: 1rem;
        margin-bottom: 1rem;
        padding: 0.75rem 1.25rem;
        border-radius: 0.25rem;
      }
      .alert-success {
        color: #0f5132;
        background-color: #d1e7dd;
        border-color: #badbcc;
      }
      .alert-danger {
        color: #842029;
        background-color: #f8d7da;
        border-color: #f5c2c7;
      }

      /* Responsive adjustments */
      @media (max-width: 767px) {
        .profile-header {
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
        }
        .profile-card .card-body {
            padding: 1rem;
        }
        .profile-card .card-title {
            font-size: 1.1rem;
        }
      }
   </style>
</head>
<body>
<header>
  <?php require_once './includes/desktopnav.php' ?>
  <?php require_once './includes/mobilenav.php'; ?>
</header>
<hr>

<div class="container profile-page-container">
    <h1 class="profile-header">
        Hello, <span class="role-highlight">
        <?php echo htmlspecialchars(($_SESSION['customer_role'] == 'admin') ? 'Admin' : $_SESSION['customer_name']); ?>
        </span>!
    </h1>

    <?php if (!empty($update_message)): ?>
        <div class="alert <?php echo $update_success ? 'alert-success' : 'alert-danger'; ?> update-alert" role="alert">
            <?php echo htmlspecialchars($update_message); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="profile-card">
                <div class="card-body">
                    <h5 class="card-title">My Profile</h5>
                    <p class="card-text"><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['customer_name']); ?></p>
                    <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['customer_email']); ?></p>
                    <p class="card-text"><strong>Role:</strong> <span class="text-capitalize"><?php echo htmlspecialchars($_SESSION['customer_role']); ?></span></p>
                    
                    <button class="btn btn-secondary w-100 mt-2" onclick="toggleEditForm('profileEditForm')">Edit Profile</button>
                    <div class="edit-form" id="profileEditForm">
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                            <div class="mb-3">
                                <label for="nameInput" class="form-label">New Name</label>
                                <input type="text" name="name" id="nameInput" class="form-control" placeholder="Enter new name" value="<?php echo htmlspecialchars($_SESSION['customer_name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="emailInput" class="form-label">New Email</label>
                                <input type="email" name="email" id="emailInput" class="form-control" placeholder="Enter new email" value="<?php echo htmlspecialchars($_SESSION['customer_email']); ?>" required>
                            </div>
                            <button type="submit" name="save_profile" class="btn btn-primary w-100 btn-save">Save Changes</button>
                        </form>
                    </div>
                    <?php if($_SESSION['customer_role'] == 'admin'): ?>            
                        <a id='admin' href='admin/dashboard.php' class="btn btn-primary admin-panel-btn w-100 mt-2">Visit Admin Panel</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="profile-card">
                <div class="card-body">
                    <h5 class="card-title">My Orders</h5>
                    <p class="card-text">View your purchase history and track current orders.</p>
                    <a href="order_history.php" class="btn btn-primary w-100">View Order History</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="profile-card">
                <div class="card-body">
                    <h5 class="card-title">My Address</h5>
                    <p class="card-text"><?php echo !empty($_SESSION['customer_address']) ? htmlspecialchars($_SESSION['customer_address']) : 'No address on file.'; ?></p>
                    <button class="btn btn-secondary w-100 mt-2" onclick="toggleEditForm('addressEditForm')">Edit Address</button>
                    <div class="edit-form" id="addressEditForm">
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                            <div class="mb-3">
                                <label for="addressInput" class="form-label">New Address</label>
                                <textarea name="address" id="addressInput" class="form-control" placeholder="Enter new address" rows="3" required><?php echo htmlspecialchars($_SESSION['customer_address']); ?></textarea>
                            </div>
                            <button type="submit" name="save_address" class="btn btn-primary w-100 btn-save">Save Address</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        
    </div>
</div>

<?php include_once('./includes/footer.php'); ?>

<script>
    // JavaScript to toggle edit forms
    function toggleEditForm(formId) {
        var form = document.getElementById(formId);
        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block";
        } else {
            form.style.display = "none";
        }
    }

    // Auto-hide success/error messages after a few seconds
    window.onload = function() {
        const alerts = document.querySelectorAll('.update-alert');
        alerts.forEach(function(alert) {
            if (alert.textContent.trim() !== "") { // Check if alert has content
                setTimeout(function() {
                    // Simple fade out effect
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500); // Remove from layout after fade
                }, 5000); // Hide after 5 seconds
            } else {
                alert.style.display = 'none'; // Hide empty alerts immediately
            }
        });
    };
</script>
</body>
</html>
