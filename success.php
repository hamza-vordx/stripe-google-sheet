<?php
require 'vendor/autoload.php';
require 'config.php'; // Load constants

use Stripe\Stripe;
use Stripe\Checkout\Session;

// Set your Stripe secret key
Stripe::setApiKey(STRIPE_SECRET_KEY);

// Get the session ID from the query parameters
$sessionId = $_GET['session_id'] ?? '';

if ($sessionId) {
    try {
        // Retrieve the Checkout Session
        $session = Session::retrieve($sessionId);

        // Extracting required details
        $customerEmail = $session->customer_details->email;
        $amountPaid = $session->amount_total / 100; // Convert cents to dollars
        $paymentId = $session->payment_intent;

        // Display the details in a card format
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Payment Success</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f7f7f7;
                    margin: 0;
                    padding: 0;
                    height: 100vh; /* Full viewport height */
                    display: flex; /* Use flexbox for centering */
                    align-items: center; /* Center vertically */
                    justify-content: center; /* Center horizontally */
                }
                .card {
                    background: #fff;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    padding: 20px;
                    max-width: 400px;
                    width: 100%; /* Full width up to max */
                    text-align: center; /* Center text */
                }
                .card h1 {
                    font-size: 24px;
                    margin-bottom: 10px;
                }
                .card p {
                    font-size: 16px;
                    margin: 5px 0;
                }
                .card .amount {
                    font-weight: bold;
                    color: #28a745; /* Green color */
                }
                .card .payment-id {
                    font-size: 14px;
                    color: #888;
                }
            </style>
        </head>
        <body>

        <div class='card'>
            <h1>Payment Successful!</h1>
            <p>Customer Email: <strong>$customerEmail</strong></p>
            <p class='amount'>Amount Paid: $$amountPaid</p>
            <p class='payment-id'>Payment ID: $paymentId</p>
        </div>

        </body>
        </html>";
    } catch (Exception $e) {
        // Handle error
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Session ID is required.']);
}
?>
