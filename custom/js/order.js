var manageOrderTable;

$(document).ready(function() {
    var divRequest = $(".div-request").text();

    // Top nav bar 
    $("#navOrder").addClass('active');

    if (divRequest == 'add') {
        // Add order    
        $('#topNavAddOrder').addClass('active'); 

        // Create order form function
        $("#createOrderForm").unbind('submit').bind('submit', function() {
            var form = $(this);

            $('.form-group').removeClass('has-error').removeClass('has-success');
            $('.text-danger').remove();
                
            var orderDateTime = $("#orderDateTime").val();
            var clientName = $("#clientName").val();
            var clientContact = $("#clientContact").val();
            var paid = $("#paid").val();
            var discount = $("#discount").val();

            // Form validation 
            if (!orderDateTime || !/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(orderDateTime)) {
                $("#orderDateTime").after('<p class="text-danger">The Order Date & Time field is required and must be in YYYY-MM-DDTHH:MM format</p>');
                $('#orderDateTime').closest('.form-group').addClass('has-error');
            } else {
                $('#orderDateTime').closest('.form-group').addClass('has-success');
            }

            if (!clientName) {
                $("#clientName").after('<p class="text-danger">The Client Name field is required</p>');
                $('#clientName').closest('.form-group').addClass('has-error');
            } else {
                $('#clientName').closest('.form-group').addClass('has-success');
            }

            if (!clientContact) {
                $("#clientContact").after('<p class="text-danger">The Contact field is required</p>');
                $('#clientContact').closest('.form-group').addClass('has-error');
            } else {
                $('#clientContact').closest('.form-group').addClass('has-success');
            }

            if (!paid) {
                $("#paid").after('<p class="text-danger">The Paid field is required</p>');
                $('#paid').closest('.form-group').addClass('has-error');
            } else if (isNaN(paid) || paid < 0) {
                $("#paid").after('<p class="text-danger">The Paid field must be a valid number</p>');
                $('#paid').closest('.form-group').addClass('has-error');
            } else {
                $('#paid').closest('.form-group').addClass('has-success');
            }

            if (!discount) {
                $("#discount").after('<p class="text-danger">The Discount field is required</p>');
                $('#discount').closest('.form-group').addClass('has-error');
            } else if (isNaN(discount) || discount < 0) {
                $("#discount").after('<p class="text-danger">The Discount field must be a valid number</p>');
                $('#discount').closest('.form-group').addClass('has-error');
            } else {
                $('#discount').closest('.form-group').addClass('has-success');
            }

            // Array validation
            var productName = document.getElementsByName('productName[]');               
            var validateProduct = true;
            for (var x = 0; x < productName.length; x++) {                   
                var productNameId = productName[x].id;       
                if (!productName[x].value) {                            
                    $("#" + productNameId).after('<p class="text-danger">Product Name Field is required</p>');
                    $("#" + productNameId).closest('.form-group').addClass('has-error');                            
                    validateProduct = false;
                } else {        
                    $("#" + productNameId).closest('.form-group').addClass('has-success');                            
                }          
            }

            var quantity = document.getElementsByName('quantity[]');             
            var validateQuantity = true;
            for (var x = 0; x < quantity.length; x++) {       
                var quantityId = quantity[x].id;
                if (!quantity[x].value || quantity[x].value < 1) {      
                    $("#" + quantityId).after('<p class="text-danger">Quantity Field is required and must be at least 1</p>');
                    $("#" + quantityId).closest('.form-group').addClass('has-error');                            
                    validateQuantity = false;
                } else {        
                    $("#" + quantityId).closest('.form-group').addClass('has-success');                            
                } 
            }

            if (orderDateTime && clientName && clientContact && paid && !isNaN(paid) && paid >= 0 && 
                discount && !isNaN(discount) && discount >= 0 && validateProduct && validateQuantity) {
                $("#createOrderBtn").button('loading');
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),                  
                    dataType: 'json',
                    success: function(response) {
                        $("#createOrderBtn").button('reset');
                        
                        $(".text-danger").remove();
                        $('.form-group').removeClass('has-error').removeClass('has-success');

                        if (response.success == true) {
                            $(".success-messages").html('<div class="alert alert-success">' +
                                '<button type="button" class="close" data-dismiss="alert">×</button>' +
                                '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> ' + response.messages +
                                ' <br /> <br /> <a type="button" onclick="printOrder(' + response.order_id + ')" class="btn btn-primary"> <i class="glyphicon glyphicon-print"></i> Print </a>' +
                                '<a href="orders.php?o=add" class="btn btn-default" style="margin-left:10px;"> <i class="glyphicon glyphicon-plus-sign"></i> Add New Order </a>' +
                                '</div>');
                            
                            $("html, body, div.panel, div.pane-body").animate({scrollTop: '0px'}, 100);
                            $(".submitButtonFooter").addClass('div-hide');
                            $(".removeProductRowBtn").addClass('div-hide');
                        } else {
                            $(".success-messages").html('<div class="alert alert-danger">' +
                                '<button type="button" class="close" data-dismiss="alert">×</button>' +
                                '<strong><i class="glyphicon glyphicon-remove-sign"></i></strong> ' + response.messages +
                                '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $("#createOrderBtn").button('reset');
                        var errorMessage = xhr.responseJSON && xhr.responseJSON.messages ? 
                            xhr.responseJSON.messages : 'An error occurred while submitting the form.';
                        $(".success-messages").html('<div class="alert alert-danger">' +
                            '<button type="button" class="close" data-dismiss="alert">×</button>' +
                            '<strong><i class="glyphicon glyphicon-remove-sign"></i></strong> ' + errorMessage +
                            '</div>');
                        console.log('AJAX Error:', xhr.responseText);
                    }
                });
            }
            
            return false;
        });
    
    } else if (divRequest == 'manord') {
        // Manage order
        $('#topNavManageOrder').addClass('active');

        manageOrderTable = $("#manageOrderTable").DataTable({
            'ajax': 'php_action/fetchOrder.php',
            'order': []
        });     
                
    } else if (divRequest == 'editOrd') {
        // Edit order form function
        $("#editOrderForm").unbind('submit').bind('submit', function() {
            var form = $(this);

            $('.form-group').removeClass('has-error').removeClass('has-success');
            $('.text-danger').remove();
                
            var orderDateTime = $("#orderDateTime").val();
            var clientName = $("#clientName").val();
            var clientContact = $("#clientContact").val();
            var paid = $("#paid").val();
            var discount = $("#discount").val();

            // Form validation 
            if (!orderDateTime || !/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(orderDateTime)) {
                $("#orderDateTime").after('<p class="text-danger">The Order Date & Time field is required and must be in YYYY-MM-DDTHH:MM format</p>');
                $('#orderDateTime').closest('.form-group').addClass('has-error');
            } else {
                $('#orderDateTime').closest('.form-group').addClass('has-success');
            }

            if (!clientName) {
                $("#clientName").after('<p class="text-danger">The Client Name field is required</p>');
                $('#clientName').closest('.form-group').addClass('has-error');
            } else {
                $('#clientName').closest('.form-group').addClass('has-success');
            }

            if (!clientContact) {
                $("#clientContact").after('<p class="text-danger">The Contact field is required</p>');
                $('#clientContact').closest('.form-group').addClass('has-error');
            } else {
                $('#clientContact').closest('.form-group').addClass('has-success');
            }

            if (!paid) {
                $("#paid").after('<p class="text-danger">The Paid field is required</p>');
                $('#paid').closest('.form-group').addClass('has-error');
            } else if (isNaN(paid) || paid < 0) {
                $("#paid").after('<p class="text-danger">The Paid field must be a valid number</p>');
                $('#paid').closest('.form-group').addClass('has-error');
            } else {
                $('#paid').closest('.form-group').addClass('has-success');
            }

            if (!discount) {
                $("#discount").after('<p class="text-danger">The Discount field is required</p>');
                $('#discount').closest('.form-group').addClass('has-error');
            } else if (isNaN(discount) || discount < 0) {
                $("#discount").after('<p class="text-danger">The Discount field must be a valid number</p>');
                $('#discount').closest('.form-group').addClass('has-error');
            } else {
                $('#discount').closest('.form-group').addClass('has-success');
            }

            // Array validation
            var productName = document.getElementsByName('productName[]');               
            var validateProduct = true;
            for (var x = 0; x < productName.length; x++) {                   
                var productNameId = productName[x].id;       
                if (!productName[x].value) {                            
                    $("#" + productNameId).after('<p class="text-danger">Product Name Field is required</p>');
                    $("#" + productNameId).closest('.form-group').addClass('has-error');                            
                    validateProduct = false;
                } else {        
                    $("#" + productNameId).closest('.form-group').addClass('has-success');                            
                }          
            }

            var quantity = document.getElementsByName('quantity[]');             
            var validateQuantity = true;
            for (var x = 0; x < quantity.length; x++) {       
                var quantityId = quantity[x].id;
                if (!quantity[x].value || quantity[x].value < 1) {      
                    $("#" + quantityId).after('<p class="text-danger">Quantity Field is required and must be at least 1</p>');
                    $("#" + quantityId).closest('.form-group').addClass('has-error');                            
                    validateQuantity = false;
                } else {        
                    $("#" + quantityId).closest('.form-group').addClass('has-success');                            
                } 
            }

            if (orderDateTime && clientName && clientContact && paid && !isNaN(paid) && paid >= 0 && 
                discount && !isNaN(discount) && discount >= 0 && validateProduct && validateQuantity) {
                $("#editOrderBtn").button('loading');
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),                  
                    dataType: 'json',
                    success: function(response) {
                        $("#editOrderBtn").button('reset');
                        
                        $(".text-danger").remove();
                        $('.form-group').removeClass('has-error').removeClass('has-success');

                        if (response.success == true) {
                            $(".success-messages").html('<div class="alert alert-success">' +
                                '<button type="button" class="close" data-dismiss="alert">×</button>' +
                                '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> ' + response.messages +
                                '</div>');
                            
                            $("html, body, div.panel, div.pane-body").animate({scrollTop: '0px'}, 100);
                            $(".editButtonFooter").addClass('div-hide');
                            $(".removeProductRowBtn").addClass('div-hide');
                        } else {
                            $(".success-messages").html('<div class="alert alert-danger">' +
                                '<button type="button" class="close" data-dismiss="alert">×</button>' +
                                '<strong><i class="glyphicon glyphicon-remove-sign"></i></strong> ' + response.messages +
                                '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $("#editOrderBtn").button('reset');
                        var errorMessage = xhr.responseJSON && xhr.responseJSON.messages ? 
                            xhr.responseJSON.messages : 'An error occurred while submitting the form.';
                        $(".success-messages").html('<div class="alert alert-danger">' +
                            '<button type="button" class="close" data-dismiss="alert">×</button>' +
                            '<strong><i class="glyphicon glyphicon-remove-sign"></i></strong> ' + errorMessage +
                            '</div>');
                        console.log('AJAX Error:', xhr.responseText);
                    }
                });
            }
            
            return false;
        });
    }   

});

