<?php
require_once '../config.php';
require_once '../includes/functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$page_title = 'لوحة التحكم';
$user_id = $_SESSION['user_id'];

// جلب بيانات المستخدم
$user = getUserData($user_id);
// جلب حجوزات المستخدم
$bookings = getUserBookings($user_id);

// إحصائيات
$total_bookings = count($bookings);
$active_bookings = array_filter($bookings, function($b) {
    return in_array($b['status'], ['pending', 'confirmed']);
});
$completed_bookings = array_filter($bookings, function($b) {
    return $b['status'] == 'completed';
});
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="row">
    <div class="col-md-3">
        <!-- القائمة الجانبية -->
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <img src="<?php echo PROFILE_IMG_PATH . $user['profile_image']; ?>" 
                     class="profile-img mb-3" alt="صورة الملف الشخصي">
                <h5 class="mb-1"><?php echo $user['full_name']; ?></h5>
                <p class="text-muted mb-3"><?php echo $user['email']; ?></p>
                <div class="d-grid gap-2">
                    <a href="profile.php" class="btn btn-outline-primary">
                        <i class="fas fa-user-edit"></i> تعديل الملف الشخصي
                    </a>
                </div>
            </div>
        </div>
        
        <div class="list-group shadow-sm">
            <a href="dashboard.php" class="list-group-item list-group-item-action active">
                <i class="fas fa-tachometer-alt"></i> لوحة التحكم
            </a>
            <a href="bookings.php" class="list-group-item list-group-item-action">
                <i class="fas fa-calendar-check"></i> حجوزاتي
            </a>
            <a href="profile.php" class="list-group-item list-group-item-action">
                <i class="fas fa-user"></i> الملف الشخصي
            </a>
            <a href="../logout.php" class="list-group-item list-group-item-action text-danger">
                <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
            </a>
        </div>
    </div>
    
    <div class="col-md-9">
        <!-- إحصائيات سريعة -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">إجمالي الحجوزات</h5>
                                <h2 class="mb-0"><?php echo $total_bookings; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">حجوزات نشطة</h5>
                                <h2 class="mb-0"><?php echo count($active_bookings); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-info text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">حجوزات مكتملة</h5>
                                <h2 class="mb-0"><?php echo count($completed_bookings); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clipboard-check fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- الحجوزات الأخيرة -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history"></i> الحجوزات الأخيرة</h5>
                <a href="bookings.php" class="btn btn-sm btn-outline-primary">عرض الكل</a>
            </div>
            <div class="card-body">
                <?php if (empty($bookings)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">لا توجد حجوزات سابقة</h5>
                        <a href="../pages/hotels.php" class="btn btn-primary mt-3">
                            <i class="fas fa-hotel"></i> ابحث عن فندق
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>رقم الحجز</th>
                                    <th>الفندق</th>
                                    <th>نوع الغرفة</th>
                                    <th>التواريخ</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($bookings, 0, 5) as $booking): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo $booking['hotel_name']; ?></td>
                                        <td><?php echo $booking['room_type']; ?></td>
                                        <td>
                                            <?php echo date('Y-m-d', strtotime($booking['check_in'])); ?><br>
                                            إلى <?php echo date('Y-m-d', strtotime($booking['check_out'])); ?>
                                        </td>
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
                                                <?php
                                                $status_text = [
                                                    'pending' => 'قيد الانتظار',
                                                    'confirmed' => 'مؤكد',
                                                    'cancelled' => 'ملغي',
                                                    'completed' => 'مكتمل'
                                                ];
                                                echo $status_text[$booking['status']];
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- نشرة أخبار -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-bell"></i> إشعارات مهمة</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">عرض خاص!</h6>
                                    <small>قبل يومين</small>
                                </div>
                                <p class="mb-1">احصل على خصم 20% على جميع الحجوزات هذا الأسبوع</p>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">تحديث النظام</h6>
                                    <small>قبل أسبوع</small>
                                </div>
                                <p class="mb-1">تم إضافة ميزات جديدة لتحسين تجربة الحجز</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-lightbulb"></i> نصائح سريعة</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success"></i> تأكد من تحديث بياناتك الشخصية</li>
                            <li class="mb-2"><i class="fas fa-check text-success"></i> راجع الحجوزات القادمة</li>
                            <li class="mb-2"><i class="fas fa-check text-success"></i> قم بتقييم الفنادق بعد الإقامة</li>
                            <li class="mb-2"><i class="fas fa-check text-success"></i> احفظ الفنادق المفضلة لديك</li>
                            <li class="mb-2"><i class="fas fa-check text-success"></i> اشترك في نشرتنا البريدية للعروض الخاصة</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>