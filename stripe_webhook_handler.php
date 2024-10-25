<?php
require 'vendor/autoload.php';
require 'config.php'; // Import constants

use Stripe\Stripe;
use Stripe\Webhook;
use Google\Client;
use Google\Service\Sheets;

// Set Stripe API Key
Stripe::setApiKey(STRIPE_SECRET_KEY);

function storeInCSV($data) {
    $file = CSV_FILE_PATH;
    $header = !file_exists($file) ? ['ID', 'Customer Email', 'Subscription Status', 'Amount Paid', 'Currency'] : null;

    $handle = fopen($file, 'a');
    if ($header) {
        fputcsv($handle, $header);
    }
    fputcsv($handle, $data);
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

    http_response_code(200); // Respond to Stripe
} catch (\UnexpectedValueException $e) {
    http_response_code(400); // Invalid payload
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400); // Invalid signature
    exit();
}
?>
