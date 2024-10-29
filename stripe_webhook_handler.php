<?php
require 'vendor/autoload.php';
require 'config.php'; // Import constants

use Stripe\Stripe;
use Stripe\Webhook;
use Google\Client;
use Google\Service\Sheets;
use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Service\Drive;

// Set Stripe API Key
Stripe::setApiKey(STRIPE_SECRET_KEY);

function initializeGoogleClient() {
    $client = new Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);
    $client->addScope(Sheets::SPREADSHEETS);
    $client->addScope(Google\Service\Drive::DRIVE_FILE);
    $client->setAccessType('offline'); // Request refresh tokens

    // If there's an existing token, set it
    if (file_exists('token.json')) {
        $accessToken = json_decode(file_get_contents('token.json'), true);
        $client->setAccessToken($accessToken);
    }

    // Handle token refresh if expired
    if ($client->isAccessTokenExpired()) {
        // Refresh token if possible
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents('token.json', json_encode($client->getAccessToken()));
        } else {
            // If no refresh token, get a new access token
            header('Location: ' . $client->createAuthUrl());
            exit();
        }
    }

    return $client;
}

$client = initializeGoogleClient();

function sendInvoiceEmailAndSaveToDrive($client, $to, $data, $folderId) {
    // Step 1: Generate the PDF

    ob_start();

// Include the invoice.php file with the data array
    include('invoice.php');

// Get the HTML content of the invoice
    $htmlContent = ob_get_clean();
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($htmlContent);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Set a unique filename using a timestamp or unique ID
    $uniqueFileName = 'invoice_' . $to . '.pdf';
    $pdfFilePath = __DIR__ . '/' . $uniqueFileName;
    file_put_contents($pdfFilePath, $dompdf->output());

    // Step 2: Upload PDF to Google Drive
    $fileId = null; // Initialize file ID
    try {
        // Authenticate and get the Google Drive service
        $service = new Drive($client);

        // Prepare file metadata with the specific folder ID
        $fileMetadata = new Drive\DriveFile([
            'name' => $uniqueFileName,
            'parents' => [$folderId],
            'mimeType' => 'application/pdf'
        ]);

        // Upload the file to Google Drive
        $content = file_get_contents($pdfFilePath);
        $file = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => 'application/pdf',
            'uploadType' => 'multipart'
        ]);

        $fileId = $file->id; // Get the file ID
        echo "PDF file uploaded to Google Drive. File ID: " . $fileId;

        // Generate the Google Drive link
        $fileLink = "https://drive.google.com/file/d/{$fileId}/view?usp=sharing";

    } catch (Exception $e) {

        echo "Failed to upload PDF to Google Drive: " . $e->getMessage();
        return; // Exit the function if upload fails
    }


    // Return the file link
    return $fileLink; // Return the URL of the uploaded file
}

