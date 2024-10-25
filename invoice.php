<?php
function base64Image($filePath) {
    $imageData = file_get_contents($filePath);
    return 'data:image/png;base64,' . base64_encode($imageData);
}

$logo = base64Image('images/logo.png');
$stamp = base64Image('images/stamp.png');
$signature = base64Image('images/signature.png');
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
            width: 80%;
            margin: 0 auto;
        }

        .footer {
            text-align: center;
        }

        .header h1 {
            font-size: 1.5em;
        }


        .company-info {
            font-weight: 600;
            font-size: 17px;
            color: #2e2e2e;
            line-height: 28px;
        }

        .details-table,
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table td,
        .summary-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .summary-table {
            margin-top: 20px;
        }

        .logo {
            max-width: 200px;
        }

        .stamp {
            max-width: 100px;
            position: absolute;
            bottom: 20px;
            right: 20px;
        }

        .signature {
            max-width: 100px;
        }

        .International {
            display: flex;
            gap: 320px;
            align-items: center;
        }

        .Tax {
            font-size: 35px;
            font-weight: 500;
        }

        .date {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .date2 {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .date-container {
            display: flex;
            align-items: center;
            gap: 30px;
            justify-content: center;
            padding-left: 390px;
            font-size: 19px;
            font-weight: 600;
        }


        .client-info {
            font-weight: 600;
            font-size: 17px;
            color: #2e2e2e;
            line-height: 28px;

        }

        .company-info-div {
            display: flex;
            align-items: last baseline;
            gap: 255px;

        }

        .client-id {
            font-size: 18px;
            font-weight: 600;
        }

        .trn-div {
            display: flex;
            gap: 120px;
            font-size: 18px;
            font-weight: 600;
        }

        .billl {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .bill-container {
            display: flex;
            gap: 60px;
            font-size: 18px;
            font-weight: 600;
            color: #2e2e2e;
        }

        .footer-div {
            display: flex;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
        }

        .total-div {
            font-size: 18px;
            font-weight: 600;
            margin-left: 343px;
        }
    </style>
</head>

<body>
<div class="invoice-container">
    <!-- Logo -->
    <div class="header">
        <img src="<?php echo $logo; ?>" alt="">
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
            <p style="margin: 0;">DATE:</p>
            <p style="margin-top: 0;">#</p>
        </div>
        <div class="date2">
            <p style="margin: 0;">01.09.2024</p>
            <p style="margin-top: 0;">4816</p>
        </div>
    </div>


    <div class="company-info-div">
        <div class="client-info">

            <p>PO Box: 125566 UAE, DUBAI, DEIRA, PORT <br>
                SAEED,PORT SAEED, CITY AVENUE BUILDING, <br>
                M & 4th FLOOR, Office No. 45-0110</p>
        </div>

        <p class="client-id">Client ID:</p>

    </div> <br>

    <div class="trn-div">
        <p>TRN:</p>
        <p>104036354900003</p>
    </div>

    <p class="billl">Bill To:</p>

    <div class="bill-container">
        <div>
            <p>Name:</p>
            <p>email address:</p>
            <p>PO box:</p>
            <p>Phone:</p>
            <p>TRN:</p>
        </div>

        <div>
            <p> Elspeth Mackie</p>
            <p>elspethmac70@gmail.com</p>
            <p>United Arab Emirates</p>
        </div>
    </div>


    <!-- Details Table -->
    <table class="details-table">
        <tr>
            <td>Description</td>
            <td>Quantity</td>
            <td>Amount/pc (AED)</td>
            <td>Total Amount (AED)</td>
            <td>VAT (AED)</td>
        </tr>
        <tr>
            <td>Educational Services</td>
            <td>1</td>
            <td>65.80</td>
            <td>65.80</td>
            <td>3.29</td>
        </tr>
    </table>

    <!-- Summary Table -->
    <table class="summary-table">
        <tr>
            <td><strong>Total Amount (AED):</strong></td>
            <td>65.80</td>
        </tr>
        <tr>
            <td><strong>VAT (AED):</strong></td>
            <td>3.29</td>
        </tr>
        <tr>
            <td><strong>Grand Total (AED):</strong></td>
            <td>69.09</td>
        </tr>
    </table>


    <p class="total-div">TOTAL</p>
    <!-- Stamp and Signature -->
    <div class="footer-div">
        <p style="margin-top: 0;">Settlement currency: AED</p>
        <img src="<?php echo $stamp; ?>" alt="">
        <img src="<?php echo $signature; ?>" alt="">
    </div>
</div>
</body>

</html>