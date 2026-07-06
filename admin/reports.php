<?php
require_once '../config.php';
require_once '../includes/functions.php';

// التحقق من صلاحيات المدير
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$page_title = 'التقارير والإحصائيات';

// التاريخ الافتراضي
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$report_type = $_GET['report_type'] ?? 'daily';

// إحصائيات عامة
$total_revenue = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;
$total_bookings = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_row()[0];
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_row()[0];
$total_hotels = $conn->query("SELECT COUNT(*) as total FROM hotels")->fetch_row()[0];

// تقرير المبيعات حسب التاريخ
$sales_report_sql = "SELECT 
    DATE(booking_date) as date,
    COUNT(*) as bookings_count,
    SUM(total_price) as revenue,
    AVG(total_price) as avg_booking_value
    FROM bookings 
    WHERE booking_date BETWEEN ? AND ?
    GROUP BY DATE(booking_date)
    ORDER BY date DESC";

$stmt = $conn->prepare($sales_report_sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$sales_report = $stmt->get_result();

// الفنادق الأكثر حجزاً
$top_hotels_sql = "SELECT 
    h.name as hotel_name,
    COUNT(b.id) as booking_count,
    SUM(b.total_price) as total_revenue
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.id
    WHERE b.booking_date BETWEEN ? AND ?
    GROUP BY b.hotel_id
    ORDER BY booking_count DESC
    LIMIT 10";

$stmt2 = $conn->prepare($top_hotels_sql);
$stmt2->bind_param("ss", $start_date, $end_date);
$stmt2->execute();
$top_hotels = $stmt2->get_result();

// العملاء الأكثر نشاطاً
$top_customers_sql = "SELECT 
    u.full_name,
    u.email,
    COUNT(b.id) as booking_count,
    SUM(b.total_price) as total_spent
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    WHERE b.booking_date BETWEEN ? AND ?
    GROUP BY b.user_id
    ORDER BY total_spent DESC
    LIMIT 10";

$stmt3 = $conn->prepare($top_customers_sql);
$stmt3->bind_param("ss", $start_date, $end_date);
$stmt3->execute();
$top_customers = $stmt3->get_result();
?>

<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-md-3">
        <?php include 'admin-sidebar.php'; ?>
    </div>
    
    <div class="col-md-9">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> التقارير والإحصائيات</h5>
            </div>
            <div class="card-body">
                <!-- فلترة التقارير -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">من تاريخ</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo $start_date; ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">إلى تاريخ</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?php echo $end_date; ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="report_type" class="form-label">نوع التقرير</label>
                                <select class="form-select" id="report_type" name="report_type">
                                    <option value="daily" <?php echo $report_type == 'daily' ? 'selected' : ''; ?>>يومي</option>
                                    <option value="weekly" <?php echo $report_type == 'weekly' ? 'selected' : ''; ?>>أسبوعي</option>
                                    <option value="monthly" <?php echo $report_type == 'monthly' ? 'selected' : ''; ?>>شهري</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> تطبيق الفلتر
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- إحصائيات سريعة -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">إجمالي الإيرادات</h6>
                                        <h4 class="mb-0"><?php echo number_format($total_revenue, 2); ?> ريال</h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">إجمالي الحجوزات</h6>
                                        <h4 class="mb-0"><?php echo $total_bookings; ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-calendar-check fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">إجمالي المستخدمين</h6>
                                        <h4 class="mb-0"><?php echo $total_users; ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">إجمالي الفنادق</h6>
                                        <h4 class="mb-0"><?php echo $total_hotels; ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-hotel fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- تقرير المبيعات -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-chart-line"></i> تقرير المبيعات</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>عدد الحجوزات</th>
                                        <th>الإيرادات</th>
                                        <th>متوسط قيمة الحجز</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $sales_report->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['date']; ?></td>
                                            <td><?php echo $row['bookings_count']; ?></td>
                                            <td><?php echo number_format($row['revenue'] ?? 0, 2); ?> ريال</td>
                                            <td><?php echo number_format($row['avg_booking_value'] ?? 0, 2); ?> ريال</td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-primary">
                                        <td><strong>المجموع</strong></td>
                                        <td>
                                            <?php 
                                            $sales_report->data_seek(0);
                                            $total_count = 0;
                                            while($row = $sales_report->fetch_assoc()) {
                                                $total_count += $row['bookings_count'];
                                            }
                                            echo $total_count;
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $sales_report->data_seek(0);
                                            $total_rev = 0;
                                            while($row = $sales_report->fetch_assoc()) {
                                                $total_rev += $row['revenue'] ?? 0;
                                            }
                                            echo number_format($total_rev, 2) . ' ريال';
                                            ?>
                                        </td>
                                        <td>-</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- الفنادق الأكثر حجزاً -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-hotel"></i> الفنادق الأكثر حجزاً</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>الفندق</th>
                                                <th>عدد الحجوزات</th>
                                                <th>الإيرادات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $top_hotels->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['hotel_name']); ?></td>
                                                    <td><?php echo $row['booking_count']; ?></td>
                                                    <td><?php echo number_format($row['total_revenue'] ?? 0, 2); ?> ريال</td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- العملاء الأكثر نشاطاً -->
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-users"></i> العملاء الأكثر نشاطاً</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>العميل</th>
                                                <th>البريد</th>
                                                <th>عدد الحجوزات</th>
                                                <th>إجمالي المصروفات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $top_customers->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                    <td><?php echo $row['booking_count']; ?></td>
                                                    <td><?php echo number_format($row['total_spent'] ?? 0, 2); ?> ريال</td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- أزرار التصدير -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <div class="btn-group" role="group">
                            <a href="export-reports.php?type=sales&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                               class="btn btn-outline-success">
                                <i class="fas fa-file-excel"></i> تصدير تقرير المبيعات
                            </a>
                            <a href="export-reports.php?type=hotels&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-file-excel"></i> تصدير تقرير الفنادق
                            </a>
                            <a href="export-reports.php?type=customers&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                               class="btn btn-outline-info">
                                <i class="fas fa-file-excel"></i> تصدير تقرير العملاء
                            </a>
                            <button onclick="window.print()" class="btn btn-outline-secondary">
                                <i class="fas fa-print"></i> طباعة التقرير
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// مخطط الإيرادات
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php 
            $labels = [];
            $sales_report->data_seek(0);
            while($row = $sales_report->fetch_assoc()) {
                $labels[] = $row['date'];
            }
            echo json_encode($labels);
        ?>,
        datasets: [{
            label: 'الإيرادات (ريال)',
            data: <?php 
                $data = [];
                $sales_report->data_seek(0);
                while($row = $sales_report->fetch_assoc()) {
                    $data[] = $row['revenue'] ?? 0;
                }
                echo json_encode($data);
            ?>,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString() + ' ريال';
                    }
                }
            }
        }
    }
});

// مخطط الفنادق
const hotelsCtx = document.getElementById('hotelsChart').getContext('2d');
const hotelsChart = new Chart(hotelsCtx, {
    type: 'bar',
    data: {
        labels: <?php 
            $hotelLabels = [];
            $hotelData = [];
            $top_hotels->data_seek(0);
            while($row = $top_hotels->fetch_assoc()) {
                $hotelLabels[] = substr($row['hotel_name'], 0, 15) . '...';
                $hotelData[] = $row['booking_count'];
            }
            echo json_encode($hotelLabels);
        ?>,
        datasets: [{
            label: 'عدد الحجوزات',
            data: <?php echo json_encode($hotelData); ?>,
            backgroundColor: '#007bff',
            borderColor: '#0056b3',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<style>
@media print {
    .navbar, .btn-group, form {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>