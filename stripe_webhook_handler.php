<?php
require 'vendor/autoload.php';
require 'config.php'; // Import constants

use Stripe\Stripe;
use Stripe\Webhook;
use Google\Client;
use Google\Service\Sheets;
use Dompdf\Dompdf;
use Dompdf\Options;

// Set Stripe API Key
Stripe::setApiKey(STRIPE_SECRET_KEY);

function sendInvoiceEmail($to, $subject, $message, $htmlContent, $from = "sender@example.com") {
    // Initialize Dompdf
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($htmlContent);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Save PDF to a file
    $pdfOutput = $dompdf->output();
    $pdfFilePath = 'invoice.pdf';
    file_put_contents($pdfFilePath, $pdfOutput);

    // Email headers
    $headers = "From: $from";
    $separator = md5(time());
    $headers .= "\r\nMIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"";

    // Email body with attachment
    $body = "--" . $separator . "\r\n";
    $body .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= $message . "\r\n";

    // Read PDF file content
    $file_size = filesize($pdfFilePath);
    $handle = fopen($pdfFilePath, 'r');
    $content = fread($handle, $file_size);
    fclose($handle);
    $content = chunk_split(base64_encode($content));

    // Attachment
    $body .= "--" . $separator . "\r\n";
    $body .= "Content-Type: application/pdf; name=\"invoice.pdf\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n";
    $body .= "Content-Disposition: attachment; filename=\"invoice.pdf\"\r\n\r\n";
    $body .= $content . "\r\n";
    $body .= "--" . $separator . "--";

    // Send email
    if (mail($to, $subject, $body, $headers)) {
        echo "Email sent with PDF attachment.";
    } else {
        echo "Failed to send email.";
    }

    // Optionally, remove the PDF file after sending
    unlink($pdfFilePath);
}

// Example usage
$htmlContent = file_get_contents('invoice.html'); // Load HTML content from a file
sendInvoiceEmail("recipient@example.com", "Your Invoice", "Please find attached your invoice.", $htmlContent);

// Function to store data in CSV
function storeInCSV($data) {
    $file = CSV_FILE_PATH;
    $header = !file_exists($file) ? ['ID', 'Customer Email', 'Subscription Status', 'Amount Paid', 'Currency', 'Product Name', 'Created Date'] : null;

    $handle = fopen($file, 'a');
    if ($header) {
        fputcsv($handle, $header);
    }
    fputcsv($handle, $data);
    fclose($handle);
}

// Function to store data in Google Sheets
function storeInGoogleSheet($data) {
    $client = new Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);
    $client->addScope(Sheets::SPREADSHEETS);
    $client->setAccessType('offline'); // For refresh tokens

    // Load previously saved access token if available
    if (file_exists('token.json')) {
        $accessToken = json_decode(file_get_contents('token.json'), true);
        $client->setAccessToken($accessToken);
    }

    // Refresh the token if it's expired
    if ($client->isAccessTokenExpired()) {
        $refreshToken = $accessToken['refresh_token'];
        $client->fetchAccessTokenWithRefreshToken($refreshToken);
        // Save the new access token
        file_put_contents('token.json', json_encode($client->getAccessToken()));
    }

    // Now make the Sheets API call
    $service = new Sheets($client);
    $values = [$data];

    $body = new Sheets\ValueRange(['values' => $values]);

    $params = ['valueInputOption' => 'RAW'];
    try {
        $response = $service->spreadsheets_values->append(GOOGLE_SHEET_ID, GOOGLE_SHEET_RANGE, $body, $params);

        return $response; // Return the response if needed

    } catch (Exception $e) {
        var_dump($e->getMessage());
        logError("Google Sheets API error: " . $e->getMessage());
    }
}

// Function to log errors
function logError($message) {
    error_log($message);
}

// Retrieve the request's body and the Stripe signature header
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

try {
    // Verify the webhook signature
    $event = Webhook::constructEvent($payload, $sig_header, STRIPE_WEBHOOK_SECRET);

    // Handle different event types
    switch ($event->type) {
        case 'invoice.payment_succeeded':
            $invoice = $event->data->object;
            $customerEmail = $invoice->customer_email;
            $subscriptionStatus = $invoice->status;
            $amountPaid = $invoice->amount_paid / 100; // Convert to dollars
            $currency = strtoupper($invoice->currency);
            $productName = $invoice->lines->data[0]->description ?? 'N/A'; // Get the product name
            $createdDate = date('Y-m-d H:i:s', $invoice->created); // Convert Unix timestamp to date

            $data = [
                $invoice->id,
                $customerEmail,
                $subscriptionStatus,
                $amountPaid,
                $currency,
                $productName,
                $createdDate,
                'Subscription'
            ];

            storeInCSV($data); // Store data in CSV
            storeInGoogleSheet($data); // Store data in Google Sheet
            break;

        case 'checkout.session.completed':
            $session = $event->data->object;
            $customerEmail = $session->customer_details->email;
            $amountPaid = ($session->amount_total / 100); // Convert to dollars
            $currency = strtoupper($session->currency);
            $productName = $session->line_items->data[0]->description ?? 'N/A'; // Get the product name
            $createdDate = date('Y-m-d H:i:s', $session->created); // Convert Unix timestamp to date

            $data = [
                $session->id,
                $customerEmail,
                'paid', // Status for one-time payment
                $amountPaid,
                $currency,
                $productName,
                $createdDate,
                'One time'
            ];

            storeInCSV($data); // Store data in CSV
            storeInGoogleSheet($data); // Store data in Google Sheet
            break;

        default:
            logError("Unhandled event type: {$event->type}");
            break;
    }

    http_response_code(200); // Respond to Stripe
} catch (\UnexpectedValueException $e) {
    logError("Invalid payload: " . $e->getMessage());
    http_response_code(400); // Invalid payload
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    logError("Invalid signature: " . $e->getMessage());
    http_response_code(400); // Invalid signature
    exit();
}
?>
