<?php
// التحقق من صلاحيات المدير
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}
?>

<!-- القائمة الجانبية -->
<div class="card shadow-sm mb-4">
    <div class="card-body text-center">
        <h5 class="mb-3">مرحباً، <?php echo $_SESSION['full_name']; ?></h5>
        <p class="text-muted">مدير النظام</p>
        <div class="d-grid gap-2">
            <a href="../auth/register-admin.php" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-user-plus"></i> إضافة مدير
            </a>
            <a href="/11/auth/register.php" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-user-plus"></i> إضافة عميل
            </a>
        </div>
    </div>
</div>

<div class="list-group shadow-sm">
    <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt"></i> لوحة التحكم
    </a>
    <a href="users.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
        <i class="fas fa-users"></i> المستخدمين
    </a>
    <a href="../pages/hotels.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'hotels.php' ? 'active' : ''; ?>">
        <i class="fas fa-hotel"></i> الفنادق
    </a>
    <a href="bookings.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>">
    <i class="fas fa-calendar-check"></i> الحجوزات
    </a>

    <a href="reports.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
        <i class="fas fa-chart-bar"></i> التقارير
    </a>
    <a href="settings.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
        <i class="fas fa-cog"></i> الإعدادات
    </a>
    <a href="../auth/register-admin.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'register-admin.php' ? 'active' : ''; ?>">
        <i class="fas fa-user-shield"></i> إضافة مدراء
    </a>
</div>

<!-- إحصائيات سريعة -->
<div class="card shadow-sm mt-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fas fa-chart-pie"></i> إحصائيات سريعة</h6>
    </div>
    <div class="card-body">
        <?php
        $today = date('Y-m-d');
        
        $stats = [
            'اليوم' => [
                'bookings' => $conn->query("SELECT COUNT(*) FROM bookings WHERE DATE(booking_date) = '$today'")->fetch_row()[0],
                'revenue' => $conn->query("SELECT SUM(total_price) FROM bookings WHERE DATE(booking_date) = '$today' AND status = 'completed'")->fetch_row()[0] ?? 0
            ],
            'الشهر' => [
                'bookings' => $conn->query("SELECT COUNT(*) FROM bookings WHERE MONTH(booking_date) = MONTH(NOW())")->fetch_row()[0],
                'revenue' => $conn->query("SELECT SUM(total_price) FROM bookings WHERE MONTH(booking_date) = MONTH(NOW()) AND status = 'completed'")->fetch_row()[0] ?? 0
            ]
        ];
        ?>
        
        <div class="mb-3">
            <small class="text-muted">حجوزات اليوم:</small>
            <div class="d-flex justify-content-between">
                <span><?php echo $stats['اليوم']['bookings']; ?> حجز</span>
                <span class="text-primary"><?php echo number_format($stats['اليوم']['revenue'], 2); ?> ريال</span>
            </div>
        </div>
        
        <div class="mb-3">
            <small class="text-muted">حجوزات هذا الشهر:</small>
            <div class="d-flex justify-content-between">
                <span><?php echo $stats['الشهر']['bookings']; ?> حجز</span>
                <span class="text-success"><?php echo number_format($stats['الشهر']['revenue'], 2); ?> ريال</span>
            </div>
        </div>
        
        <div class="text-center">
            <small class="text-muted">آخر تحديث: <?php echo date('H:i'); ?></small>
        </div>
    </div>
</div>