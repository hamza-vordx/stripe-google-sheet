<?php
require 'vendor/autoload.php';
require 'config.php'; // Load constants

use Stripe\Stripe;
use Stripe\Checkout\Session;

// Set your Stripe secret key
Stripe::setApiKey(STRIPE_SECRET_KEY);

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the amount from the form
    $amount = intval($_POST['amount']) * 100; // Convert dollars to cents

    try {
        // Create a Checkout Session for a payment
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Subscription Payment',
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment', // or 'subscription' for recurring payments
            'success_url' => 'http://yourdomain.com/success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://yourdomain.com/cancel.php',
        ]);

        // Redirect to Stripe Checkout page
        header("Location: " . $session->url);
        exit();
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Checkout</title>
</head>
<body>

<h1>Checkout</h1>
<form action="checkout.php" method="POST">
    <label for="amount">Amount (in USD):</label>
    <input type="number" id="amount" name="amount" min="1" required>
    <button type="submit">Pay</button>
</form>

</body>
</html>
