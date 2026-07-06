<?php
require_once '../config.php';
require_once '../includes/functions.php';

// التحقق من صلاحيات المدير
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$page_title = 'لوحة التحكم - المدير';

// إحصائيات
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM hotels) as total_hotels,
    (SELECT COUNT(*) FROM bookings) as total_bookings,
    (SELECT SUM(total_price) FROM bookings WHERE status = 'completed') as total_revenue,
    (SELECT COUNT(*) FROM bookings WHERE status = 'pending') as pending_bookings,
    (SELECT COUNT(*) FROM bookings WHERE DATE(booking_date) = CURDATE()) as today_bookings";

$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// آخر المستخدمين المسجلين
$recent_users_sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users = $conn->query($recent_users_sql);

// آخر الحجوزات
$recent_bookings_sql = "SELECT b.*, u.full_name, h.name as hotel_name 
                        FROM bookings b 
                        JOIN users u ON b.user_id = u.id 
                        JOIN hotels h ON b.hotel_id = h.id 
                        ORDER BY b.booking_date DESC LIMIT 5";
$recent_bookings = $conn->query($recent_bookings_sql);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="row">
    <!-- القائمة الجانبية -->
    <div class="col-md-3">
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <h5 class="mb-3">مرحباً، <?php echo $_SESSION['full_name']; ?></h5>
                <p class="text-muted">مدير النظام</p>
                <div class="d-grid gap-2">
                    <a href="users.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-users"></i> إدارة المستخدمين
                    </a>
                    <a href="../pages/hotels.php" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-hotel"></i> إدارة الفنادق
                    </a>
                </div>
            </div>
        </div>
        
        <div class="list-group shadow-sm">
            <a href="dashboard.php" class="list-group-item list-group-item-action active">
                <i class="fas fa-tachometer-alt"></i> لوحة التحكم
            </a>
            <a href="users.php" class="list-group-item list-group-item-action">
                <i class="fas fa-users"></i> المستخدمين
            </a>
            <a href="../pages/hotels.php" class="list-group-item list-group-item-action">
                <i class="fas fa-hotel"></i> الفنادق
            </a>
            <a href="../user/bookings.php" class="list-group-item list-group-item-action">
                <i class="fas fa-calendar-check"></i> الحجوزات
            </a>
            <a href="reports.php" class="list-group-item list-group-item-action">
                <i class="fas fa-chart-bar"></i> التقارير
            </a>
            <a href="settings.php" class="list-group-item list-group-item-action">
                <i class="fas fa-cog"></i> الإعدادات
            </a>
        </div>
    </div>
    
    <!-- المحتوى الرئيسي -->
    <div class="col-md-9">
        <!-- إحصائيات سريعة -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">المستخدمين</h5>
                                <h2 class="mb-0"><?php echo $stats['total_users']; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">الفنادق</h5>
                                <h2 class="mb-0"><?php echo $stats['total_hotels']; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-hotel fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-warning text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">الحجوزات</h5>
                                <h2 class="mb-0"><?php echo $stats['total_bookings']; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">الإيرادات</h5>
                                <h4 class="mb-0"><?php echo number_format($stats['total_revenue'] ?? 0, 2); ?> ريال</h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- آخر الحجوزات -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history"></i> آخر الحجوزات</h5>
              <a href="<?php echo BASE_URL; ?>admin/bookings.php" class="btn btn-sm btn-outline-primary">عرض الكل</a>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>رقم الحجز</th>
                                <th>العميل</th>
                                <th>الفندق</th>
                                <th>التاريخ</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo $booking['full_name']; ?></td>
                                    <td><?php echo $booking['hotel_name']; ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($booking['check_in'])); ?></td>
                                    <td><?php echo number_format($booking['total_price'], 2); ?> ريال</td>
                                    <td>
                                        <?php
                                        $status_class = [
                                            'pending' => 'warning',
                                            'confirmed' => 'success',
                                            'cancelled' => 'danger',
                                            'completed' => 'info'
                                        ];
                                        ?>
                                        <span class="badge bg-<?php echo $status_class[$booking['status']]; ?>">
                                            <?php echo $booking['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- آخر المستخدمين -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user-plus"></i> آخر المستخدمين المسجلين</h5>
                <a href="users.php" class="btn btn-sm btn-outline-primary">عرض الكل</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>اسم المستخدم</th>
                                <th>الاسم الكامل</th>
                                <th>البريد الإلكتروني</th>
                                <th>النوع</th>
                                <th>تاريخ التسجيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $recent_users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['full_name']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td>
                                        <?php
                                        $user_types = [
                                            'admin' => '<span class="badge bg-danger">مدير</span>',
                                            'manager' => '<span class="badge bg-warning">مدير فندق</span>',
                                            'customer' => '<span class="badge bg-info">عميل</span>'
                                        ];
                                        echo $user_types[$user['user_type']] ?? $user['user_type'];
                                        ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- إحصائيات سريعة أخرى -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-chart-pie"></i> توزيع الحجوزات</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="bookingsChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-chart-line"></i> الإيرادات الشهرية</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// مخطط توزيع الحجوزات
const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
const bookingsChart = new Chart(bookingsCtx, {
    type: 'doughnut',
    data: {
        labels: ['قيد الانتظار', 'مؤكدة', 'ملغية', 'مكتملة'],
        datasets: [{
            data: [
                <?php echo $stats['pending_bookings']; ?>,
                <?php echo $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetch_row()[0]; ?>,
                <?php echo $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'cancelled'")->fetch_row()[0]; ?>,
                <?php echo $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'completed'")->fetch_row()[0]; ?>
            ],
            backgroundColor: [
                '#ffc107',
                '#28a745',
                '#dc3545',
                '#17a2b8'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// مخطط الإيرادات الشهرية
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
        datasets: [{
            label: 'الإيرادات (ريال)',
            data: [15000, 18000, 22000, 19000, 25000, 30000],
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
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>