$(document).ready(function() {
    // Handle form submission
    $("#getOrderReportForm").on('submit', function(e) {
        e.preventDefault(); // Prevent page reload

        var startDateTime = $("#startDateTime").val();
        var endDateTime = $("#endDateTime").val();

        // Validation
        if (startDateTime === "" || endDateTime === "") {
            if (startDateTime === "") {
                $("#startDateTime").closest('.form-group').addClass('has-error');
                $("#startDateTime").after('<p class="text-danger">The Start Date & Time is required</p>');
            }
            if (endDateTime === "") {
                $("#endDateTime").closest('.form-group').addClass('has-error');
                $("#endDateTime").after('<p class="text-danger">The End Date & Time is required</p>');
            }
            return;
        }

        // Validate date range
        var startDateObj = new Date(startDateTime);
        var endDateObj = new Date(endDateTime);
        if (isNaN(startDateObj) || isNaN(endDateObj)) {
            $("#startDateTime").closest('.form-group').addClass('has-error');
            $("#startDateTime").after('<p class="text-danger">Invalid date and time format</p>');
            return;
        }
        if (startDateObj > endDateObj) {
            $("#startDateTime").closest('.form-group').addClass('has-error');
            $("#startDateTime").after('<p class="text-danger">Start Date & Time cannot be after End Date & Time</p>');
            return;
        }

        // Remove previous error messages
        $(".form-group").removeClass('has-error');
        $(".text-danger").remove();

        // AJAX request
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: $(this).serialize(),
            dataType: 'html',
            beforeSend: function() {
                $("#generateReportBtn").prop('disabled', true).html(' Generating...');
            },
            success: function(response) {
                if (response.trim() === '' || response.includes('Error:')) {
                    alert("No orders found or an error occurred: " + response);
                    return;
                }

                // Open new window with styled report
                var mywindow = window.open('', 'Stock Management System', 'height=600,width=800');
                mywindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Order Report</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            .print-btn, .close-btn { margin: 10px; padding: 10px 20px; cursor: pointer; }
                        </style>
                    </head>
                    <body>
                        ${response}
                        <button class="print-btn" onclick="window.print()">Print</button>
                        <button class="close-btn" onclick="window.close()">Close</button>
                    </body>
                    </html>
                `);
                mywindow.document.close();
                mywindow.focus();

                // Display inline
                $("#reportResult").html(response).show();
            },
            error: function(xhr, status, error) {
                console.log("AJAX Error:", xhr.responseText, status, error);
                alert("An error occurred: " + xhr.responseText);
            },
            complete: function() {
                $("#generateReportBtn").prop('disabled', false).html(' Generate Report');
            }
        });
    });
});