// Print order function
function printOrder(orderId = null) {
    if (orderId) {      
        $.ajax({
            url: 'php_action/printOrder.php',
            type: 'post',
            data: {orderId: orderId},
            dataType: 'text',
            success: function(response) {
                if (response.trim() === '' || response.includes('Error:')) {
                    alert('Failed to load order details: ' + response);
                    console.log('PrintOrder Response:', response);
                    return;
                }

                var mywindow = window.open('', 'Stock Management System', 'height=400,width=600');
                mywindow.document.write('<html><head><title>Order Invoice</title>');
                mywindow.document.write('</head><body>');
                mywindow.document.write(response);
                mywindow.document.write('</body></html>');
                mywindow.document.close();
                mywindow.focus();

                mywindow.onload = function() {
                    mywindow.print();
                    mywindow.onafterprint = function() {
                        mywindow.close();
                    };
                };
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', xhr.responseText);
                alert('Failed to fetch order details. Check the console for details.');
            }
        });
    } else {
        alert('Error: No order ID provided.');
    }
}

function addRow() {
    $("#addRowBtn").button("loading");

    var tableLength = $("#productTable tbody tr").length;
    var tableRow;
    var arrayNumber;
    var count;

    if (tableLength > 0) {      
        tableRow = $("#productTable tbody tr:last").attr('id');
        arrayNumber = $("#productTable tbody tr:last").attr('class');
        count = tableRow.substring(3);  
        count = Number(count) + 1;
        arrayNumber = Number(arrayNumber) + 1;                   
    } else {
        count = 1;
        arrayNumber = 0;
    }

    $.ajax({
        url: 'php_action/fetchProductData.php',
        type: 'post',
        dataType: 'json',
        success: function(response) {
            $("#addRowBtn").button("reset");            

            var tr = '<tr id="row' + count + '" class="' + arrayNumber + '">' +                                 
                '<td>' +
                    '<div class="form-group">' +
                    '<select class="form-control" name="productName[]" id="productName' + count + '" onchange="getProductData(' + count + ')" >' +
                        '<option value="">~~SELECT~~</option>';
                        $.each(response, function(index, value) {
                            tr += '<option value="' + value[0] + '">' + value[1] + '</option>';                         
                        });
                    tr += '</select>' +
                    '</div>' +
                '</td>' +
                '<td style="padding-left:20px;">' +
                    '<input type="text" name="rate[]" id="rate' + count + '" autocomplete="off" disabled="true" class="form-control" />' +
                    '<input type="hidden" name="rateValue[]" id="rateValue' + count + '" autocomplete="off" class="form-control" />' +
                '</td>' +
                '<td style="padding-left:20px;">' +
                    '<div class="form-group">' +
                    '<input type="number" name="quantity[]" id="quantity' + count + '" onkeyup="getTotal(' + count + ')" autocomplete="off" class="form-control" min="1" />' +
                    '</div>' +
                '</td>' +
                '<td style="padding-left:20px;">' +
                    '<input type="text" name="total[]" id="total' + count + '" autocomplete="off" class="form-control" disabled="true" />' +
                    '<input type="hidden" name="totalValue[]" id="totalValue' + count + '" autocomplete="off" class="form-control" />' +
                '</td>' +
                '<td>' +
                    '<button class="btn btn-default removeProductRowBtn" type="button" onclick="removeProductRow(' + count + ')"><i class="glyphicon glyphicon-trash"></i></button>' +
                '</td>' +
            '</tr>';
            if (tableLength > 0) {                          
                $("#productTable tbody tr:last").after(tr);
            } else {                
                $("#productTable tbody").append(tr);
            }       
        }
    });
}

