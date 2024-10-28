
<?php
// Assuming this will be included with $data
$data = isset($data) ? $data : []; // Default to an empty array if not set

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Tax Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #2e2e2e;
        }

        .invoice-container {
            max-width: 990px;
            margin: auto;
        }

        .footer {
            text-align: center;
        }

        .header {
            margin-top: 30px;
        }

        .header h1 {
            font-size: 1.5em;
        }


        .company-info {
            font-weight: 500;
            font-size: 12px;
            color: #2e2e2e;
            line-height: normal;
            font-style: normal;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .details-table th,
        .details-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #000;
            /* Darker border for clearer separation */
        }

        .details-table th {
            background-color: #f2f2f2;
            /* Light grey background for headers */
            font-weight: bold;
        }

        .details-table tbody tr {
            background-color: #fff;
        }

        .details-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
            /* Light alternating row colors */
        }

        .summary-table {
            margin-top: 20px;
        }

        .logo {
            max-width: 200px;
        }

        .signature {
            max-width: 100px;
        }

        .International {
            font-size: 12px;
            font-style: bold;
            font-weight: 700;
        }

        .Tax {
            font-size: 30px;
            font-weight: 500;
            margin-left: 436px;
            margin-top: -56px;
        }

        .date {
            display: flex;
            flex-direction: column;
            font-weight: 700;
            font-style: bold;
            font-size: 14px;
            gap: 10px;
        }

        .date p {
            margin: 0;
            padding: 0;
        }

        .date2 p {
            margin: 0;
            padding: 0;
        }

        .date2 {
            margin-left: 70px;
            margin-top: -40px;
        }

        .date-container {
            padding-left: 436px;
            font-size: 12px;
            margin: -36px 0;

        }


        .client-info {
            font-weight: 500;
            font-size: 12px;
            color: #2e2e2e;
            line-height: normal;
            font-style: normal;

        }

        .company-info-div {
            margin-top: 80px;
        }

        .client-id {
            font-size: 18px;
            font-weight: 600;
        }

        .trn-name {
            font-weight: 500;
            font-size: 12px;
            color: #2e2e2e;
            line-height: normal;
            font-style: normal;
        }

        .trn-id {
            font-weight: 500;
            font-size: 12px;
            color: #2e2e2e;
            line-height: normal;
            margin-top: -25px;
            margin-left: 105px;
            font-style: normal;
        }

        .billl {
            font-size: 18px;
            font-weight: 700;
            margin: 30px 0;
        }

        .comments {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
        }

        .bill-container {
            margin-bottom: 50px;
            margin-top: -28px;
        }

        .bill-container p {
            font-size: 12px;
            font-weight: 400;
            font-style: normal;
            line-height: normal;
            margin: 0;
            padding-top: 5px;
        }

        .footer-div {
            font-size: 14px;
            font-weight: 500;
            margin-top: 40px;
        }

        .footer-div p {
            margin: 0;
            padding: 0;
        }

        .total-div {
            font-size: 16px;
            font-weight: 700;
            margin-top: -27px;
            margin-left: 125px;
        }

        .main-title {
            gap: 20px;
            margin-left: 20px;
        }

        .main-title h1 {
            font-size: 16px;
            color: #dab560;
            margin-left: 87px;
            margin-top: -59px;
        }

        .client {
            margin-left: 436px;
            font-weight: 700;
            font-size: 14px;
            margin-top: -67px;
        }

        .client-id {
            margin-top: -14px;
            margin-left: 520px;
            font-weight: 500;
            font-size: 12px;
        }

        .bill-details {
            margin-left: 107px;
            margin-top: -94px;
        }

        .stamp {
            margin-left: 179px;
            margin-top: -17px;
        }

        .signature {
            margin-left: 350px;
            margin-top: -129px;
        }
        .left-align-row td {
            text-align: left;
            padding-left: 20px;
        }
    </style>
</head>

