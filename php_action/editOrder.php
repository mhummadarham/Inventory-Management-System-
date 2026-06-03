<?php
require_once 'core.php';

$valid['success'] = array('success' => false, 'messages' => array());

if ($_POST) {
    try {
        $orderId = filter_input(INPUT_POST, 'orderId', FILTER_VALIDATE_INT);
        if (!$orderId) {
            throw new Exception("Invalid order ID.");
        }

        $orderDateTime = !empty($_POST['orderDateTime']) ? $_POST['orderDateTime'] : null;
        if (!$orderDateTime || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $orderDateTime)) {
            throw new Exception("Invalid or missing order date and time.");
        }
        $orderDateTime = date('Y-m-d H:i:s', strtotime($orderDateTime));

        $clientName = $connect->real_escape_string($_POST['clientName']);
        $clientContact = $connect->real_escape_string($_POST['clientContact']);
        $subTotalValue = filter_input(INPUT_POST, 'subTotalValue', FILTER_SANITIZE_STRING);
        $totalAmountValue = filter_input(INPUT_POST, 'totalAmountValue', FILTER_SANITIZE_STRING);
        $discount = filter_input(INPUT_POST, 'discount', FILTER_SANITIZE_STRING) ?: '0';
        $grandTotalValue = filter_input(INPUT_POST, 'grandTotalValue', FILTER_SANITIZE_STRING);
        $paid = filter_input(INPUT_POST, 'paid', FILTER_SANITIZE_STRING) ?: '0';
        $dueValue = filter_input(INPUT_POST, 'dueValue', FILTER_SANITIZE_STRING);
        $paymentType = filter_input(INPUT_POST, 'paymentType', FILTER_VALIDATE_INT);
        $paymentStatus = filter_input(INPUT_POST, 'paymentStatus', FILTER_VALIDATE_INT);

        if (!$clientName || !$clientContact || !$subTotalValue || !$totalAmountValue || !$grandTotalValue || !$dueValue || !$paymentType || !$paymentStatus) {
            throw new Exception("Missing required fields.");
        }

        // Update orders table
        $sql = "UPDATE orders SET order_datetime = '$orderDateTime', client_name = '$clientName', client_contact = '$clientContact', 
                sub_total = '$subTotalValue', total_amount = '$totalAmountValue', discount = '$discount', 
                grand_total = '$grandTotalValue', paid = '$paid', due = '$dueValue', payment_type = '$paymentType', 
                payment_status = '$paymentStatus', order_status = 1 WHERE order_id = '$orderId'";
        
        if (!$connect->query($sql)) {
            throw new Exception("Failed to update order: " . $connect->error);
        }

        // Remove existing order items
        $deleteOrderItems = "DELETE FROM order_item WHERE order_id = '$orderId'";
        if (!$connect->query($deleteOrderItems)) {
            throw new Exception("Failed to delete existing order items: " . $connect->error);
        }

        // Insert new order items and update product quantities
        for ($x = 0; $x < count($_POST['productName']); $x++) {
            $productId = filter_input(INPUT_POST, "productName][$x]", FILTER_VALIDATE_INT);
            $quantity = filter_input(INPUT_POST, "quantity][$x]", FILTER_VALIDATE_INT);
            $rate = filter_input(INPUT_POST, "rateValue][$x]", FILTER_SANITIZE_STRING);
            $total = filter_input(INPUT_POST, "totalValue][$x]", FILTER_SANITIZE_STRING);

            if (!$productId || !$quantity || !$rate || !$total) {
                throw new Exception("Invalid order item data at index $x.");
            }

            // Insert order item
            $orderItemSql = "INSERT INTO order_item (order_id, product_id, quantity, rate, total, order_item_status) 
                            VALUES ('$orderId', '$productId', '$quantity', '$rate', '$total', 1)";
            if (!$connect->query($orderItemSql)) {
                throw new Exception("Failed to insert order item: " . $connect->error);
            }

            // Update product quantity
            $updateProductQuantitySql = "SELECT quantity FROM product WHERE product_id = $productId";
            $updateProductQuantityData = $connect->query($updateProductQuantitySql);
            if ($updateProductQuantityData && $updateProductQuantityData->num_rows > 0) {
                $updateProductQuantityResult = $updateProductQuantityData->fetch_row();
                $newQuantity = $updateProductQuantityResult[0] - $quantity;
                if ($newQuantity < 0) {
                    throw new Exception("Insufficient product quantity for product ID $productId.");
                }
                $updateProductTable = "UPDATE product SET quantity = '$newQuantity' WHERE product_id = $productId";
                if (!$connect->query($updateProductTable)) {
                    throw new Exception("Failed to update product quantity: " . $connect->error);
                }
            } else {
                throw new Exception("Product not found: ID $productId.");
            }
        }

        $valid['success'] = true;
        $valid['messages'] = "Successfully Updated";

    } catch (Exception $e) {
        $valid['success'] = false;
        $valid['messages'] = "Error: " . $e->getMessage();
    }

    $connect->close();
    echo json_encode($valid);
}
?>