function removeProductRow(row = null) {
    if (row) {
        $("#row" + row).remove();
        calculateTotalAmount();
    } else {
        alert('Error: Unable to remove row. Please refresh the page.');
    }
}

function getProductData(row = null) {
    if (row) {
        var productId = $("#productName" + row).val();      
        if (!productId) {
            $("#rate" + row).val("");
            $("#quantity" + row).val("");                       
            $("#total" + row).val("");
            $("#totalValue" + row).val("");
        } else {
            $.ajax({
                url: 'php_action/fetchSelectedProduct.php',
                type: 'post',
                data: {productId: productId},
                dataType: 'json',
                success: function(response) {
                    $("#rate" + row).val(response.rate);
                    $("#rateValue" + row).val(response.rate);
                    $("#quantity" + row).val(1);
                    var total = Number(response.rate) * 1;
                    total = total.toFixed(2);
                    $("#total" + row).val(total);
                    $("#totalValue" + row).val(total);
                    
                    calculateTotalAmount();
                },
                error: function(xhr, status, error) {
                    alert('Error fetching product data. Please try again.');
                    console.log('AJAX Error:', xhr.responseText);
                }
            });
        }
    } else {
        alert('Error: No row specified. Please refresh the page.');
    }
}

function getTotal(row = null) {
    if (row) {
        var quantity = Number($("#quantity" + row).val());
        if (quantity < 1) {
            $("#quantity" + row).val(1);
            quantity = 1;
        }
        var total = Number($("#rate" + row).val()) * quantity;
        total = total.toFixed(2);
        $("#total" + row).val(total);
        $("#totalValue" + row).val(total);
        
        calculateTotalAmount();
    } else {
        alert('Error: No row specified. Please refresh the page.');
    }
}

