<?php
require_once 'core.php';

$output = array('data' => array());

try {
    // Select orders with order_datetime formatted for display
    $sql = "SELECT order_id, DATE_FORMAT(order_datetime, '%d %b %Y, %H:%i') AS order_datetime, client_name, client_contact 
            FROM orders 
            WHERE order_status = 1";
    $result = $connect->query($sql);

    if ($result === false) {
        throw new Exception("Query failed: " . $connect->error);
    }

    if ($result->num_rows > 0) {
        $x = 1;

        while ($row = $result->fetch_array()) {
            $orderId = $row['order_id'];

            // Count order items
            $countOrderItemSql = "SELECT COUNT(*) FROM order_item WHERE order_id = ?";
            $stmt = $connect->prepare($countOrderItemSql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $connect->error);
            }
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $itemCountResult = $stmt->get_result();
            $itemCountRow = $itemCountResult->fetch_row();
            $stmt->close();

            // Action buttons with HTML-escaped orderId
            $orderIdEscaped = htmlspecialchars($orderId, ENT_QUOTES, 'UTF-8');
            $button = <<<HTML
            <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Action <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="orders.php?o=editOrd&i={$orderIdEscaped}" id="editOrderModalBtn"><i class="glyphicon glyphicon-edit"></i> Edit</a></li>
                    <li><a type="button" onclick="printOrder({$orderIdEscaped})"><i class="glyphicon glyphicon-print"></i> Print</a></li>
                    <li><a type="button" data-toggle="modal" data-target="#removeOrderModal" id="removeOrderModalBtn" onclick="removeOrder({$orderIdEscaped})"><i class="glyphicon glyphicon-trash"></i> Remove</a></li>
                </ul>
            </div>
HTML;

            $output['data'][] = array(
                $x,
                htmlspecialchars($row['order_datetime'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($row['client_name'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($row['client_contact'], ENT_QUOTES, 'UTF-8'),
                $itemCountRow[0],
                $button
            );
            $x++;
        }
    }

} catch (Exception $e) {
    error_log("fetchOrder.php error: " . $e->getMessage());
    $output['error'] = "An error occurred while fetching orders.";
}

$connect->close();

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($output);
?>