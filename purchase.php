<?php require_once 'php_action/db_connect.php'; ?>
<?php require_once 'includes/header.php'; ?>

<div class="container mx-auto px-4 py-8 bg-slate-200">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="flex space-x-2 text-sm text-slate-600">
            <li><a href="dashboard.php" class="text-cyan-700 hover:text-cyan-800">Home</a></li>
            <li class="before:content-['/'] before:mx-2">Purchase</li>
        </ol>
    </nav>

    <div class="bg-slate-100 rounded-lg shadow-lg border border-slate-300">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold text-slate-800 underline-cyan">Manage Purchases</h2>
                <button class="px-4 py-2 bg-cyan-700 text-white rounded-md hover:bg-cyan-800 transition" data-toggle="modal" data-target="#addPurchaseModal">
                    <i class="glyphicon glyphicon-plus-sign"></i> Add Purchase
                </button>
            </div>

            <div class="remove-messages"></div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border border-slate-300" id="managePurchaseTable">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="p-3 text-slate-800">Purchase Date</th>
                            <th class="p-3 text-slate-800">Supplier Name</th>
                            <th class="p-3 text-slate-800">Product</th>
                            <th class="p-3 text-slate-800">Quantity</th>
                            <th class="p-3 text-slate-800">Rate (PKR)</th>
                            <th class="p-3 text-slate-800">Total (PKR)</th>
                            <th class="p-3 text-slate-800 w-1/6">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTable will populate this -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Purchase Modal -->
    <div class="modal fade" id="addPurchaseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content bg-white rounded-lg shadow-lg border border-slate-300">
                <form class="p-6" id="submitPurchaseForm" action="php_action/createPurchase.php" method="POST">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-xl font-semibold text-slate-800"><i class="fa fa-plus"></i> Add Purchase</h4>
                        <button type="button" class="text-slate-600 hover:text-slate-800" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div id="add-purchase-messages"></div>

                    <div class="mb-4">
                        <label for="purchaseDate" class="block text-sm font-medium text-slate-800">Purchase Date</label>
                        <input type="text" class="mt-1 block w-full border border-slate-300 rounded-md p-2 focus:ring-cyan-700 focus:border-cyan-700" id="purchaseDate" name="purchaseDate" autocomplete="off">
                    </div>
                    <div class="mb-4">
                        <label for="supplierName" class="block text-sm font-medium text-slate-800">Supplier Name</label>
                        <input type="text" class="mt-1 block w-full border border-slate-300 rounded-md p-2 focus:ring-cyan-700 focus:border-cyan-700" id="supplierName" name="supplierName" placeholder="Supplier Name" autocomplete="off">
                    </div>
                    <div class="mb-4">
                        <label for="supplierContact" class="block text-sm font-medium text-slate-800">Supplier Contact</label>
                        <input type="text" class="mt-1 block w-full border border-slate-300 rounded-md p-2 focus:ring-cyan-700 focus:border-cyan-700" id="supplierContact" name="supplierContact" placeholder="Contact Number" autocomplete="off">
                    </div>
                    <div class="mb-4">
                        <label for="productId" class="block text-sm font-medium text-slate-800">Product</label>
                        <select class="mt-1 block w-full border border-slate-300 rounded-md p-2 focus:ring-cyan-700 focus:border-cyan-700" id="productId" name="productId">
                            <option value="">~~SELECT~~</option>
                            <?php
                            $productSql = "SELECT product_id, product_name FROM product WHERE status = 1 AND active = 1";
                            $productResult = $connect->query($productSql);
                            while ($row = $productResult->fetch_array()) {
                                echo "<option value='{$row[0]}'>{$row[1]}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="quantity" class="block text-sm font-medium text-slate-800">Quantity</label>
                        <input type="number" class="mt-1 block w-full border border-slate-300 rounded-md p-2 focus:ring-cyan-700 focus:border-cyan-700" id="quantity" name="quantity" min="1" autocomplete="off">
                    </div>
                    <div class="mb-4">
                        <label for="rate" class="block text-sm font-medium text-slate-800">Rate (PKR)</label>
                        <input type="text" class="mt-1 block w-full border border-slate-300 rounded-md p-2 focus:ring-cyan-700 focus:border-cyan-700" id="rate" name="rate" placeholder="Rate" autocomplete="off">
                    </div>
                    <div class="mb-4">
                        <label for="total" class="block text-sm font-medium text-slate-800">Total (PKR)</label>
                        <input type="text" class="mt-1 block w-full border border-slate-300 rounded-md p-2 focus:ring-cyan-700 focus:border-cyan-700" id="total" name="total" disabled>
                        <input type="hidden" id="totalValue" name="totalValue">
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" class="px-4 py-2 bg-slate-400 text-white rounded-md hover:bg-slate-500 transition" data-dismiss="modal">Close</button>
                        <button type="submit" class="px-4 py-2 bg-cyan-700 text-white rounded-md hover:bg-cyan-800 transition" id="createPurchaseBtn" data-loading-text="Loading...">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Purchase Modal -->
    <div class="modal fade" id="editPurchaseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content bg-white rounded-lg shadow-lg border border-slate-300">
                <form class="p-6" id="editPurchaseForm" action="php_action/editPurchase.php" method="POST">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-xl font-semibold text-slate-800"><i class="fa fa-edit"></i> Edit Purchase</h4>
                        <button type="button" class="text-slate-600 hover:text-slate-800" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div id="edit-purchase-messages"></div>

                    <div class="modal-loading div-hide text-center py-12">
                        <i class="fa fa-spinner fa-pulse fa-3x fa-fw text-cyan-700"></i>
                        <span class="sr-only">Loading...</span>
                    </div>

                    <div class="edit-purchase-result">
                        <input type="hidden" id="editPurchaseId" name="purchaseId">
                        <div class="mb-4">
                            <label for="editPurchaseDate" class="block text-sm font-medium text-slate-800">Purchase Date</label>
                            <input type="text" class="mt-1 block w-full border border-slate-300 rounded-md p-2 focus:ring-cyan-700 focus:border-cyan-700" id="editPurchaseDate" name="purchaseDate" autocomplete="off">
                        </div>
                        <div class="mb-4">
                            <label for="editSupplierName" class="block text-sm font-medium text-slate-800">Supplier Name</label>
                            <input type="text" class="mt-1 block w-full border border-slate-300 rounded-md p-2 focus:ring-cyan-700 focus:border-cyan-700" id="editSupplierName" name="supplierName" placeholder="Supplier Name" autocomplete="off">
                        </div>
                        <div class="mb-4">
                            <label for="editSupplierContact" class="block text-sm font-medium text-slate-800">Supplier Contact</label>
                            <input type="text" class="mt-1 block w-full border border-slate-300 rounded-md p-2 focus:ring-cyan-700 focus:border-cyan-700" id="editSupplierContact" name="supplierContact" placeholder="Contact Number" autocomplete="off">
                        </div>
                        <div class="mb-4">
                            <label for="editProductId" class="block text-sm font-medium text-slate-800">Product</label>
                            <select class="mt-1 block w-full border border-slate-300 rounded-md p-2 focus:ring-cyan-700 focus:border-cyan-700" id="editProductId" name="productId">
                                <option value="">~~SELECT~~</option>
                                <?php
                                $productSql = "SELECT product_id, product_name FROM product WHERE status = 1 AND active = 1";
                                $productResult = $connect->query($productSql);
                                while ($row = $productResult->fetch_array()) {
                                    echo "<option value='{$row[0]}'>{$row[1]}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="editQuantity" class="block text-sm font-medium text-slate-800">Quantity</label>
                            <input type="number" class="mt-1 block w-full border border-slate-300 rounded-md p-2 focus:ring-cyan-700 focus:border-cyan-700" id="editQuantity" name="quantity" min="1" autocomplete="off">
                        </div>
                        <div class="mb-4">
                            <label for="editRate" class="block text-sm font-medium text-slate-800">Rate (PKR)</label>
                            <input type="text" class="mt-1 block w-full border border-slate-300 rounded-md p-2 focus:ring-cyan-700 focus:border-cyan-700" id="editRate" name="rate" placeholder="Rate" autocomplete="off">
                        </div>
                        <div class="mb-4">
                            <label for="editTotal" class="block text-sm font-medium text-slate-800">Total (PKR)</label>
                            <input type="text" class="mt-1 block w-full border border-slate-300 rounded-md p-2 focus:ring-cyan-700 focus:border-cyan-700" id="editTotal" name="total" disabled>
                            <input type="hidden" id="editTotalValue" name="totalValue">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" class="px-4 py-2 bg-slate-400 text-white rounded-md hover:bg-slate-500 transition" data-dismiss="modal">
                            <i class="glyphicon glyphicon-remove-sign"></i> Close
                        </button>
                        <button type="submit" class="px-4 py-2 bg-cyan-700 text-white rounded-md hover:bg-cyan-800 transition" id="editPurchaseBtn" data-loading-text="Loading...">
                            <i class="glyphicon glyphicon-ok-sign"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Remove Purchase Modal -->
    <div class="modal fade" id="removePurchaseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content bg-white rounded-lg shadow-lg border border-slate-300">
                <div class="flex justify-between items-center p-6 border-b border-slate-300">
                    <h4 class="text-xl font-semibold text-slate-800"><i class="glyphicon glyphicon-trash"></i> Remove Purchase</h4>
                    <button type="button" class="text-slate-600 hover:text-slate-800" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="p-6">
                    <div class="removePurchaseMessages"></div>
                    <p class="text-slate-600">Do you really want to remove this purchase?</p>
                </div>
                <div class="flex justify-end space-x-2 p-6 border-t border-slate-300">
                    <button type="button" class="px-4 py-2 bg-slate-400 text-white rounded-md hover:bg-slate-500 transition" data-dismiss="modal">
                        <i class="glyphicon glyphicon-remove-sign"></i> Close
                    </button>
                    <button type="button" class="px-4 py-2 bg-cyan-700 text-white rounded-md hover:bg-cyan-800 transition" id="removePurchaseBtn" data-loading-text="Loading...">
                        <i class="glyphicon glyphicon-ok-sign"></i> Save changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="custom/js/purchase.js"></script>
    <style>
        tr:hover {
            background-color: #f8fafc; /* slate-50 */
        }
    </style>
    <script>
        $(document).ready(function() {
            // Datepicker for purchase date
            $("#purchaseDate, #editPurchaseDate").datepicker({
                dateFormat: "yy-mm-dd"
            });

            // Calculate total on quantity or rate change
            function calculateTotal(rowPrefix) {
                var quantity = parseFloat($(`#${rowPrefix}Quantity`).val()) || 0;
                var rate = parseFloat($(`#${rowPrefix}Rate`).val()) || 0;
                var total = quantity * rate;
                $(`#${rowPrefix}Total`).val(total.toFixed(2));
                $(`#${rowPrefix}TotalValue`).val(total.toFixed(2));
            }

            $("#quantity, #rate").on("input", function() {
                calculateTotal("");
            });

            $("#editQuantity, #editRate").on("input", function() {
                calculateTotal("edit");
            });
        });
    </script>

<?php require_once 'includes/footer.php'; ?>