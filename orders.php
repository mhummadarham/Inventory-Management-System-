<?php 
require_once 'php_action/db_connect.php'; 
require_once 'includes/header.php'; 

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not authenticated
if (!isset($_SESSION['userId'])) {
    header('location: http://localhost/smartstock/index.php');
    exit();
}

// Restrict admin from accessing add order section and staff from edit order section
if (isset($_GET['o'])) {
    if ($_GET['o'] == 'add' && $_SESSION['role'] === 'admin') {
        header('location: http://localhost/smartstock/dashboard.php');
        exit();
    }
    if ($_GET['o'] == 'editOrd' && $_SESSION['role'] !== 'admin') {
        header('location: http://localhost/smartstock/dashboard.php');
        exit();
    }
}

if($_GET['o'] == 'add') { 
    // add order
    echo "<div class='div-request div-hide'>add</div>";
} else if($_GET['o'] == 'manord') { 
    echo "<div class='div-request div-hide'>manord</div>";
} else if($_GET['o'] == 'editOrd') { 
    echo "<div class='div-request div-hide'>editOrd</div>";
} // /else manage order
?>

<ol class="breadcrumb">
  <li><a href="dashboard.php">Home</a></li>
  <li>Order</li>
  <li class="active">
      <?php if($_GET['o'] == 'add') { ?>
          Add Order
      <?php } else if($_GET['o'] == 'manord') { ?>
          Manage Order
      <?php } else if($_GET['o'] == 'editOrd') { ?>
          Edit Order
      <?php } ?>
  </li>
</ol>

<h4>
    <i class='glyphicon glyphicon-circle-arrow-right'></i>
    <?php if($_GET['o'] == 'add') {
        echo "Add Order";
    } else if($_GET['o'] == 'manord') { 
        echo "Manage Order";
    } else if($_GET['o'] == 'editOrd') { 
        echo "Edit Order";
    }
    ?>  
</h4>