function sendMail($to,$fileLink,$invoiceData)
{

    $uniqueFileName = 'invoice_' . $to . '.pdf';
    $pdfFilePath = __DIR__ . '/' . $uniqueFileName;
    // Step 3: Send the PDF as an email attachment with PHPMailer
    $mail = new PHPMailer(true);
    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = MAIL_HOST; // Your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME; // Your SMTP username
        $mail->Password = MAIL_PASSWORD; // Your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use 'tls' or 'ssl' as necessary
        $mail->Port = MAIL_PORT; // SMTP port

        // Set sender and recipient
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($to); // Add a recipient
        $invoiceTable = '
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; }
                                .invoice-container { width: 100%; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; }
                                h2 { color: #333; }
                                .invoice-header { background-color: #f2f2f2; padding: 10px; text-align: center; }
                                .invoice-details { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 20px; }
                                .invoice-details div { padding: 8px; border: 1px solid #ddd; }
                                .label { font-weight: bold; }
                            </style>
                        </head>
                        <body>
                            <div class="invoice-container">
                                <h2 class="invoice-header">Invoice Details</h2>
                                <p>Dear ' . htmlspecialchars($invoiceData['customer_name']) . ',</p>
                                <p>Thank you for your payment. Please find your invoice details below.</p>
                                <div class="invoice-details">
                                    <div><span class="label">Reference Number:</span> ' . htmlspecialchars($invoiceData['ref_number']) . '</div>
                                    <div><span class="label">Session ID:</span> ' . htmlspecialchars($invoiceData['session_id']) . '</div>
                                    <div><span class="label">Payment Intent:</span> ' . htmlspecialchars($invoiceData['payment_intent']) . '</div>
                                    <div><span class="label">Email:</span> ' . htmlspecialchars($invoiceData['email']) . '</div>
                                    <div><span class="label">Phone:</span> ' . htmlspecialchars($invoiceData['customer_phone']) . '</div>
                                    <div><span class="label">Address:</span> '
                                    . htmlspecialchars($invoiceData['address_line1']) . '<br>'
                                    . htmlspecialchars($invoiceData['address_line2']) . '<br>'
                                    . htmlspecialchars($invoiceData['city']) . ', ' . htmlspecialchars($invoiceData['state']) . '<br>'
                                    . htmlspecialchars($invoiceData['postal_code']) . ', ' . htmlspecialchars($invoiceData['country']) .
                                    '</div>
                                    <div><span class="label">Product Name:</span> ' . htmlspecialchars($invoiceData['product_name']) . '</div>
                                    <div><span class="label">Amount Paid:</span> ' . htmlspecialchars($invoiceData['currency']) . ' ' . number_format($invoiceData['amount_paid'], 2) . '</div>
                                    <div><span class="label">Subtotal:</span> ' . htmlspecialchars($invoiceData['currency']) . ' ' . number_format($invoiceData['subtotal_amount'], 2) . '</div>
                                    <div><span class="label">Tax Amount:</span> ' . htmlspecialchars($invoiceData['currency']) . ' ' . number_format($invoiceData['tax_amount'], 2) . '</div>
                                    <div><span class="label">Date:</span> ' . date("F j, Y", strtotime($invoiceData['created_date'])) . '</div>
                                </div>
                                <p>Best regards,</p>
                                <p>' . htmlspecialchars(MAIL_FROM_NAME) . '</p>
                            </div>
                        </body>
                        </html>
                        ';
        // Email subject and body
        $mail->Subject = 'TAX Invoice"';
        $mail->isHTML(true);
        $mail->Body = $invoiceTable;

        // Attach the PDF
        if (file_exists($pdfFilePath)) {
            $mail->addAttachment($pdfFilePath, $uniqueFileName); // Attach the PDF
        } else {
            echo "File not found: $pdfFilePath";
            return; // Exit if the file doesn't exist
        }
        // Send email
        if ($mail->send()) {
            echo "Email sent with PDF attachment.";
        } else {
            echo "Failed to send email.";
        }
    } catch (Exception $e) {

        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

    // Step 4: Clean up
    unlink($pdfFilePath);

}
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
function storeInGoogleSheet($client, $data) {

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
//    $event = Webhook::constructEvent($payload, $sig_header, STRIPE_WEBHOOK_SECRET);
    $event = json_decode($payload);
    // Handle different event types
    switch ($event->type) {
        case 'invoice.paid':
            $invoice = $event->data->object;
            $customerEmail = $invoice->customer_email;
            $subscriptionStatus = $invoice->status;
            $amountPaid = $invoice->amount_paid / 100; // Convert to dollars
            $currency = strtoupper($invoice->currency);
            $productName = $invoice->lines->data[0]->description ?? 'N/A'; // Get the product name
            $createdDate = date('Y-m-d H:i:s', $invoice->created); // Convert Unix timestamp to date
            $ref_number  = time();
            $data = [
                $invoice->id,
                $customerEmail,
                $subscriptionStatus,
                $amountPaid,
                $currency,
                $productName,
                $createdDate,
                $ref_number,
                'Subscription',
                $invoice->hosted_invoice_url
            ];
            $invoiceData = [
                'session_id'           => $invoice->id,
                'payment_intent'       => $invoice->payment_intent,
                'email'                => $invoice->customer_email,
                'customer_name'        => $invoice->customer_name ?? 'Client',
                'customer_phone'       => $invoice->customer_phone ?? 'N/A',
                'address_line1'        => $invoice->customer_address->line1 ?? '',
                'address_line2'        => $invoice->customer_details->line2 ?? '',
                'city'                 => $invoice->customer_details->city ?? '',
                'postal_code'          => $invoice->customer_details->postal_code ?? '',
                'state'                => $invoice->customer_details->state ?? '',
                'country'              => $invoice->customer_details->country ?? '',
                'amount_paid'          => $amountPaid,
                'subtotal_amount'      => ($invoice->subtotal / 100),
                'tax_amount'           => ($invoice->tax / 100) ?? 0,
                'currency'             => $currency,
                'product_name'         => $productName,
                'created_date'         => $createdDate,
                'ref_number'           => $ref_number
            ];


            $fileLink =sendInvoiceEmailAndSaveToDrive($client,$customerEmail, $invoiceData, GOOGLE_DRIVE_ID);
            $data[] = $fileLink;
            storeInGoogleSheet($client,$data);
            sendMail($customerEmail,$fileLink,$invoiceData);
            echo "Uploaded file link: " . $fileLink;
            break;

        case 'checkout.session.completed':

            $session = $event->data->object;
            if ($session->subscription == null) {
                $customerEmail = $session->customer_details->email;
                $amountPaid = ($session->amount_total / 100); // Convert to dollars
                $currency = strtoupper($session->currency);
                $productName = $session->line_items->data[0]->description ?? 'N/A'; // Get the product name
                $createdDate = date('Y-m-d H:i:s', $session->created); // Convert Unix timestamp to date
                $ref_number  = time();
                $data = [
                    $session->id,
                    $customerEmail,
                    'paid', // Status for one-time payment
                    $amountPaid,
                    $currency,
                    $productName,
                    $createdDate,
                    $ref_number,
                    'One time',
                    'N/A'
                ];
                $invoiceData = [
                    'session_id'           => $session->id,
                    'payment_intent'       => $session->payment_intent,
                    'email'                => $session->customer_details->email,
                    'customer_name'        => $session->customer_details->name ?? 'Client',
                    'customer_phone'       => $session->customer_details->phone ?? 'N/A',
                    'address_line1'        => $session->customer_details->address->line1 ?? '',
                    'address_line2'        => $session->customer_details->address->line2 ?? '',
                    'city'                 => $session->customer_details->address->city ?? '',
                    'postal_code'          => $session->customer_details->address->postal_code ?? '',
                    'state'                => $session->customer_details->address->state ?? '',
                    'country'              => $session->customer_details->address->country ?? '',
                    'amount_paid'          => $amountPaid,
                    'subtotal_amount'      => ($session->amount_subtotal / 100),
                    'tax_amount'           => ($session->total_details->amount_tax / 100) ?? 0,
                    'currency'             => $currency,
                    'product_name'         => $productName,
                    'created_date'         => $createdDate,
                    'ref_number'           => $ref_number
                ];

                $fileLink = sendInvoiceEmailAndSaveToDrive($client,$customerEmail, $invoiceData, GOOGLE_DRIVE_ID);
                $data[] = $fileLink;
                storeInGoogleSheet($client,$data);
                sendMail($customerEmail,$fileLink,$invoiceData);
                echo "Uploaded file link: " . $fileLink;
            }

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
