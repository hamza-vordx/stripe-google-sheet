<?php
require 'vendor/autoload.php';
require 'config.php'; // Load constants

use Stripe\Stripe;
use Stripe\Checkout\Session;

// Set your Stripe secret key
Stripe::setApiKey(STRIPE_SECRET_KEY);

$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
$current_url = $protocol . '://' . $_SERVER['HTTP_HOST'].'/stripe-google-sheet';

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
                        'name' => 'ALES TECHNICAL AND OCCUPATIONAL SKILLS TRAINING L.L.C Payment',
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment', // or 'subscription' for recurring payments
            'success_url' => $current_url . '/success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $current_url . '/cancel.php',
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 300px;
            text-align: center;
        }
        h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        label {
            margin-bottom: 10px;
            display: block;
            font-size: 16px;
            color: #555;
        }
        input[type="number"] {
            padding: 10px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 16px;
        }
        button {
            background-color: #6772e5;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        button:hover {
            background-color: #5469d4;
        }
        .icon {
            margin-right: 8px;
        }
    </style>
</head>
<body>

<div class="card">
    <h1>Checkout</h1>
    <form action="checkout.php" method="POST">
        <label for="amount">Amount (in USD):</label>
        <input type="number" id="amount" name="amount" min="1" required>
        <button type="submit">
            <img src="https://img.icons8.com/material-outlined/24/ffffff/payment.png" class="icon" alt="Pay Icon" />
            Pay
        </button>
    </form>
</div>

</body>
</html>
