<?php

// --- Composer Autoloader ---
require __DIR__ . '/vendor/autoload.php'; 

// --- Use PayPal SDK Namespaces ---
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;    // For testing
use PayPalCheckoutSdk\Core\ProductionEnvironment; // For live
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpException; // Import HttpException

// --- PHPMailer Namespaces --- ADD THIS
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException; // Alias PHPMailer's Exception

// --- Configuration ---
// Load your PayPal API Credentials securely
$clientId = getenv('PAYPAL_CLIENT_ID') ?: 'AYn-gDaUjFZxow-e1hYvzPZIO2VnHTnHlbvM8JYRBi_JTGi-KXpkHyjRinCNfs9Kqgs7rlGY4i35D8Tr'; //
$clientSecret = getenv('PAYPAL_CLIENT_SECRET') ?: 'EDHK4qpD7nKYc66qyw8_4V46CAWdc1zk4yyG6qP_qEfNG4-dwsLxkexryd8BH9_eeRh6bjtkV5HyY5Gp'; //

// --- Database Configuration ---
$dbHost = getenv('DB_HOST') ?: 'localhost'; //
$dbName = getenv('DB_NAME') ?: 'db_merchshop'; //
$dbUser = getenv('DB_USER') ?: 'root'; //
$dbPass = getenv('DB_PASS') ?: ''; //
$dbCharset = 'utf8mb4'; //

// --- SMTP/Email Configuration
$smtpHost = 'smtp.gmail.com'; // e.g., 'smtp.gmail.com'
$smtpUsername = '149oanh@gmail.com';
$smtpPassword = 'exts zwiz caba prmf';
$smtpPort = 587; // Or 465 for SSL
$smtpSecure = PHPMailer::ENCRYPTION_STARTTLS; // Or PHPMailer::ENCRYPTION_SMTPS
$emailFromName = 'Sabrina Merch Shop';
$emailFromAddress = 'no-reply@yourmerchshop.com';


// --- Determine PayPal Environment ---
$isProduction = false; // Set to true for live

if ($isProduction) {
    $environment = new ProductionEnvironment($clientId, $clientSecret); //
    // In production, disable error display completely and rely on logs
    ini_set('display_errors', 0); //
    error_reporting(0); // Or a level like E_ALL & ~E_DEPRECATED & ~E_STRICT //
    ini_set('log_errors', 1); // Ensure errors are logged //
    // ini_set('error_log', '/path/to/your/production_php_errors.log'); // Optional: Set specific log file
} else {
    // Use Sandbox environment and credentials for testing
    $environment = new SandboxEnvironment($clientId, $clientSecret); //
    // Keep errors visible for debugging in sandbox, but hide Deprecated/Notices from output
    ini_set('display_errors', 1); //
    // Report all errors except Deprecated and Notice level messages
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE); //
    ini_set('log_errors', 1); // Still log all errors, including deprecated/notices //
}

$client = new PayPalHttpClient($environment); //

// --- Start Session ---
if (session_status() == PHP_SESSION_NONE) {
    session_start(); //
}

// --- Set Header ---
header('Content-Type: application/json'); //

// --- Default Response ---
$response = ['success' => false, 'message' => 'Verification initialization failed.']; //

// --- Get Data from Client ---
$requestBody = file_get_contents('php://input'); //
$data = json_decode($requestBody); //

// Basic validation
if (!$data || !isset($data->orderID) || !isset($data->email_address)) {
    $response['message'] = 'Invalid or incomplete data received from client.'; //
    error_log("PayPal Verify Error: Invalid data received. Payload: " . $requestBody); //
    echo json_encode($response);
    exit;
}

$orderID = filter_var($data->orderID, FILTER_SANITIZE_FULL_SPECIAL_CHARS); //
$customerEmail = filter_var($data->email_address, FILTER_VALIDATE_EMAIL); //
$customerFirstName = filter_var(isset($data->first_name) ? $data->first_name : 'Valued Customer', FILTER_SANITIZE_FULL_SPECIAL_CHARS); //


