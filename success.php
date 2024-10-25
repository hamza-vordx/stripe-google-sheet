<?php
require 'vendor/autoload.php';
require 'config.php';

use Stripe\Stripe;
use Stripe\Checkout\Session;

if (isset($_GET['session_id'])) {
    $sessionId = $_GET['session_id'];

    // Set your Stripe secret key
    Stripe::setApiKey(STRIPE_SECRET_KEY);

    try {
        // Retrieve the session
        $session = Session::retrieve($sessionId);
        echo "<h1>Payment successful!</h1>";
        echo "<p>Customer Email: " . $session->customer_email . "</p>";
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
} else {
    echo "<h1>No session ID provided.</h1>";
}
?>
