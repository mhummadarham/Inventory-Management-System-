<?php 
require_once 'includes/header.php'; 

// Redirect to login if user is not authenticated
if (!isset($_SESSION['userId'])) {
    header('location: http://localhost/smartstock/index.php');
    exit();
}

// Use the username from session, fallback to 'Guest' if not set
$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
?>

<style>
/* General Reset and Typography */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    background-color: #f7fafc;
    color: #2d3748;
    line-height: 1.6;
}

/* Container and Layout */
.row {
    margin: 0 -15px;
}

.col-md-12, .col-md-4, .col-sm-6 {
    padding: 0 15px;
}

/* Breadcrumb */
.breadcrumb {
    background-color: #edf2f7;
    border-radius: 6px;
    padding: 12px 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.breadcrumb li.active {
    color: #1a73e8;
    font-weight: 500;
}

/* Panels */
.panel {
    border: none;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    background-color: #ffffff;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.panel:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.panel-heading {
    background-color: #1a73e8;
    color: #ffffff;
    padding: 15px 20px;
    border-radius: 8px 8px 0 0;
    font-size: 16px;
    font-weight: 500;
    display: flex;
    align-items: center;
}

.panel-heading .glyphicon {
    margin-right: 10px;
    font-size: 18px;
}

.panel-body {
    padding: 20px;
}

/* Welcome Panel */
.panel-default .panel-body p {
    font-size: 16px;
    color: #4a5568;
}

/* Stats Panel - Wells */
.well {
    background-color: #f7fafc;
    border: none;
    border-radius: 6px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: background-color 0.2s ease;
}

.well:hover {
    background-color: #edf2f7;
}

.well h4 {
    font-size: 18px;
    margin-bottom: 10px;
    color: #2d3748;
}

.well p {
    margin: 10px 0;
}

.well .text-success {
    color: #28a745;
}

.well .text-primary {
    color: #1a73e8;
}

.well .text-danger {
    color: #dc3545;
}

.well .btn {
    margin-top: 10px;
}

/* Buttons */
.btn-default {
    background-color: #1a73e8;
    color: #ffffff;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.btn-default:hover {
    color: #ffffff;
}

.btn-default:active {
    transform: translateY(0);
}

/* Enhanced View Products and View Orders Buttons */
.btn-view-products, .btn-view-orders {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    width: 100%;
    background: linear-gradient(45deg, #4a5568, #718096); /* Medium grey gradient */
    color: #ffffff;

}


.btn-view-products .glyphicon, .btn-view-orders .glyphicon {
    margin-right: 8px;
    font-size: 16px;
}

/* Date Panel */
.panel-body p[style*="font-size: 18px"] {
    font-size: 18px;
    color: #2d3748;
    font-weight: 500;
}

/* Sales Graph Panel */
#periodFilter {
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    padding: 8px;
    font-size: 14px;
    background-color: #ffffff;
    transition: border-color 0.2s ease;
}

#periodFilter:focus {
    border-color: #1a73e8;
    outline: none;
    box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.2);
}

#salesChart {
    background-color: #ffffff;
    padding: 15px;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .col-md-4 {
        margin-bottom: 15px;
    }

    .panel-heading {
        font-size: 14px;
        padding: 12px 15px;
    }

    .panel-body {
        padding: 15px;
    }

    .well {
        padding: 15px;
    }

    #periodFilter {
        width: 100% !important;
        margin-top: 10px;
    }

    .text-right {
        text-align: left;
    }

    .btn-view-products, .btn-view-orders {
        padding: 10px 20px;
        font-size: 14px;
    }
}

@media (max-width: 576px) {
    .breadcrumb {
        padding: 10px 15px;
    }

    .well h4 {
        font-size: 16px;
    }

    .well p {
        font-size: 20px;
    }

    .btn-view-products, .btn-view-orders {
        padding: 8px 16px;
        font-size: 13px;
    }
}
</style>