if (!$customerEmail) {
     $response['message'] = 'Invalid email address received from client.'; //
     error_log("PayPal Verify Error: Invalid email. Payload: " . $requestBody); //
     echo json_encode($response);
     exit;
}

// --- Get Expected Amount/Currency from Session ---
$expectedAmount = null; //
$expectedCurrency = 'USD'; //

if (isset($_SESSION['cart_total']) && is_numeric($_SESSION['cart_total'])) {
    $expectedAmount = number_format((float)$_SESSION['cart_total'], 2, '.', ''); //
}
if (isset($_SESSION['currency_code']) && !empty($_SESSION['currency_code'])) {
    $expectedCurrency = filter_var($_SESSION['currency_code'], FILTER_SANITIZE_FULL_SPECIAL_CHARS); //
}

if ($expectedAmount === null || $expectedAmount <= 0) {
     $response['message'] = 'Could not retrieve valid expected cart total from session.'; //
     error_log("PayPal Verify Error: Cart total missing or invalid in session for Order ID: " . $orderID . ". Expected Amount derived as: " . var_export($expectedAmount, true)); //
     echo json_encode($response);
     exit;
}

// --- Call PayPal API ---
$request = new OrdersGetRequest($orderID); //

try {
    $apiResponse = $client->execute($request); //
    $orderDetails = $apiResponse->result; //

    $paymentStatus = $orderDetails->status; //
    $paidAmount = null;
    if (isset($orderDetails->purchase_units[0]->amount->value)) {
        $paidAmount = $orderDetails->purchase_units[0]->amount->value; //
    }
    $paidCurrency = null;
    if (isset($orderDetails->purchase_units[0]->amount->currency_code)) {
         $paidCurrency = $orderDetails->purchase_units[0]->amount->currency_code; //
    }

    $transactionId = null;
    if (isset($orderDetails->purchase_units[0]->payments->captures[0]->id)) {
        $transactionId = $orderDetails->purchase_units[0]->payments->captures[0]->id; //
    } else {
        error_log("PayPal Verify Warning: Capture ID not found in expected location for Order ID: " . $orderID); //
        $transactionId = $orderDetails->id; // Fallback //
    }


    if ($paymentStatus == 'COMPLETED') { //
         if ($paidAmount == $expectedAmount && $paidCurrency == $expectedCurrency) { //
            // --- SUCCESS ---
            $last_order_id = null; // Initialize last_order_id
            $order_items_details_for_email = []; // ADD THIS: For email content

            try {
                $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=$dbCharset"; //
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //
                    PDO::ATTR_EMULATE_PREPARES   => false, //
                ];
                $pdo = new PDO($dsn, $dbUser, $dbPass, $options); //

                $sql = "INSERT INTO `orders` (
                            customer_email, customer_fname, customer_lname,
                            customer_phone, address_house, address_street,
                            address_city, address_postcode, address_country,
                            paypal_order_id, paypal_transaction_id,
                            amount, currency, order_status, order_date
                        ) VALUES (
                            :email, :fname, :lname,
                            :phone, :house, :street,
                            :city, :postcode, :country,
                            :paypal_order_id, :paypal_tx_id,
                            :amount, :currency, :status, NOW()
                        )"; //

                $stmt = $pdo->prepare($sql); //

                $stmt->execute([
                    ':email' => $customerEmail, //
                    ':fname' => $customerFirstName, // Use the sanitized first name
                    ':lname' => filter_var(isset($data->last_name) ? $data->last_name : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), //
                    ':phone' => filter_var(isset($data->contact_number) ? $data->contact_number : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), //
                    ':house' => filter_var(isset($data->house_number) ? $data->house_number : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), //
                    ':street' => filter_var(isset($data->street) ? $data->street : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), //
                    ':city' => filter_var(isset($data->city) ? $data->city : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), //
                    ':postcode' => filter_var(isset($data->post_code) ? $data->post_code : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), //
                    ':country' => filter_var(isset($data->country) ? $data->country : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), //
                    ':paypal_order_id' => $orderID, //
                    ':paypal_tx_id' => $transactionId, //
                    ':amount' => $paidAmount, //
                    ':currency' => $paidCurrency, //
                    ':status' => 'Paid' //
                ]);

                $last_order_id = $pdo->lastInsertId(); //

                if ($last_order_id && isset($_SESSION['mycart']) && is_array($_SESSION['mycart']) && !empty($_SESSION['mycart'])) { //
                    $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase)
                                 VALUES (:order_id, :product_id, :quantity, :price)"; //
                    $stmt_item = $pdo->prepare($sql_item); //

                    foreach ($_SESSION['mycart'] as $item) { //
                        if (isset($item['product_id'], $item['product_qty'], $item['price'], $item['name']) && // Added item['name'] for email
                            is_numeric($item['product_id']) &&
                            is_numeric($item['product_qty']) && $item['product_qty'] > 0 &&
                            is_numeric($item['price']))
                        {
                            $stmt_item->execute([
                                ':order_id' => $last_order_id,
                                ':product_id' => (int)$item['product_id'], //
                                ':quantity' => (int)$item['product_qty'], //
                                ':price' => (float)$item['price'] //
                            ]);
                            // ADD THIS: Store item details for email
                            $order_items_details_for_email[] = [
                                'name' => htmlspecialchars($item['name']),
                                'quantity' => (int)$item['product_qty'],
                                'price' => number_format((float)$item['price'], 2)
                            ];
                        } else {
                            error_log("VerifyPayPal - Invalid item data in session cart for Order ID: " . $last_order_id . " Item Data: " . print_r($item, true)); //
                        }
                    }
                }

                // --- SEND CONFIRMATION EMAIL --- ADD THIS BLOCK
                if ($last_order_id && $customerEmail) {
                    $mail = new PHPMailer(true);
                    try {
                        //Server settings
                        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output for testing
                        $mail->isSMTP();
                        $mail->Host       = $smtpHost;
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $smtpUsername;
                        $mail->Password   = $smtpPassword;
                        $mail->SMTPSecure = $smtpSecure;
                        $mail->Port       = $smtpPort;

                        //Recipients
                        $mail->setFrom($emailFromAddress, $emailFromName);
                        $mail->addAddress($customerEmail, $customerFirstName); // Add a recipient

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Your Order Confirmation - Merch Shop (Order #' . $last_order_id . ')';
                        
                        $emailBody = "<h1>Thank you for your order, " . htmlspecialchars($customerFirstName) . "!</h1>";
                        $emailBody .= "<p>Your order has been confirmed.</p>";
                        $emailBody .= "<h2>Order Summary:</h2><table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'><thead><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr></thead><tbody>";
                        
                        $email_grand_total = 0;
                        foreach ($order_items_details_for_email as $item_detail) {
                            $item_subtotal = $item_detail['quantity'] * $item_detail['price'];
                            $email_grand_total += $item_subtotal;
                            $emailBody .= "<tr>";
                            $emailBody .= "<td>" . $item_detail['name'] . "</td>";
                            $emailBody .= "<td style='text-align:center;'>" . $item_detail['quantity'] . "</td>";
                            $emailBody .= "<td style='text-align:right;'>" . htmlspecialchars($paidCurrency) . " " . $item_detail['price'] . "</td>";
                            $emailBody .= "<td style='text-align:right;'>" . htmlspecialchars($paidCurrency) . " " . number_format($item_subtotal, 2) . "</td>";
                            $emailBody .= "</tr>";
                        }
                        $emailBody .= "</tbody><tfoot><tr><td colspan='3' style='text-align:right;'><strong>Grand Total:</strong></td><td style='text-align:right;'><strong>" . htmlspecialchars($paidCurrency) . " " . number_format($email_grand_total, 2) . "</strong></td></tr></tfoot></table>";
                        $emailBody .= "<p>Shipping to:</p><address>";
                        $emailBody .= htmlspecialchars(isset($data->first_name) ? $data->first_name : '') . " " . htmlspecialchars(isset($data->last_name) ? $data->last_name : '') . "<br>";
                        $emailBody .= htmlspecialchars(isset($data->house_number) ? $data->house_number : '') . " " . htmlspecialchars(isset($data->street) ? $data->street : '') . "<br>";
                        $emailBody .= htmlspecialchars(isset($data->city) ? $data->city : '') . ", " . htmlspecialchars(isset($data->post_code) ? $data->post_code : '') . "<br>";
                        $emailBody .= htmlspecialchars(isset($data->country) ? $data->country : '') . "<br>";
                        $emailBody .= "Contact: " . htmlspecialchars(isset($data->contact_number) ? $data->contact_number : '') . "</address>";
                        $emailBody .= "<p>Thank you for shopping with us!</p>";

                        $mail->Body    = $emailBody;
                        $mail->AltBody = 'Your order #' . $last_order_id . ' has been confirmed. Total: ' . htmlspecialchars($paidCurrency) . ' ' . number_format($email_grand_total, 2) . '. Thank you for shopping with us!';

                        $mail->send();
                        error_log("Order confirmation email sent successfully to {$customerEmail} for Order ID {$last_order_id}.");
                    } catch (PHPMailerException $e) {
                        error_log("Order confirmation email could not be sent to {$customerEmail} for Order ID {$last_order_id}. Mailer Error: {$mail->ErrorInfo}");
                        // Do not change $response['success'] here, as the payment itself was successful.
                        // You might want to add a specific flag or message if email sending is critical.
                        // $response['email_status'] = 'failed';
                        // $response['email_message'] = 'Order confirmed, but confirmation email failed to send.';
                    }
                }
                // --- END SEND CONFIRMATION EMAIL ---

                // Clear session
                unset($_SESSION['mycart']); //
                unset($_SESSION['cart_total']); //
                unset($_SESSION['currency_code']); //

                $response['success'] = true; //
                $response['message'] = 'Payment verified successfully and order saved.'; //
                $response['transaction_id'] = $transactionId; //

            } catch (PDOException $e) {
                $response['message'] = 'Payment verified, but failed to save order to database.'; //
                error_log("PayPal Verify DB Error: OrderID {$orderID}, TxID {$transactionId}. Error: " . $e->getMessage()); //
                $response['success'] = false; // Ensure success is false on DB error //
            }
         } else {
             $response['message'] = 'Payment amount or currency mismatch. Verification failed.'; //
             error_log("PayPal SECURITY ALERT: Amount/Currency mismatch for Order ID: {$orderID}. Paid: {$paidAmount} {$paidCurrency}, Expected: {$expectedAmount} {$expectedCurrency}"); //
         }
    } else {
        $response['message'] = 'Payment status is not COMPLETED (' . htmlspecialchars($paymentStatus) . '). Verification failed.'; //
         error_log("PayPal Verify Warning: Status not completed for Order ID: {$orderID}. Status: {$paymentStatus}"); //
    }

} catch (HttpException $ex) {
    $response['message'] = 'Error communicating with PayPal API to verify order.'; //
    $debugId = 'N/A'; 
    if (method_exists($ex, 'getHeaders') && is_array($ex->getHeaders()) && isset($ex->getHeaders()['PayPal-Debug-Id'][0])) {
         $debugId = $ex->getHeaders()['PayPal-Debug-Id'][0]; //
    }
    error_log("PayPal Verify API Exception: OrderID: {$orderID}. Status Code: {$ex->statusCode}. Message: {$ex->getMessage()}. Debug ID: {$debugId}"); //

} catch (Exception $e) { // General Exception Catch
     $response['message'] = 'An internal server error occurred during payment verification.'; //
     error_log("PayPal Verify Generic Exception: OrderID: {$orderID}. Error: {$e->getMessage()}"); //
}

echo json_encode($response);
exit;

?>