<body>
<div class="invoice-container">


    <!-- title -->

    <div class="main-title">
        <div>
            <img src="https://i.ibb.co/FxGPzNb/logo.png" alt="logo">
        </div>
        <h1>
            INTERNATIONAL ACADEMY <br> of FACEPLASTIC AND OSTEOPATHY
        </h1>
    </div>



    <!-- Logo -->
    <div class="header">
        <div class="International">
            <h1>International Faceplastic And <br> Osteopathy - FZCO</h1>
            <p class="Tax">Tax Invoice</p>
        </div>
    </div>

    <!-- Company Info -->

    <div class="company-info">
        <p>Office No. 45-0110 <br>
            M & 4th FLOOR, CITY AVENUE BUILDING, PORT <br>
            SAEED, DEIRA, DUBAI, UAE Email: <br>
            faceplasty.academy@gmail.com</p>
    </div>

    <div class="date-container">

        <div class="date">
            <p>DATE:</p>
            <p>#</p>
        </div>
        <div class="date2">
            <p><?php echo date('Y-m-d', strtotime($data['created_date'])); ?> </p>
            <p><?php echo $data['ref_number']; ?></p>
        </div>
    </div>


    <div class="company-info-div">
        <div class="client-info">

            <p>PO Box: 125566 UAE, DUBAI, DEIRA, PORT <br>
                SAEED,PORT SAEED, CITY AVENUE BUILDING, <br>
                M & 4th FLOOR, Office No. 45-0110</p>
        </div>

        <p class="trn-name">TRN:</p>
        <p class="trn-id"><?php echo $data['payment_intent']; ?> </p>

        <div class="client">
            CLIENT ID:
        </div>

        <div class="client-id">
            <?php echo $data['email']; ?>
        </div>

    </div> <br>



    <p class="billl">Bill To:</p>

    <div class="bill-container">
        <div>
            <p>Name:</p>
            <p>email address:</p>
            <p>PO box:</p>
            <p>Phone:</p>
            <p>TRN:</p>
        </div>

        <div class="bill-details">
            <p> <?php echo $data['customer_name']; ?> </p>
            <p><?php echo $data['email']; ?> </p>
            <p><?php echo $data['address_line1'] . ' '. $data['address_line2']. ' '.$data['address_line2']. ' '.$data['city'].
                ' '.$data['postal_code']. ' '.$data['state']. ' '.$data['country'] ; ?></p>
        </div>
    </div>


    <!-- Details Table -->

    <p class="comments">comments or special instructions: N/A</p>

    <table class="details-table">
        <thead>
        <tr>
            <th>Product Name</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Amount/per pc</th>
            <th>Total Amount </th>
            <th>VAT </th>
            <th>Total </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Educational Services</td>
            <td><?php echo $data['product_name']; ?></td>
            <td>1</td>
            <td><?php echo $data['subtotal_amount']; ?></td>
            <td><?php echo $data['subtotal_amount']; ?></td>
            <td><?php echo $data['tax_amount']; ?></td>
            <td><?php echo $data['amount_paid']; ?></td>
        </tr>
        <tr>
            <td colspan="4"></td>
            <td><?php echo $data['subtotal_amount']; ?></td>
            <td><?php echo $data['tax_amount']; ?></td>
            <td><?php echo $data['amount_paid']; ?></td>

        </tr>
        </tbody>
    </table>

    <p class="total-div">TOTAL</p>
    <!-- Stamp and Signature -->
    <div class="footer-div">
        <p>Settlement currency: <?php echo $data['currency']; ?></p>
        <div class="stamp">
            <img src="https://fiverr-res.cloudinary.com/image/upload/f_auto,q_auto/v1/secured-attachments/messaging_message/attachment/70f0f26df6f61c56396184cbb902e6d4-1730134943825/Stamp.png?__cld_token__=exp=1730160748~hmac=64ed271ad1f8e15966910a45c0c5c37063e8f709fd3ccbf860c2e20e8fa16d61" alt="stamp">
        </div>
        <div class="signature">
            <img src="https://fiverr-res.cloudinary.com/image/upload/f_auto,q_auto/v1/secured-attachments/messaging_message/attachment/1c78781a4e93975b94e512d298fe40e3-1730134943575/Signature.png?__cld_token__=exp=1730160748~hmac=ac98205befcaba05826bc2b02af5f939dc6b804bdfcafb1abae7267f420e701c" alt="signature">
        </div>
    </div>
</div>
</body>

</html>