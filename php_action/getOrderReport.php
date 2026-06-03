<?php
require_once 'core.php';

// Ensure server uses PKT time zone
date_default_timezone_set('Asia/Karachi');

if ($_POST) {
    // Validate input
    if (!isset($_POST['startDateTime']) || !isset($_POST['endDateTime'])) {
        echo "Error: Missing start or end date and time.";
        exit;
    }

    $startDateTime = $_POST['startDateTime'];
    $endDateTime = $_POST['endDateTime'];

    // Validate datetime format (Y-m-d\TH:i)
    try {
        $startDateTimeObj = DateTime::createFromFormat('Y-m-d\TH:i', $startDateTime);
        $endDateTimeObj = DateTime::createFromFormat('Y-m-d\TH:i', $endDateTime);

        if (!$startDateTimeObj || !$endDateTimeObj) {
            echo "Error: Invalid datetime format. Please use YYYY-MM-DD HH:MM.";
            exit;
        }

        // Convert to MySQL DATETIME format for comparison
        $start_datetime = $startDateTimeObj->format("Y-m-d H:i:s");
        $end_datetime = $endDateTimeObj->format("Y-m-d H:i:s");

        // Ensure start datetime is not after end datetime
        if ($startDateTimeObj > $endDateTimeObj) {
            echo "Error: Start date and time cannot be after end date and time.";
            exit;
        }

        // Format for display in report
        $startDateTimeDisplay = $startDateTimeObj->format('F j, Y h:i A');
        $endDateTimeDisplay = $endDateTimeObj->format('F j, Y h:i A');
    } catch (Exception $e) {
        echo "Error: Datetime processing failed.";
        exit;
    }

    // Prepare SQL query
    $sql = "SELECT order_datetime, client_name, client_contact, grand_total 
            FROM orders 
            WHERE order_datetime >= ? AND order_datetime <= ? AND order_status = 1";
    $stmt = $connect->prepare($sql);
    if (!$stmt) {
        echo "Error: Database query preparation failed.";
        exit;
    }

    $stmt->bind_param("ss", $start_datetime, $end_datetime);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if logo file exists
    $logoPath = 'custom/images/logo.png';
    $logoHtml = file_exists($_SERVER['DOCUMENT_ROOT'] . '/smartstock/' . $logoPath) 
        ? '<img src="' . $logoPath . '" alt="SmartStock Solutions" class="logo">'
        : '<h2 style="margin: 0; color: #007bff;">SmartStock Solutions</h2>';

    // Generate HTML
    $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Order Report</title>
        <style>
            body {
                font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                margin: 0;
                padding: 20px;
                color: #333;
                line-height: 1.6;
            }
            .report-container {
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
            .report-title {
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
            .orders-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            .orders-table th, .orders-table td {
                border: 1px solid #ddd;
                padding: 10px;
                font-size: 14px;
                text-align: left;
            }
            .orders-table th {
                background: #f8f9fa;
                font-weight: bold;
            }
            .orders-table .text-right {
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
                .report-container {
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
        <div class="report-container">
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
            <div class="report-title">Order Report</div>
            <table class="details-table">
                <tr>
                    <td class="label">Date & Time Range:</td>
                    <td>' . htmlspecialchars($startDateTimeDisplay) . ' to ' . htmlspecialchars($endDateTimeDisplay) . '</td>
                </tr>
            </table>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order Date & Time</th>
                        <th>Client Name</th>
                        <th>Contact</th>
                        <th>Grand Total (PKR)</th>
                    </tr>
                </thead>
                <tbody>';

    $totalAmount = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Format order_datetime for display
            $formattedDateTime = date('F j, Y h:i A', strtotime($row['order_datetime']));
            $html .= '
                    <tr>
                        <td>' . htmlspecialchars($formattedDateTime) . '</td>
                        <td>' . htmlspecialchars($row['client_name']) . '</td>
                        <td>' . htmlspecialchars($row['client_contact']) . '</td>
                        <td class="text-right">' . number_format($row['grand_total'], 2) . '</td>
                    </tr>';
            $totalAmount += $row['grand_total'];
        }
    } else {
        $html .= '
                    <tr>
                        <td colspan="4">No orders found for the selected date range.</td>
                    </tr>';
    }

    $html .= '
                </tbody>
            </table>
            <table class="summary-table">
                <tr>
                    <td class="label">Total Amount:</td>
                    <td class="value">' . number_format($totalAmount, 2) . ' PKR</td>
                </tr>
            </table>
            <div class="clear"></div>
            <div class="footer">
                Generated by SmartStock Solutions on ' . date('Y-m-d') . '
            </div>
        </div>
    </body>
    </html>';

    echo $html;

    $stmt->close();
    $connect->close();
} else {
    echo "Error: Invalid request method.";
}
?>