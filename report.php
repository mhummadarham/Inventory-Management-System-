<?php require_once 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-check"></i> Order Report
            </div>
            <!-- /panel-heading -->
            <div class="panel-body">
                
                <form class="form-horizontal" action="php_action/getOrderReport.php" method="post" id="getOrderReportForm">
                    <div class="form-group">
                        <label for="startDateTime" class="col-sm-2 control-label">Start Date & Time</label>
                        <div class="col-sm-10">
                            <input type="datetime-local" class="form-control" id="startDateTime" name="startDateTime" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="endDateTime" class="col-sm-2 control-label">End Date & Time</label>
                        <div class="col-sm-10">
                            <input type="datetime-local" class="form-control" id="endDateTime" name="endDateTime" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-success" id="generateReportBtn"> <i class="glyphicon glyphicon-ok-sign"></i> Generate Report</button>
                        </div>
                    </div>
                </form>
                <div id="reportResult" class="well" style="display: none;"></div>
            </div>
            <!-- /panel-body -->
        </div>
    </div>
    <!-- /col-md-12 -->
</div>
<!-- /row -->

<script src="custom/js/report.js"></script>

<script>
// Set the default values of startDateTime and endDateTime to the user's local system date and time
document.addEventListener('DOMContentLoaded', function() {
    const startDateTimeInput = document.getElementById('startDateTime');
    const endDateTimeInput = document.getElementById('endDateTime');
    
    if (startDateTimeInput && endDateTimeInput) {
        const now = new Date();
        // Format date and time as YYYY-MM-DDTHH:MM
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const localDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        
        // Set default to current time for endDateTime
        endDateTimeInput.value = localDateTime;
        
        // Set default to start of the day for startDateTime (e.g., 00:00)
        startDateTimeInput.value = `${year}-${month}-${day}T00:00`;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>