<?php 
// Get filter period from GET parameter, default to 'daily'
$filter_period = isset($_GET['period']) ? $_GET['period'] : 'daily';

// Validate filter period
$valid_periods = ['daily', 'monthly', 'yearly'];
if (!in_array($filter_period, $valid_periods)) {
    $filter_period = 'daily';
}

// Product count
$sql = "SELECT * FROM product WHERE status = 1";
$query = $connect->query($sql);
$countProduct = $query ? $query->num_rows : 0;

// Active orders count
$orderSql = "SELECT * FROM orders WHERE order_status = 1";
$orderQuery = $connect->query($orderSql);
$countOrder = $orderQuery ? $orderQuery->num_rows : 0;

// Low stock count
$lowStockSql = "SELECT * FROM product WHERE quantity <= 3 AND status = 1";
$lowStockQuery = $connect->query($lowStockSql);
$countLowStock = $lowStockQuery ? $lowStockQuery->num_rows : 0;

// Sales price data for graph
$labels = [];
$salesPrices = [];

try {
    if ($filter_period === 'daily') {
        // Group by hour for today
        $salesSql = "SELECT HOUR(order_datetime) AS time_unit, SUM(paid) AS total_sales 
                     FROM orders 
                     WHERE DATE(order_datetime) = CURDATE() 
                     GROUP BY HOUR(order_datetime) 
                     ORDER BY HOUR(order_datetime)";
        $time_format = 'H:00';
        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf("%02d:00", $i);
            $salesPrices[] = 0;
        }
    } elseif ($filter_period === 'monthly') {
        // Group by day for current month
        $salesSql = "SELECT DAY(order_datetime) AS time_unit, SUM(paid) AS total_sales 
                     FROM orders 
                     WHERE YEAR(order_datetime) = YEAR(CURDATE()) AND MONTH(order_datetime) = MONTH(CURDATE()) 
                     GROUP BY DAY(order_datetime) 
                     ORDER BY DAY(order_datetime)";
        $time_format = 'd M';
        $days_in_month = date('t');
        for ($i = 1; $i <= $days_in_month; $i++) {
            $labels[] = date('d M', mktime(0, 0, 0, date('m'), $i, date('Y')));
            $salesPrices[] = 0;
        }
    } elseif ($filter_period === 'yearly') {
        // Group by month for current year
        $salesSql = "SELECT MONTH(order_datetime) AS time_unit, SUM(paid) AS total_sales 
                     FROM orders 
                     WHERE YEAR(order_datetime) = YEAR(CURDATE()) 
                     GROUP BY MONTH(order_datetime) 
                     ORDER BY MONTH(order_datetime)";
        $time_format = 'M';
        for ($i = 1; $i <= 12; $i++) {
            $labels[] = date('M', mktime(0, 0, 0, $i, 1, date('Y')));
            $salesPrices[] = 0;
        }
    }

    $salesQuery = $connect->query($salesSql);
    if ($salesQuery === false) {
        throw new Exception("Sales query failed: " . $connect->error);
    }

    while ($row = $salesQuery->fetch_assoc()) {
        $index = $filter_period === 'daily' ? $row['time_unit'] : $row['time_unit'] - 1;
        $salesPrices[$index] = (float)$row['total_sales'];
    }

    // Calculate filtered total sales for summary
    $filteredSalesSql = "SELECT SUM(paid) AS total_sales FROM orders";
    if ($filter_period === 'daily') {
        $filteredSalesSql .= " WHERE DATE(order_datetime) = CURDATE()";
    } elseif ($filter_period === 'monthly') {
        $filteredSalesSql .= " WHERE YEAR(order_datetime) = YEAR(CURDATE()) AND MONTH(order_datetime) = MONTH(CURDATE())";
    } elseif ($filter_period === 'yearly') {
        $filteredSalesSql .= " WHERE YEAR(order_datetime) = YEAR(CURDATE())";
    }

    $filteredSalesQuery = $connect->query($filteredSalesSql);
    if ($filteredSalesQuery === false) {
        throw new Exception("Filtered sales query failed: " . $connect->error);
    }

    $filteredTotalSales = $filteredSalesQuery->fetch_assoc()['total_sales'] ?? 0;

} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $filteredTotalSales = 0;
    $salesPrices = array_fill(0, count($labels), 0);
}