function calculateTotalAmount() {
    var tableProductLength = $("#productTable tbody tr").length;
    var totalAmount = 0;
    for (var x = 0; x < tableProductLength; x++) {
        var tr = $("#productTable tbody tr")[x];
        var count = $(tr).attr('id');
        count = count.substring(3);
        totalAmount = Number(totalAmount) + Number($("#total" + count).val());
    }
    totalAmount = totalAmount.toFixed(2);
    $("#totalAmount").val(totalAmount);
    $("#totalAmountValue").val(totalAmount);
    
    var discount = $("#discount").val();
    if (discount && !isNaN(discount) && discount >= 0) {
        var grandTotal = Number(totalAmount) - Number(discount);
        grandTotal = grandTotal.toFixed(2);
        $("#grandTotal").val(grandTotal);
        $("#grandTotalValue").val(grandTotal);
    } else {
        $("#grandTotal").val(totalAmount);
        $("#grandTotalValue").val(totalAmount);
    }
    
    var paid = $("#paid").val();
    if (paid && !isNaN(paid) && paid >= 0) {
        var dueAmount = Number($("#grandTotal").val()) - Number(paid);
        dueAmount = dueAmount.toFixed(2);
        $("#due").val(dueAmount);
        $("#dueValue").val(dueAmount);
    } else {    
        $("#due").val($("#grandTotal").val());
        $("#dueValue").val($("#grandTotal").val());
    }
}

