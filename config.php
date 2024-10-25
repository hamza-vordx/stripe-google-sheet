<?php

// Stripe API Configuration
define('STRIPE_SECRET_KEY', 'your_stripe_secret_key');
define('STRIPE_WEBHOOK_SECRET', 'your_webhook_secret_key');

// Google Sheets API Configuration
define('GOOGLE_SHEET_ID', 'your_spreadsheet_id');
define('GOOGLE_SHEET_RANGE', 'Sheet1!A1:E1'); // Adjust as per your Google Sheet

// Path to Google Sheets credentials JSON file
define('GOOGLE_CREDENTIALS_PATH', 'path/to/credentials.json');

// File path for storing CSV data
define('CSV_FILE_PATH', 'stripe_data.csv');
?>