$connect->close();
?>


        <!-- Welcome Panel -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-home"></i> Welcome, <?php echo htmlspecialchars($user_name); ?>!
            </div>
            <div class="panel-body">
                <p>Here's an overview of your inventory and orders.</p>
            </div>
        </div>

        <!-- Stats Panel -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-stats"></i> Inventory & Orders Overview
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="well">
                            <h4>Total Products</h4>
                            <p class="text-success" style="font-size: 24px;"><?php echo $countProduct; ?></p>
                            <a href="product.php" class="btn btn-default btn-view-products"><i class="glyphicon glyphicon-list-alt"></i> View Products</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="well">
                            <h4>Total Orders</h4>
                            <p class="text-primary" style="font-size: 24px;"><?php echo $countOrder; ?></p>
                            <a href="orders.php?o=manord" class="btn btn-default btn-view-orders"><i class="glyphicon glyphicon-shopping-cart"></i> View Orders</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="well">
                            <h4>Low Stock</h4>
                            <p class="text-danger" style="font-size: 24px;"><?php echo $countLowStock; ?></p>
                            <a href="product.php" class="btn btn-default btn-view-products"><i class="glyphicon glyphicon-list-alt"></i> View Products</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Panel -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-calendar"></i> Today's Date
            </div>
            <div class="panel-body">
                <p style="font-size: 18px;"><?php echo date('l, d M Y'); ?></p>
            </div>
        </div>

        <!-- Sales Graph Panel -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-signal"></i> Sales Overview
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-6">
                        <p><strong>Total Sales:</strong> PKR <?php echo number_format($filteredTotalSales, 2); ?></p>
                    </div>
                    <div class="col-sm-6 text-right">
                        <select id="periodFilter" onchange="window.location.href='dashboard.php?period=' + this.value" class="form-control" style="width: 150px; display: inline-block;">
                            <option value="daily" <?php echo $filter_period === 'daily' ? 'selected' : ''; ?>>Daily</option>
                            <option value="monthly" <?php echo $filter_period === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                            <option value="yearly" <?php echo $filter_period === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                        </select>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <canvas id="salesChart" style="max-height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(function () {
    // top bar active
    $('#navDashboard').addClass('active');
});

// Chart.js configuration
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [
            {
                label: 'Sales (PKR)',
                data: <?php echo json_encode($salesPrices); ?>,
                backgroundColor: 'rgba(30, 58, 138, 0.2)', // blue-900 fill
                borderColor: 'rgba(30, 58, 138, 1)', // blue-900 line
                pointBackgroundColor: 'rgba(6, 182, 212, 1)', // cyan-500
                pointBorderColor: 'rgba(6, 182, 212, 1)', // cyan-500
                pointHoverBackgroundColor: 'rgba(8, 145, 178, 1)', // cyan-400
                pointHoverBorderColor: 'rgba(8, 145, 178, 1)', // cyan-400
                borderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                fill: true,
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Sales (PKR)',
                    color: '#333'
                },
                ticks: {
                    color: '#666',
                    callback: function(value) {
                        return 'PKR ' + value.toFixed(2);
                    }
                }
            },
            x: {
                title: {
                    display: true,
                    text: '<?php echo $filter_period === 'daily' ? 'Hour' : ($filter_period === 'monthly' ? 'Day' : 'Month'); ?>',
                    color: '#333'
                },
                ticks: {
                    color: '#666'
                }
            }
        },
        plugins: {
            error: {
                display: true,
                position: 'top',
                labels: {
                    color: '#333'
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `PKR ${context.parsed.y.toFixed(2)}`;
                    }
                }
            }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>