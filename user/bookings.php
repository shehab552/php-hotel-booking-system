<?php
require_once '../config.php';
require_once '../includes/functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$page_title = 'حجوزاتي';
$user_id = $_SESSION['user_id'];

// جلب جميع حجوزات المستخدم
$bookings = getUserBookings($user_id);

// معالجة إلغاء الحجز
if (isset($_GET['cancel_id'])) {
    $booking_id = intval($_GET['cancel_id']);
    
    // التحقق من أن الحجز يخص المستخدم
    $sql = "SELECT * FROM bookings WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $booking = $result->fetch_assoc();
        
        // يمكن الإلغاء فقط إذا كان الحجز قيد الانتظار أو مؤكد
        if (in_array($booking['status'], ['pending', 'confirmed'])) {
            // تحديث حالة الحجز
            $update_sql = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $booking_id);
            
            if ($update_stmt->execute()) {
                // إعادة الغرفة إلى الغرف المتاحة
                $room_sql = "UPDATE rooms SET available_rooms = available_rooms + 1 WHERE id = ?";
                $room_stmt = $conn->prepare($room_sql);
                $room_stmt->bind_param("i", $booking['room_id']);
                $room_stmt->execute();
                
                $success_messages[] = 'تم إلغاء الحجز بنجاح';
                // تحديث القائمة
                $bookings = getUserBookings($user_id);
            } else {
                $error_messages[] = 'حدث خطأ أثناء الإلغاء';
            }
        } else {
            $error_messages[] = 'لا يمكن إلغاء الحجز في هذه الحالة';
        }
    } else {
        $error_messages[] = 'الحجز غير موجود أو ليس لديك صلاحية للإلغاء';
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-md-3">
        <!-- القائمة الجانبية -->
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <h5 class="mb-3">إدارة الحجوزات</h5>
                <a href="../pages/hotels.php" class="btn btn-primary btn-sm mb-2">
                    <i class="fas fa-plus"></i> حجز جديد
                </a>
            </div>
        </div>
        
        <div class="list-group shadow-sm">
            <a href="dashboard.php" class="list-group-item list-group-item-action">
                <i class="fas fa-tachometer-alt"></i> لوحة التحكم
            </a>
            <a href="bookings.php" class="list-group-item list-group-item-action active">
                <i class="fas fa-calendar-check"></i> حجوزاتي
            </a>
            <a href="profile.php" class="list-group-item list-group-item-action">
                <i class="fas fa-user"></i> الملف الشخصي
            </a>
        </div>
        
        <!-- تصفية الحجوزات -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-filter"></i> تصفية الحجوزات</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="?filter=all" class="list-group-item list-group-item-action">
                        <i class="fas fa-list"></i> جميع الحجوزات
                    </a>
                    <a href="?filter=pending" class="list-group-item list-group-item-action">
                        <i class="fas fa-clock text-warning"></i> قيد الانتظار
                    </a>
                    <a href="?filter=confirmed" class="list-group-item list-group-item-action">
                        <i class="fas fa-check-circle text-success"></i> مؤكدة
                    </a>
                    <a href="?filter=completed" class="list-group-item list-group-item-action">
                        <i class="fas fa-clipboard-check text-info"></i> مكتملة
                    </a>
                    <a href="?filter=cancelled" class="list-group-item list-group-item-action">
                        <i class="fas fa-times-circle text-danger"></i> ملغية
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> حجوزاتي</h5>
                <div>
                    <span class="badge bg-primary"><?php echo count($bookings); ?> حجز</span>
                </div>
            </div>
            <div class="card-body">
                <?php displayMessages(); ?>
                
                <?php if (empty($bookings)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">لا توجد حجوزات</h5>
                        <p class="text-muted mb-4">يمكنك البدء بحجز فندقك الأول الآن</p>
                        <a href="../pages/hotels.php" class="btn btn-primary">
                            <i class="fas fa-hotel"></i> ابحث عن فندق
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الحجز</th>
                                    <th>الفندق</th>
                                    <th>نوع الغرفة</th>
                                    <th>التواريخ</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></strong><br>
                                            <small class="text-muted"><?php echo date('Y-m-d', strtotime($booking['booking_date'])); ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo $booking['hotel_name']; ?></strong><br>
                                            <small class="text-muted"><?php echo $booking['guests']; ?> ضيف</small>
                                        </td>
                                        <td><?php echo $booking['room_type']; ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo date('Y-m-d', strtotime($booking['check_in'])); ?>
                                            </span><br>
                                            <small>إلى</small><br>
                                            <span class="badge bg-light text-dark">
                                                <?php echo date('Y-m-d', strtotime($booking['check_out'])); ?>
                                            </span><br>
                                            <small class="text-muted"><?php echo $booking['total_nights']; ?> ليلة</small>
                                        </td>
                                        <td>
                                            <strong class="text-primary"><?php echo number_format($booking['total_price'], 2); ?> ريال</strong><br>
                                            <small class="text-muted"><?php echo number_format($booking['price_per_night'], 2); ?> ريال/ليلة</small>
                                        </td>
                                        <td>
                                            <?php
                                            $status_class = [
                                                'pending' => 'warning',
                                                'confirmed' => 'success',
                                                'cancelled' => 'danger',
                                                'completed' => 'info'
                                            ];
                                            $status_text = [
                                                'pending' => 'قيد الانتظار',
                                                'confirmed' => 'مؤكد',
                                                'cancelled' => 'ملغي',
                                                'completed' => 'مكتمل'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_class[$booking['status']]; ?>">
                                                <?php echo $status_text[$booking['status']]; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="../pages/booking-details.php?id=<?php echo $booking['id']; ?>" 
                                                   class="btn btn-outline-primary" title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                                                    <a href="?cancel_id=<?php echo $booking['id']; ?>" 
                                                       class="btn btn-outline-danger" title="إلغاء الحجز"
                                                       onclick="return confirmAction('هل أنت متأكد من إلغاء هذا الحجز؟')">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($booking['status'] == 'completed'): ?>
                                                    <a href="#" class="btn btn-outline-success" title="تقييم الإقامة">
                                                        <i class="fas fa-star"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="../pages/invoice.php?id=<?php echo $booking['id']; ?>" 
                                                   class="btn btn-outline-info" title="طباعة الفاتورة">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- ملخص الإحصائيات -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-info">إجمالي الحجوزات</h6>
                                    <h2 class="text-info"><?php echo count($bookings); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-success">الحجوزات المؤكدة</h6>
                                    <h2 class="text-success"><?php echo count(array_filter($bookings, fn($b) => $b['status'] == 'confirmed')); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-danger">الحجوزات الملغاة</h6>
                                    <h2 class="text-danger"><?php echo count(array_filter($bookings, fn($b) => $b['status'] == 'cancelled')); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-primary">إجمالي المصروفات</h6>
                                    <h4 class="text-primary"><?php echo number_format(array_sum(array_column($bookings, 'total_price')), 2); ?> ريال</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- تذكير بالحجوزات القادمة -->
                    <?php
                    $upcoming_bookings = array_filter($bookings, function($b) {
                        $check_in = strtotime($b['check_in']);
                        $today = strtotime(date('Y-m-d'));
                        $diff = ($check_in - $today) / (60 * 60 * 24);
                        return in_array($b['status'], ['confirmed']) && $diff >= 0 && $diff <= 7;
                    });
                    
                    if (!empty($upcoming_bookings)): ?>
                        <div class="alert alert-warning mt-4">
                            <h6><i class="fas fa-bell"></i> تذكير بالحجوزات القادمة:</h6>
                            <ul class="mb-0">
                                <?php foreach ($upcoming_bookings as $booking): 
                                    $check_in = strtotime($booking['check_in']);
                                    $today = strtotime(date('Y-m-d'));
                                    $diff = ceil(($check_in - $today) / (60 * 60 * 24));
                                ?>
                                    <li>
                                        حجز #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?> 
                                        في <?php echo $booking['hotel_name']; ?> 
                                        بعد <?php echo $diff; ?> يوم
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- نصائح للحجوزات -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-lightbulb"></i> نصائح للحجوزات</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <i class="fas fa-calendar-check fa-2x text-primary mb-3"></i>
                            <h6>التأكيد الفوري</h6>
                            <p class="text-muted small">تأكيد الحجز خلال 24 ساعة</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <i class="fas fa-money-bill-wave fa-2x text-success mb-3"></i>
                            <h6>ضمان استرداد الأموال</h6>
                            <p class="text-muted small">إمكانية الإلغاء المجاني قبل 48 ساعة</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <i class="fas fa-headset fa-2x text-warning mb-3"></i>
                            <h6>دعم 24/7</h6>
                            <p class="text-muted small">خدمة عملاء متاحة على مدار الساعة</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// فلترة الحجوزات حسب الحالة
function filterBookings(status) {
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        if (status === 'all') {
            row.style.display = '';
        } else {
            const rowStatus = row.querySelector('td:nth-child(6)').textContent.trim();
            if (rowStatus === getStatusText(status)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
}

function getStatusText(status) {
    const statusMap = {
        'pending': 'قيد الانتظار',
        'confirmed': 'مؤكد',
        'cancelled': 'ملغي',
        'completed': 'مكتمل'
    };
    return statusMap[status] || '';
}

// تحميل الفلتر من الرابط
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get('filter');
    if (filter) {
        filterBookings(filter);
    }
});
</script>

<?php include '../includes/footer.php'; ?>