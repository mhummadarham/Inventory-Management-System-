<?php
require_once 'core.php';

$valid['success'] = array('success' => false, 'messages' => array(), 'order_id' => '');

if ($_POST) {
    try {
        // Validate and sanitize orderDateTime
        $orderDateTime = !empty($_POST['orderDateTime']) ? $_POST['orderDateTime'] : null;
        if (!$orderDateTime || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $orderDateTime)) {
            throw new Exception("Invalid or missing order date and time.");
        }
        // Convert to MySQL DATETIME format (YYYY-MM-DD HH:MM:SS)
        $orderDateTime = date('Y-m-d H:i:s', strtotime($orderDateTime));

        $clientName = $_POST['clientName'];
        $clientContact = $_POST['clientContact'];
        $totalAmountValue = $_POST['totalAmountValue'];
        $discount = $_POST['discount'] ?: '0'; // Default to 0 if empty
        $grandTotalValue = $_POST['grandTotalValue'];
        $paid = $_POST['paid'] ?: '0'; // Default to 0 if empty
        $dueValue = $_POST['dueValue'];

        // Sanitize inputs to prevent SQL injection
        $clientName = $connect->real_escape_string($clientName);
        $clientContact = $connect->real_escape_string($clientContact);
        $orderDateTime = $connect->real_escape_string($orderDateTime);

        // Insert into orders table
        $sql = "INSERT INTO orders (order_datetime, client_name, client_contact, total_amount, discount, grand_total, paid, due, order_status) 
                VALUES ('$orderDateTime', '$clientName', '$clientContact', '$totalAmountValue', '$discount', '$grandTotalValue', '$paid', '$dueValue', 1)";

        $order_id = 0;
        $orderStatus = false;
        if ($connect->query($sql) === true) {
            $order_id = $connect->insert_id;
            $valid['order_id'] = $order_id;
            $orderStatus = true;
        } else {
            throw new Exception("Failed to insert order: " . $connect->error);
        }

        // Process order items
        $orderItemStatus = false;
        for ($x = 0; $x < count($_POST['productName']); $x++) {
            $productId = (int)$_POST['productName'][$x];
            $quantity = (int)$_POST['quantity'][$x];
            $rate = (float)$_POST['rateValue'][$x];
            $total = (float)$_POST['totalValue'][$x];

            // Update product quantity
            $updateProductQuantitySql = "SELECT quantity FROM product WHERE product_id = $productId";
            $updateProductQuantityData = $connect->query($updateProductQuantitySql);
            if ($updateProductQuantityData && $updateProductQuantityData->num_rows > 0) {
                $updateProductQuantityResult = $updateProductQuantityData->fetch_row();
                $newQuantity = $updateProductQuantityResult[0] - $quantity;

                // Update product table
                $updateProductTable = "UPDATE product SET quantity = '$newQuantity' WHERE product_id = $productId";
                if (!$connect->query($updateProductTable)) {
                    throw new Exception("Failed to update product quantity: " . $connect->error);
                }

                // Insert into order_item
                $orderItemSql = "INSERT INTO order_item (order_id, product_id, quantity, rate, total, order_item_status) 
                                VALUES ('$order_id', '$productId', '$quantity', '$rate', '$total', 1)";
                if ($connect->query($orderItemSql)) {
                    if ($x == count($_POST['productName']) - 1) {
                        $orderItemStatus = true;
                    }
                } else {
                    throw new Exception("Failed to insert order item: " . $connect->error);
                }
            } else {
                throw new Exception("Product not found or query failed for product_id: $productId");
            }
        }

        if ($orderStatus && $orderItemStatus) {
            $valid['success'] = true;
            $valid['messages'] = "Successfully Added";
        } else {
            throw new Exception("Order creation incomplete: OrderStatus=$orderStatus, OrderItemStatus=$orderItemStatus");
        }

    } catch (Exception $e) {
        $valid['success'] = false;
        $valid['messages'] = "Error: " . $e->getMessage();
    }

    $connect->close();
    echo json_encode($valid);
}
?>