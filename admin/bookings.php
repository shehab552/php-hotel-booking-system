<?php
require_once '../config.php';
require_once '../includes/functions.php';

// التحقق من صلاحيات المدير
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$page_title = 'إدارة الحجوزات';

// جلب جميع الحجوزات مع بيانات المستخدم والفندق والغرفة
$sql = "
    SELECT 
        b.id,
        u.full_name,
        u.email,
        h.name AS hotel_name,
        r.room_type,
        b.check_in,
        b.check_out,
        b.status
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    JOIN hotels h ON r.hotel_id = h.id
    ORDER BY b.check_in DESC
";

$result = $conn->query($sql);
if (!$result) {
    die("خطأ في SQL: " . $conn->error);
}
?>

<?php include '../includes/header.php'; ?>

<div class="row">
    <!-- القائمة الجانبية -->
    <div class="col-md-3">
        <?php include 'admin-sidebar.php'; ?>
    </div>

    <div class="col-md-9">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> إدارة الحجوزات</h5>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>المستخدم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الفندق</th>
                                <th>نوع الغرفة</th>
                                <th>تاريخ الوصول</th>
                                <th>تاريخ المغادرة</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows == 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                                        <p class="text-muted">لا توجد حجوزات</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while ($b = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $b['id']; ?></td>
                                        <td><?= htmlspecialchars($b['full_name']); ?></td>
                                        <td><?= htmlspecialchars($b['email']); ?></td>
                                        <td><?= htmlspecialchars($b['hotel_name']); ?></td>
                                        <td><?= htmlspecialchars($b['room_type']); ?></td>
                                        <td><?= htmlspecialchars($b['check_in']); ?></td>
                                        <td><?= htmlspecialchars($b['check_out']); ?></td>
                                        <td>
                                            <?php
                                                if ($b['status'] == 'pending') echo '<span class="badge bg-warning">قيد الانتظار</span>';
                                                elseif ($b['status'] == 'confirmed') echo '<span class="badge bg-success">مؤكد</span>';
                                                elseif ($b['status'] == 'cancelled') echo '<span class="badge bg-danger">ملغى</span>';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
