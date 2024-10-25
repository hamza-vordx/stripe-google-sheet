# Stripe Payment Integration with PHP

This PHP application integrates Stripe's Checkout and webhook handler to store payment data in Google Sheets or a CSV file.

## Prerequisites
1. PHP 7.4+ with Composer installed.
2. Stripe account with API keys.
3. (Optional) Google Cloud account with Sheets API enabled, for Google Sheets storage.

## Setup Guide

### Step 1: Install Dependencies
Run the following command to install dependencies:
1. ```bash 
      composer install

### Step 2: Configure Environment Variables

1. Rename example-config.php to config.php
2. Update config.php with your details

### Step 3: Set Up Webhook on Stripe

1. In the Stripe Dashboard, add a webhook for the invoice.payment_succeeded event.
2. Use the URL pointing to your stripe_webhook_handler.php script.

File Overview
1. checkout.php: Checkout page to initiate payments.
2. stripe_webhook_handler.php: Webhook handler for processing payment events.
3. success.php: Page displayed after successful payment.
4. cancel.php: Page displayed when a payment is canceled.

### Important Notes
Security: Keep config.php secure, especially your API keys.
Production: Use live keys and ensure HTTPS for webhook URLs in production