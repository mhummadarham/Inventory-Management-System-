<?php
require_once 'core.php';

if (!isset($_POST['orderId']) || !is_numeric($_POST['orderId'])) {
    echo 'Error: Invalid or missing order ID.';
    exit;
}

$orderId = (int)$_POST['orderId'];

// Fetch order details
$sql = "SELECT order_datetime, client_name, client_contact, total_amount, discount, grand_total, paid, due 
        FROM orders WHERE order_id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$orderResult = $stmt->get_result();

if ($orderResult->num_rows === 0) {
    echo 'Error: Order not found.';
    $stmt->close();
    $connect->close();
    exit;
}

$orderData = $orderResult->fetch_array(MYSQLI_NUM);
$orderDateTime = date('F j, Y h:i A', strtotime($orderData[0])); // Format as "May 14, 2025 09:40 PM"
$clientName = $connect->real_escape_string($orderData[1]);
$clientContact = $connect->real_escape_string($orderData[2]);
$totalAmount = number_format($orderData[3], 2);
$discount = number_format($orderData[4], 2);
$grandTotal = number_format($orderData[5], 2);
$paid = number_format($orderData[6], 2);
$due = number_format($orderData[7], 2);

// Fetch order items
$orderItemSql = "SELECT order_item.product_id, order_item.rate, order_item.quantity, order_item.total, product.product_name 
                 FROM order_item INNER JOIN product ON order_item.product_id = product.product_id 
                 WHERE order_item.order_id = ?";
$orderItemStmt = $connect->prepare($orderItemSql);
$orderItemStmt->bind_param("i", $orderId);
$orderItemStmt->execute();
$orderItemResult = $orderItemStmt->get_result();

// Check if logo file exists
$logoPath = 'custom/images/logo.png';
$logoHtml = file_exists($_SERVER['DOCUMENT_ROOT'] . '/smartstock/' . $logoPath) 
    ? '<img src="' . $logoPath . '" alt="SmartStock Solutions" class="logo">'
    : '<h2 style="margin: 0; color: #007bff;">SmartStock Solutions</h2>';

$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Invoice</title>
    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #e0e0e0;
            padding: 20px;
            background: #fff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header img.logo {
            max-height: 60px;
            background: transparent;
        }
        .header .company-details {
            text-align: right;
            font-size: 14px;
        }
        .header .company-details h2 {
            margin: 0;
            color: #007bff;
            font-size: 22px;
        }
        .invoice-title {
            text-align: center;
            font-size: 24px;
            margin: 20px 0;
            color: #333;
        }
        .details-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .details-table td {
            padding: 8px;
            font-size: 14px;
            vertical-align: top;
        }
        .details-table .label {
            font-weight: bold;
            width: 20%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 14px;
            text-align: left;
        }
        .items-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .items-table .text-right {
            text-align: right;
        }
        .summary-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 8px;
            font-size: 14px;
        }
        .summary-table .label {
            font-weight: bold;
            text-align: right;
        }
        .summary-table .value {
            text-align: right;
        }
        .footer {
            margin-top: 20px;
            border-top: 1px solid #e0e0e0;
            padding-top: 10px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .clear {
            clear: both;
        }
        @media print {
            body {
                margin: 0;
            }
            .invoice-container {
                border: none;
                padding: 0;
            }
            .header img.logo {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div class="company-logo">
                ' . $logoHtml . '
            </div>
            <div class="company-details">
                <h2>SmartStock Solutions</h2>
                123 Business Avenue, Karachi, Pakistan<br>
                Phone: +92 123 456 7890<br>
                Email: info@smartstock.pk<br>
                Website: www.smartstock.pk
            </div>
        </div>
        <div class="invoice-title">Order Invoice</div>
        <table class="details-table">
            <tr>
                <td class="label">Invoice Number:</td>
                <td>INV-' . sprintf("%06d", $orderId) . '</td>
                <td class="label">Order Date & Time:</td>
                <td>' . htmlspecialchars($orderDateTime) . '</td>
            </tr>
            <tr>
                <td class="label">Client Name:</td>
                <td>' . htmlspecialchars($clientName) . '</td>
                <td class="label">Contact:</td>
                <td>' . htmlspecialchars($clientContact) . '</td>
            </tr>
        </table>
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Rate (PKR)</th>
                    <th>Quantity</th>
                    <th>Total (PKR)</th>
                </tr>
            </thead>
            <tbody>';

$x = 1;
while ($row = $orderItemResult->fetch_array(MYSQLI_NUM)) {
    $html .= '
                <tr>
                    <td>' . $x . '</td>
                    <td>' . htmlspecialchars($row[4]) . '</td>
                    <td class="text-right">' . number_format($row[1], 2) . '</td>
                    <td class="text-right">' . $row[2] . '</td>
                    <td class="text-right">' . number_format($row[3], 2) . '</td>
                </tr>';
    $x++;
}

$html .= '
            </tbody>
        </table>
        <table class="summary-table">
            <tr>
                <td class="label">Total Amount:</td>
                <td class="value">' . $totalAmount . ' PKR</td>
            </tr>
            <tr>
                <td class="label">Discount:</td>
                <td class="value">' . $discount . ' PKR</td>
            </tr>
            <tr>
                <td class="label">Grand Total:</td>
                <td class="value">' . $grandTotal . ' PKR</td>
            </tr>
            <tr>
                <td class="label">Paid Amount:</td>
                <td class="value">' . $paid . ' PKR</td>
            </tr>
            <tr>
                <td class="label">Due Amount:</td>
                <td class="value">' . $due . ' PKR</td>
            </tr>
        </table>
        <div class="clear"></div>
        <div class="footer">
            Thank you for your business! For inquiries, contact us at info@smartstock.pk.
        </div>
    </div>
</body>
</html>';

echo $html;

$orderItemStmt->close();
$stmt->close();
$connect->close();
?>