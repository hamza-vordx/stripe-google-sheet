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

1. Subscription Events
   1. invoice.paid: Triggered when an invoice for a subscription is successfully paid. Use this event to grant access to subscription services.
   2. invoice.payment_failed: Triggered when a payment for a subscription invoice fails. This event can be used to notify customers about payment failures.
   3. customer.subscription.updated: Triggered when a subscription is updated. Use this to notify customers or update your records.
   4. customer.subscription.deleted: Triggered when a subscription is canceled or expires. This event can be used to revoke access to services.

2. One-Time Payment Events
   1. checkout.session.completed: Triggered when a Checkout Session is completed successfully. This is used for fulfilling one-time purchases.
   2. payment_intent.succeeded: Triggered when a PaymentIntent is successfully completed. Confirm that a payment was received.
   3. payment_intent.payment_failed: Triggered when a PaymentIntent fails. Notify the customer about the failure.

Webhook Handler Example
For processing webhook events, refer to the example webhook handler provided in the code section. Make sure to set your STRIPE_WEBHOOK_SECRET and handle each event appropriately.
  
   

File Overview
1. checkout.php: Checkout page to initiate payments.
2. stripe_webhook_handler.php: Webhook handler for processing payment events.
3. success.php: Page displayed after successful payment.
4. cancel.php: Page displayed when a payment is canceled.

### Important Notes
Security: Keep config.php secure, especially your API keys.
Production: Use live keys and ensure HTTPS for webhook URLs in production