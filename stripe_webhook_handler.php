<?php
require 'vendor/autoload.php';
require 'config.php'; // Import constants

use Stripe\Stripe;
use Stripe\Webhook;
use Google\Client;
use Google\Service\Sheets;

// Set Stripe API Key
Stripe::setApiKey(STRIPE_SECRET_KEY);

// Error logging function
function logError($message) {
    error_log($message, 3, 'error_log.txt'); // Log errors to a specific file
}

function storeInCSV($data) {
    $file = CSV_FILE_PATH;
    $header = ['ID', 'Customer Email', 'Subscription Status', 'Amount Paid', 'Currency'];

    // Check if the CSV file exists
    if (!file_exists($file)) {
        // Create the file and add the header
        $handle = fopen($file, 'w'); // Use 'w' mode to create the file
        if ($handle === false) {
            logError("Unable to create CSV file.");
            throw new Exception("Unable to create CSV file.");
        }
        fputcsv($handle, $header); // Write header to the new file
        fclose($handle);
    }

    // Append data to the existing file
    $handle = fopen($file, 'a'); // Use 'a' mode to append data
    if ($handle === false) {
        logError("Unable to open CSV file for appending.");
        throw new Exception("Unable to open CSV file for appending.");
    }
    fputcsv($handle, $data); // Write the data to the file
    fclose($handle);
}

function storeInGoogleSheet($data) {
    $client = new Client();
    $client->setAuthConfig(GOOGLE_CREDENTIALS_PATH);
    $client->addScope(Sheets::SPREADSHEETS);

    $service = new Sheets($client);
    $values = [$data];
    $body = new Sheets\ValueRange(['values' => $values]);

    $params = ['valueInputOption' => 'RAW'];
    $service->spreadsheets_values->append(GOOGLE_SHEET_ID, GOOGLE_SHEET_RANGE, $body, $params);
}

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

try {
    $event = Webhook::constructEvent($payload, $sig_header, STRIPE_WEBHOOK_SECRET);

    // Handle invoice.payment_succeeded event
    if ($event->type === 'invoice.payment_succeeded') {
        $invoice = $event->data->object;
        $customerEmail = $invoice->customer_email;
        $subscriptionStatus = $invoice->status;
        $amountPaid = $invoice->amount_paid / 100; // Convert to dollars
        $currency = strtoupper($invoice->currency);

        $data = [
            $invoice->id,
            $customerEmail,
            $subscriptionStatus,
            $amountPaid,
            $currency
        ];

        storeInCSV($data);      // Store data in CSV
        storeInGoogleSheet($data); // Store data in Google Sheet
    }

    // Handle checkout.session.completed event
    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;
        $customerEmail = $session->customer_details->email; // Assuming the email is present here
        $amountPaid = $session->amount_total / 100; // Convert to dollars
        $currency = strtoupper($session->currency);
        $subscriptionStatus = 'completed'; // Set status as completed

        $data = [
            $session->id,
            $customerEmail,
            $subscriptionStatus,
            $amountPaid,
            $currency
        ];

        storeInCSV($data);      // Store data in CSV
        storeInGoogleSheet($data); // Store data in Google Sheet
    }

    http_response_code(200); // Respond to Stripe
} catch (\UnexpectedValueException $e) {
    logError("Invalid payload: " . $e->getMessage());
    http_response_code(400); // Invalid payload
    exit("Invalid payload"); // Provide a response
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    logError("Invalid signature: " . $e->getMessage());
    http_response_code(400); // Invalid signature
    exit("Invalid signature"); // Provide a response
} catch (Exception $e) {
    logError("Error: " . $e->getMessage());
    http_response_code(500); // Internal server error
    exit("Internal server error"); // Provide a response
}
?>