<div class="panel panel-default">
    <div class="panel-heading">
        <?php if($_GET['o'] == 'add') { ?>
            <i class="glyphicon glyphicon-plus-sign"></i> Add Order
        <?php } else if($_GET['o'] == 'manord') { ?>
            <i class="glyphicon glyphicon-edit"></i> Manage Order
        <?php } else if($_GET['o'] == 'editOrd') { ?>
            <i class="glyphicon glyphicon-edit"></i> Edit Order
        <?php } ?>
    </div> <!--/panel-->    
    <div class="panel-body">
            
        <?php if($_GET['o'] == 'add') { 
            // add order
            ?>          
            <div class="success-messages"></div> <!--/success-messages-->

            <form class="form-horizontal" method="POST" action="php_action/createOrder.php" id="createOrderForm">
                <div class="form-group">
                    <label for="orderDateTime" class="col-sm-2 control-label">Order Date & Time</label>
                    <div class="col-sm-10">
                        <input type="datetime-local" class="form-control" id="orderDateTime" name="orderDateTime" autocomplete="off" />
                    </div>
                </div> <!--/form-group-->
                <div class="form-group">
                    <label for="clientName" class="col-sm-2 control-label">Client Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="clientName" name="clientName" placeholder="Client Name" autocomplete="off" />
                    </div>
                </div> <!--/form-group-->
                <div class="form-group">
                    <label for="clientContact" class="col-sm-2 control-label">Client Contact</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="clientContact" name="clientContact" placeholder="Contact Number" autocomplete="off" />
                    </div>
                </div> <!--/form-group-->              

                <table class="table" id="productTable">
                    <thead>
                        <tr>                            
                            <th style="width:40%;">Product</th>
                            <th style="width:20%;">Rate</th>
                            <th style="width:15%;">Quantity</th>                            
                            <th style="width:15%;">Total</th>                            
                            <th style="width:10%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $arrayNumber = 0;
                        for($x = 1; $x < 4; $x++) { ?>
                            <tr id="row<?php echo $x; ?>" class="<?php echo $arrayNumber; ?>">                             
                                <td style="margin-left:20px;">
                                    <div class="form-group">
                                    <select class="form-control" name="productName[]" id="productName<?php echo $x; ?>" onchange="getProductData(<?php echo $x; ?>)" >
                                        <option value="">~~SELECT~~</option>
                                        <?php
                                            $productSql = "SELECT * FROM product WHERE active = 1 AND status = 1 AND quantity != 0";
                                            $productData = $connect->query($productSql);
                                            while($row = $productData->fetch_array()) {                                      
                                                echo "<option value='".$row['product_id']."' id='changeProduct".$row['product_id']."'>".$row['product_name']."</option>";
                                            } // /while 
                                        ?>
                                    </select>
                                    </div>
                                </td>
                                <td style="padding-left:20px;">                                  
                                    <input type="text" name="rate[]" id="rate<?php echo $x; ?>" autocomplete="off" disabled="true" class="form-control" />                                  
                                    <input type="hidden" name="rateValue[]" id="rateValue<?php echo $x; ?>" autocomplete="off" class="form-control" />                                  
                                </td>
                                <td style="padding-left:20px;">
                                    <div class="form-group">
                                    <input type="number" name="quantity[]" id="quantity<?php echo $x; ?>" onkeyup="getTotal(<?php echo $x ?>)" autocomplete="off" class="form-control" min="1" />
                                    </div>
                                </td>
                                <td style="padding-left:20px;">                                  
                                    <input type="text" name="total[]" id="total<?php echo $x; ?>" autocomplete="off" class="form-control" disabled="true" />                                  
                                    <input type="hidden" name="totalValue[]" id="totalValue<?php echo $x; ?>" autocomplete="off" class="form-control" />                                  
                                </td>
                                <td>
                                    <button class="btn btn-default removeProductRowBtn" type="button" id="removeProductRowBtn" onclick="removeProductRow(<?php echo $x; ?>)"><i class="glyphicon glyphicon-trash"></i></button>
                                </td>
                            </tr>
                        <?php
                        $arrayNumber++;
                        } // /for
                        ?>
                    </tbody>                            
                </table>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="totalAmount" class="col-sm-3 control-label">Total Amount</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="totalAmount" name="totalAmount" disabled="true"/>
                            <input type="hidden" class="form-control" id="totalAmountValue" name="totalAmountValue" />
                        </div>
                    </div> <!--/form-group-->              
                    <div class="form-group">
                        <label for="discount" class="col-sm-3 control-label">Discount</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="discount" name="discount" onkeyup="discountFunc()" autocomplete="off" />
                        </div>
                    </div> <!--/form-group-->    
                    <div class="form-group">
                        <label for="grandTotal" class="col-sm-3 control-label">Grand Total</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="grandTotal" name="grandTotal" disabled="true" />
                            <input type="hidden" class="form-control" id="grandTotalValue" name="grandTotalValue" />
                        </div>
                    </div> <!--/form-group-->                            
                </div> <!--/col-md-6-->

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="paid" class="col-sm-3 control-label">Paid Amount</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="paid" name="paid" autocomplete="off" onkeyup="paidAmount()" />
                        </div>
                    </div> <!--/form-group-->              
                    <div class="form-group">
                        <label for="due" class="col-sm-3 control-label">Due Amount</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="due" name="due" disabled="true" />
                            <input type="hidden" class="form-control" id="dueValue" name="dueValue" />
                        </div>
                    </div> <!--/form-group-->                          
                </div> <!--/col-md-6-->

                <div class="form-group submitButtonFooter">
                    <div class="col-sm-offset-2 col-sm-10">
                    <button type="button" class="btn btn-default" onclick="addRow()" id="addRowBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-plus-sign"></i> Add Row </button>
                        <button type="submit" id="createOrderBtn" data-loading-text="Loading..." class="btn btn-success"><i class="glyphicon glyphicon-ok-sign"></i> Save Changes</button>
                        <button type="reset" class="btn btn-default" onclick="resetOrderForm()"><i class="glyphicon glyphicon-erase"></i> Reset</button>
                    </div>
                </div>
            </form>
        <?php } else if($_GET['o'] == 'manord') { 
            // manage order
            ?>
            <div id="success-messages"></div>
            
            <table class="table" id="manageOrderTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order Date</th>
                        <th>Client Name</th>
                        <th>Contact</th>
                        <th>Total Order Item</th>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { ?>
                        <th>Option</th>
                        <?php } ?>
                    </tr>
                </thead>
            </table>
        <?php 
        // /else manage order
        } else if($_GET['o'] == 'editOrd') {
            // get order
            ?>
            <div class="success-messages"></div> <!--/success-messages-->

            <form class="form-horizontal" method="POST" action="php_action/editOrder.php" id="editOrderForm">
                <?php $orderId = $_GET['i'];
                $sql = "SELECT orders.order_id, orders.order_datetime, orders.client_name, orders.client_contact, orders.total_amount, orders.discount, orders.grand_total, orders.paid, orders.due FROM orders WHERE orders.order_id = {$orderId}";
                $result = $connect->query($sql);
                $data = $result->fetch_row();
                $orderDateTime = date('Y-m-d\TH:i', strtotime($data[1])); // Format for datetime-local
                ?>

                <div class="form-group">
                    <label for="orderDateTime" class="col-sm-2 control-label">Order Date & Time</label>
                    <div class="col-sm-10">
                        <input type="datetime-local" class="form-control" id="orderDateTime" name="orderDateTime" autocomplete="off" value="<?php echo $orderDateTime; ?>" />
                    </div>
                </div> <!--/form-group-->
                <div class="form-group">
                    <label for="clientName" class="col-sm-2 control-label">Client Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="clientName" name="clientName" placeholder="Client Name" autocomplete="off" value="<?php echo $data[2] ?>" />
                    </div>
                </div> <!--/form-group-->
                <div class="form-group">
                    <label for="clientContact" class="col-sm-2 control-label">Client Contact</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="clientContact" name="clientContact" placeholder="Contact Number" autocomplete="off" value="<?php echo $data[3] ?>" />
                    </div>
                </div> <!--/form-group-->              
                <table class="table" id="productTable">
                    <thead>
                        <tr>                            
                            <th style="width:40%;">Product</th>
                            <th style="width:20%;">Rate</th>
                            <th style="width:15%;">Quantity</th>                            
                            <th style="width:15%;">Total</th>                            
                            <th style="width:10%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $orderItemSql = "SELECT order_item.order_item_id, order_item.order_id, order_item.product_id, order_item.quantity, order_item.rate, order_item.total FROM order_item WHERE order_item.order_id = {$orderId}";
                        $orderItemResult = $connect->query($orderItemSql);
                        $arrayNumber = 0;
                        $x = 1;
                        while($orderItemData = $orderItemResult->fetch_array()) { 
                            ?>
                            <tr id="row<?php echo $x; ?>" class="<?php echo $arrayNumber; ?>">                             
                                <td style="margin-left:20px;">
                                    <div class="form-group">
                                    <select class="form-control" name="productName[]" id="productName<?php echo $x; ?>" onchange="getProductData(<?php echo $x; ?>)" >
                                        <option value="">~~SELECT~~</option>
                                        <?php
                                            $productSql = "SELECT * FROM product WHERE active = 1 AND status = 1 AND quantity != 0";
                                            $productData = $connect->query($productSql);
                                            while($row = $productData->fetch_array()) {                                      
                                                $selected = "";
                                                if($row['product_id'] == $orderItemData['product_id']) {
                                                    $selected = "selected";
                                                } else {
                                                    $selected = "";
                                                }
                                                echo "<option value='".$row['product_id']."' id='changeProduct".$row['product_id']."' ".$selected." >".$row['product_name']."</option>";
                                            } // /while 
                                        ?>
                                    </select>
                                    </div>
                                </td>
                                <td style="padding-left:20px;">                                  
                                    <input type="text" name="rate[]" id="rate<?php echo $x; ?>" autocomplete="off" disabled="true" class="form-control" value="<?php echo $orderItemData['rate']; ?>" />                                  
                                    <input type="hidden" name="rateValue[]" id="rateValue<?php echo $x; ?>" autocomplete="off" class="form-control" value="<?php echo $orderItemData['rate']; ?>" />                                  
                                </td>
                                <td style="padding-left:20px;">
                                    <div class="form-group">
                                    <input type="number" name="quantity[]" id="quantity<?php echo $x; ?>" onkeyup="getTotal(<?php echo $x ?>)" autocomplete="off" class="form-control" min="1" value="<?php echo $orderItemData['quantity']; ?>" />
                                    </div>
                                </td>
                                <td style="padding-left:20px;">                                  
                                    <input type="text" name="total[]" id="total<?php echo $x; ?>" autocomplete="off" class="form-control" disabled="true" value="<?php echo $orderItemData['total']; ?>"/>                                  
                                    <input type="hidden" name="totalValue[]" id="totalValue<?php echo $x; ?>" autocomplete="off" class="form-control" value="<?php echo $orderItemData['total']; ?>"/>                                  
                                </td>
                                <td>
                                    <button class="btn btn-default removeProductRowBtn" type="button" id="removeProductRowBtn" onclick="removeProductRow(<?php echo $x; ?>)"><i class="glyphicon glyphicon-trash"></i></button>
                                </td>
                            </tr>
                        <?php
                        $arrayNumber++;
                        $x++;
                        } // /for
                        ?>
                    </tbody>                            
                </table>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="totalAmount" class="col-sm-3 control-label">Total Amount</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="totalAmount" name="totalAmount" disabled="true" value="<?php echo $data[4] ?>" />
                            <input type="hidden" class="form-control" id="totalAmountValue" name="totalAmountValue" value="<?php echo $data[4] ?>"  />
                        </div>
                    </div> <!--/form-group-->              
                    <div class="form-group">
                        <label for="discount" class="col-sm-3 control-label">Discount</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="discount" name="discount" onkeyup="discountFunc()" autocomplete="off" value="<?php echo $data[5] ?>" />
                        </div>
                    </div> <!--/form-group-->    
                    <div class="form-group">
                        <label for="grandTotal" class="col-sm-3 control-label">Grand Total</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="grandTotal" name="grandTotal" disabled="true" value="<?php echo $data[6] ?>"  />
                            <input type="hidden" class="form-control" id="grandTotalValue" name="grandTotalValue" value="<?php echo $data[6] ?>"  />
                        </div>
                    </div> <!--/form-group-->                            
                </div> <!--/col-md-6-->

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="paid" class="col-sm-3 control-label">Paid Amount</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="paid" name="paid" autocomplete="off" onkeyup="paidAmount()" value="<?php echo $data[7] ?>"  />
                        </div>
                    </div> <!--/form-group-->              
                    <div class="form-group">
                        <label for="due" class="col-sm-3 control-label">Due Amount</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="due" name="due" disabled="true" value="<?php echo $data[8] ?>"  />
                            <input type="hidden" class="form-control" id="dueValue" name="dueValue" value="<?php echo $data[8] ?>"  />
                        </div>
                    </div> <!--/form-group-->                          
                </div> <!--/col-md-6-->

                <div class="form-group editButtonFooter">
                    <div class="col-sm-offset-2 col-sm-10">
                    <button type="button" class="btn btn-default" onclick="addRow()" id="addRowBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-plus-sign"></i> Add Row </button>
                    <input type="hidden" name="orderId" id="orderId" value="<?php echo $_GET['i']; ?>" />
                    <button type="submit" id="editOrderBtn" data-loading-text="Loading..." class="btn btn-success"><i class="glyphicon glyphicon-ok-sign"></i> Save Changes</button>
                    </div>
                </div>
            </form>
            <?php
        } // /get order else  ?>
    </div> <!--/panel-->    
</div> <!--/panel-->    

<!-- remove order -->
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { ?>
<div class="modal fade" tabindex="-1" role="dialog" id="removeOrderModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove Order</h4>
      </div>
      <div class="modal-body">
        <div class="removeOrderMessages"></div>
        <p>Do you really want to remove ?</p>
      </div>
      <div class="modal-footer removeProductFooter">
        <button type="button" class="btn btn-default" data-dismiss="modal"> <i class="glyphicon glyphicon-remove-sign"></i> Close</button>
        <button type="button" class="btn btn-primary" id="removeOrderBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-ok-sign"></i> Save changes</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php } ?>
<!-- /remove order-->

<script src="custom/js/order.js"></script>

<script>
// Set the default value of orderDateTime to the user's local system date and time
document.addEventListener('DOMContentLoaded', function() {
    const orderDateTimeInput = document.getElementById('orderDateTime');
    if (orderDateTimeInput) {
        const now = new Date();
        // Format date and time as YYYY-MM-DDTHH:MM
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const localDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        orderDateTimeInput.value = localDateTime;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>