function discountFunc() {
    var discount = $("#discount").val();
    var totalAmount = Number($("#totalAmount").val());
    totalAmount = totalAmount.toFixed(2);
    if (discount && !isNaN(discount) && discount >= 0) {
        var grandTotal = Number(totalAmount) - Number(discount);
        grandTotal = grandTotal.toFixed(2);
        $("#grandTotal").val(grandTotal);
        $("#grandTotalValue").val(grandTotal);
    } else {
        $("#grandTotal").val(totalAmount);
        $("#grandTotalValue").val(totalAmount);
    }
    
    var paid = $("#paid").val();
    if (paid && !isNaN(paid) && paid >= 0) {
        var dueAmount = Number($("#grandTotal").val()) - Number(paid);
        dueAmount = dueAmount.toFixed(2);
        $("#due").val(dueAmount);
        $("#dueValue").val(dueAmount);
    } else {
        $("#due").val($("#grandTotal").val());
        $("#dueValue").val($("#grandTotal").val());
    }
}

function paidAmount() {
    var grandTotal = $("#grandTotal").val();
    var paid = $("#paid").val();
    if (grandTotal && paid && !isNaN(paid) && paid >= 0) {
        var dueAmount = Number(grandTotal) - Number(paid);
        dueAmount = dueAmount.toFixed(2);
        $("#due").val(dueAmount);
        $("#dueValue").val(dueAmount);
    } else {
        $("#due").val(grandTotal);
        $("#dueValue").val(grandTotal);
    }
}

function resetOrderForm() {
    $("#createOrderForm")[0].reset();
    $(".form-group").removeClass('has-error').removeClass('has-success');
    $(".text-danger").remove();
    $("#productTable tbody tr").remove();
    addRow();
    calculateTotalAmount();
}

function removeOrder(orderId = null) {
    if (orderId) {
        $("#removeOrderBtn").unbind('click').bind('click', function() {
            $("#removeOrderBtn").button('loading');
            $.ajax({
                url: 'php_action/removeOrder.php',
                type: 'post',
                data: {orderId: orderId},
                dataType: 'json',
                success: function(response) {
                    $("#removeOrderBtn").button('reset');
                    if (response.success == true) {
                        $("#removeOrderModal").modal('hide');
                        manageOrderTable.ajax.reload(null, false);
                        $("#success-messages").html('<div class="alert alert-success">' +
                            '<button type="button" class="close" data-dismiss="alert">×</button>' +
                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> ' + response.messages +
                            '</div>');
                    } else {
                        $(".removeOrderMessages").html('<div class="alert alert-danger">' +
                            '<button type="button" class="close" data-dismiss="alert">×</button>' +
                            '<strong><i class="glyphicon glyphicon-remove-sign"></i></strong> ' + response.messages +
                            '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $("#removeOrderBtn").button('reset');
                    $(".removeOrderMessages").html('<div class="alert alert-danger">' +
                        '<button type="button" class="close" data-dismiss="alert">×</button>' +
                        '<strong><i class="glyphicon glyphicon-remove-sign"></i></strong> Error occurred while removing the order.' +
                        '</div>');
                    console.log('AJAX Error:', xhr.responseText);
                }
            });
        });
    } else {
        alert('Error: No order ID provided.');
    }
}