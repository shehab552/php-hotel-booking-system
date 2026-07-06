<?php
require_once '../config.php';
require_once '../includes/functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

// التحقق من وجود معرف الحجز
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('hotels.php');
}

$booking_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// جلب بيانات الحجز
$sql = "SELECT b.*, h.name as hotel_name, h.address as hotel_address, 
               h.city, h.country, h.images as hotel_images,
               r.room_type, r.room_number, r.price_per_night, r.description as room_description,
               u.full_name, u.email, u.phone, u.profile_image
        FROM bookings b 
        JOIN hotels h ON b.hotel_id = h.id 
        JOIN rooms r ON b.room_id = r.id 
        JOIN users u ON b.user_id = u.id 
        WHERE b.id = ? AND b.user_id = ?";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die('Error preparing query: ' . $conn->error);
}

$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    redirect('../user/bookings.php');
}

$page_title = 'تفاصيل الحجز #' . str_pad($booking_id, 6, '0', STR_PAD_LEFT);
?>


<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-md-3">
        <!-- القائمة الجانبية -->
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <h5 class="mb-3">الحجز #<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></h5>
                <span class="badge bg-<?php 
                    $status_colors = [
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info'
                    ];
                    echo $status_colors[$booking['status']] ?? 'secondary';
                ?> p-2">
                    <?php 
                    $status_text = [
                        'pending' => 'قيد الانتظار',
                        'confirmed' => 'مؤكد',
                        'cancelled' => 'ملغي',
                        'completed' => 'مكتمل'
                    ];
                    echo $status_text[$booking['status']] ?? $booking['status'];
                    ?>
                </span>
            </div>
        </div>
        
        <div class="list-group shadow-sm">
            <a href="../user/bookings.php" class="list-group-item list-group-item-action">
                <i class="fas fa-arrow-right"></i> العودة للحجوزات
            </a>
            <a href="hotel-details.php?id=<?php echo $booking['hotel_id']; ?>" class="list-group-item list-group-item-action">
                <i class="fas fa-hotel"></i> عرض الفندق
            </a>
            <a href="#" class="list-group-item list-group-item-action" onclick="printInvoice()">
                <i class="fas fa-print"></i> طباعة الفاتورة
            </a>
            <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                <a href="#" class="list-group-item list-group-item-action text-danger" 
                   onclick="cancelBooking(<?php echo $booking_id; ?>)">
                    <i class="fas fa-times"></i> إلغاء الحجز
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card shadow-lg">
            <div class="card-header bg-white">
                <h4 class="mb-0">تفاصيل الحجز</h4>
            </div>
            <div class="card-body">
                <!-- معلومات الحجز -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h5>حجز فندق <?php echo htmlspecialchars($booking['hotel_name']); ?></h5>
                        <p class="text-muted">
                            <i class="fas fa-calendar-alt"></i> 
                            <?php echo date('d/m/Y', strtotime($booking['check_in'])); ?> 
                            إلى 
                            <?php echo date('d/m/Y', strtotime($booking['check_out'])); ?>
                            (<?php echo $booking['total_nights']; ?> ليلة)
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <h4 class="text-primary"><?php echo number_format($booking['total_price'], 2); ?> ريال</h4>
                        <small class="text-muted">السعر الإجمالي</small>
                    </div>
                </div>
                
                <!-- تفاصيل الإقامة -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-primary mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle"></i> معلومات الإقامة</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <th>رقم الحجز:</th>
                                        <td>#<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ الحجز:</th>
                                        <td><?php echo date('Y-m-d H:i', strtotime($booking['booking_date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ الوصول:</th>
                                        <td><?php echo date('d/m/Y', strtotime($booking['check_in'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ المغادرة:</th>
                                        <td><?php echo date('d/m/Y', strtotime($booking['check_out'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>عدد الليالي:</th>
                                        <td><?php echo $booking['total_nights']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>عدد الضيوف:</th>
                                        <td><?php echo $booking['guests']; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-success mb-4">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-hotel"></i> معلومات الفندق</h6>
                            </div>
                            <div class="card-body">
                                <h6><?php echo htmlspecialchars($booking['hotel_name']); ?></h6>
                                <p class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?php echo htmlspecialchars($booking['hotel_address']); ?><br>
                                    <?php echo htmlspecialchars($booking['city']); ?>، <?php echo htmlspecialchars($booking['country']); ?>
                                </p>
                                
                                <?php if ($booking['hotel_phone']): ?>
                                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($booking['hotel_phone']); ?></p>
                                <?php endif; ?>
                                
                                <p><strong>نوع الغرفة:</strong> <?php echo htmlspecialchars($booking['room_type']); ?></p>
                                <p><strong>رقم الغرفة:</strong> <?php echo htmlspecialchars($booking['room_number'] ?? 'سيتم تحديده لاحقاً'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- معلومات الغرفة -->
                <div class="card border-info mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-bed"></i> معلومات الغرفة</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6><?php echo htmlspecialchars($booking['room_type']); ?></h6>
                                <?php if ($booking['room_description']): ?>
                                    <p><?php echo nl2br(htmlspecialchars($booking['room_description'])); ?></p>
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>سعر الليلة:</strong> <?php echo number_format($booking['price_per_night'], 2); ?> ريال</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>السعر الإجمالي:</strong> <?php echo number_format($booking['total_price'], 2); ?> ريال</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <?php 
                                $images = isset($booking['hotel_images']) && !empty($booking['hotel_images']) ? 
                                          json_decode($booking['hotel_images'], true) : ['default.jpg'];
                                $main_image = HOTEL_IMG_PATH . $images[0];
                                ?>
                                <img src="<?php echo $main_image; ?>" 
                                     class="img-fluid rounded" alt="صورة الفندق"
                                     style="height: 150px; object-fit: cover; width: 100%;">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- معلومات الدفع -->
                <div class="card border-warning mb-4">
                    <div class="card-header bg-warning text-white">
                        <h6 class="mb-0"><i class="fas fa-money-bill-wave"></i> معلومات الدفع</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>طريقة الدفع:</strong> <?php echo htmlspecialchars($booking['payment_method'] ?? 'دفع عند الوصول'); ?></p>
                                <p><strong>حالة الدفع:</strong> 
                                    <span class="badge bg-<?php echo $booking['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                        <?php echo $booking['payment_status'] == 'paid' ? 'مدفوع' : 'لم يدفع بعد'; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>إجمالي الفاتورة:</strong> <?php echo number_format($booking['total_price'], 2); ?> ريال</p>
                                <p><strong>الخصومات:</strong> <?php echo number_format($booking['discount'] ?? 0, 2); ?> ريال</p>
                            </div>
                        </div>
                        
                        <?php if (!empty($booking['special_requests'])): ?>
                            <div class="alert alert-light border mt-3">
                                <h6><i class="fas fa-sticky-note"></i> الطلبات الخاصة:</h6>
                                <p><?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- معلومات العميل -->
                <div class="card border-secondary">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-user"></i> معلومات العميل</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo PROFILE_IMG_PATH . $booking['profile_image']; ?>" 
                                         class="rounded-circle me-3" width="60" height="60">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($booking['full_name']); ?></h6>
                                        <p class="text-muted mb-1"><?php echo htmlspecialchars($booking['email']); ?></p>
                                        <?php if ($booking['phone']): ?>
                                            <p class="text-muted mb-0"><?php echo htmlspecialchars($booking['phone']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <p><strong>رقم المستخدم:</strong> <?php echo $user_id; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- أزرار الإجراءات -->
            <div class="card-footer bg-white">
                <div class="btn-group" role="group">
                    <button onclick="printInvoice()" class="btn btn-outline-primary">
                        <i class="fas fa-print"></i> طباعة الفاتورة
                    </button>
                    <button onclick="downloadPDF()" class="btn btn-outline-success">
                        <i class="fas fa-download"></i> تحميل PDF
                    </button>
                    <button onclick="shareBooking()" class="btn btn-outline-info">
                        <i class="fas fa-share-alt"></i> مشاركة
                    </button>
                    <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                        <button onclick="cancelBooking(<?php echo $booking_id; ?>)" class="btn btn-outline-danger">
                            <i class="fas fa-times"></i> إلغاء الحجز
                        </button>
                    <?php endif; ?>
                    <?php if ($booking['status'] == 'completed'): ?>
                        <button onclick="addReview()" class="btn btn-outline-warning">
                            <i class="fas fa-star"></i> إضافة تقييم
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- نموذج إلغاء الحجز -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إلغاء الحجز</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    هل أنت متأكد من إلغاء هذا الحجز؟
                </div>
                
                <div id="cancellationPolicy" class="mt-3">
                    <h6>سياسة الإلغاء:</h6>
                    <?php 
                    $check_in_date = new DateTime($booking['check_in']);
                    $today = new DateTime();
                    $days_diff = $today->diff($check_in_date)->days;
                    
                    if ($days_diff > 2) {
                        echo '<p class="text-success">يمكنك الإلغاء مجاناً قبل ' . $days_diff . ' أيام</p>';
                    } elseif ($days_diff <= 2 && $days_diff > 0) {
                        echo '<p class="text-warning">سيتم خصم 50% من قيمة الحجز للإلغاء خلال 48 ساعة</p>';
                    } else {
                        echo '<p class="text-danger">لا يمكن الإلغاء بعد تاريخ الوصول</p>';
                    }
                    ?>
                </div>
                
                <div class="mb-3 mt-3">
                    <label for="cancel_reason" class="form-label">سبب الإلغاء (اختياري)</label>
                    <textarea class="form-control" id="cancel_reason" rows="3" 
                              placeholder="اختياري: اذكر سبب الإلغاء لمساعدتنا على التحسين"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-danger" onclick="confirmCancellation()">تأكيد الإلغاء</button>
            </div>
        </div>
    </div>
</div>

<!-- نموذج التقييم -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تقييم الإقامة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">التقييم</label>
                    <div class="star-rating mb-3" id="reviewStars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star fa-2x star" data-value="<?php echo $i; ?>" 
                               style="cursor: pointer; color: #ddd; margin: 0 2px;"></i>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" id="review_rating" value="5">
                </div>
                
                <div class="mb-3">
                    <label for="review_comment" class="form-label">التعليق</label>
                    <textarea class="form-control" id="review_comment" rows="4" 
                              placeholder="شاركنا بتجربتك في هذا الفندق..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary" onclick="submitReview()">إرسال التقييم</button>
            </div>
        </div>
    </div>
</div>

<script>
// إلغاء الحجز
function cancelBooking(bookingId) {
    const checkInDate = new Date('<?php echo $booking['check_in']; ?>');
    const today = new Date();
    const timeDiff = checkInDate.getTime() - today.getTime();
    const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
    
    if (daysDiff <= 0) {
        alert('لا يمكن إلغاء الحجز بعد تاريخ الوصول');
        return;
    }
    
    // عرض نافذة الإلغاء
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    modal.show();
}

// تأكيد الإلغاء
function confirmCancellation() {
    const reason = document.getElementById('cancel_reason').value;
    const bookingId = <?php echo $booking_id; ?>;
    
    fetch('../user/cancel-booking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            booking_id: bookingId,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم إلغاء الحجز بنجاح');
            location.reload();
        } else {
            alert('حدث خطأ: ' + data.message);
        }
    })
    .catch(error => {
        alert('حدث خطأ أثناء الإلغاء');
    });
}

// طباعة الفاتورة
function printInvoice() {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>فاتورة الحجز #<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .section { margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { padding: 8px; text-align: right; border-bottom: 1px solid #ddd; }
                .total { font-weight: bold; font-size: 1.2em; color: #2A4B8C; }
                .footer { text-align: center; margin-top: 50px; color: #666; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h2 style="color: #2A4B8C;"><?php echo SITE_NAME; ?></h2>
                <h3>فاتورة الحجز</h3>
                <p>رقم الحجز: #<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></p>
                <p>تاريخ الطباعة: ${new Date().toLocaleDateString()}</p>
            </div>
            
            <div class="section">
                <h4>معلومات الحجز</h4>
                <table>
                    <tr>
                        <th>الفندق:</th>
                        <td><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                    </tr>
                    <tr>
                        <th>نوع الغرفة:</th>
                        <td><?php echo htmlspecialchars($booking['room_type']); ?></td>
                    </tr>
                    <tr>
                        <th>تاريخ الوصول:</th>
                        <td><?php echo date('d/m/Y', strtotime($booking['check_in'])); ?></td>
                    </tr>
                    <tr>
                        <th>تاريخ المغادرة:</th>
                        <td><?php echo date('d/m/Y', strtotime($booking['check_out'])); ?></td>
                    </tr>
                    <tr>
                        <th>عدد الليالي:</th>
                        <td><?php echo $booking['total_nights']; ?></td>
                    </tr>
                    <tr>
                        <th>عدد الضيوف:</th>
                        <td><?php echo $booking['guests']; ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="section">
                <h4>تفاصيل الدفع</h4>
                <table>
                    <tr>
                        <th>سعر الليلة:</th>
                        <td><?php echo number_format($booking['price_per_night'], 2); ?> ريال</td>
                    </tr>
                    <tr>
                        <th>عدد الليالي:</th>
                        <td>× <?php echo $booking['total_nights']; ?></td>
                    </tr>
                    <tr class="total">
                        <th>الإجمالي:</th>
                        <td><?php echo number_format($booking['total_price'], 2); ?> ريال</td>
                    </tr>
                </table>
            </div>
            
            <div class="section">
                <h4>معلومات العميل</h4>
                <p><strong>الاسم:</strong> <?php echo htmlspecialchars($booking['full_name']); ?></p>
                <p><strong>البريد:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
                <?php if ($booking['phone']): ?>
                    <p><strong>الهاتف:</strong> <?php echo htmlspecialchars($booking['phone']); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="footer">
                <p>شكراً لك على اختيارك <?php echo SITE_NAME; ?></p>
                <p><?php echo SITE_URL; ?></p>
            </div>
            
            <div class="no-print" style="text-align: center; margin-top: 30px;">
                <button onclick="window.print()">طباعة</button>
                <button onclick="window.close()">إغلاق</button>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
}

// تحميل PDF
function downloadPDF() {
    // يمكن استخدام مكتبة jsPDF هنا
    alert('سيتم تحميل ملف PDF. شكراً لك!');
}

// مشاركة الحجز
function shareBooking() {
    if (navigator.share) {
        navigator.share({
            title: 'حجز فندق <?php echo htmlspecialchars($booking['hotel_name']); ?>',
            text: 'لقد حجزت في فندق <?php echo htmlspecialchars($booking['hotel_name']); ?>',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('تم نسخ رابط الحجز إلى الحافظة');
    }
}

// إضافة تقييم
function addReview() {
    const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
    modal.show();
}

// تقييم النجوم
document.querySelectorAll('.star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = parseInt(this.getAttribute('data-value'));
        document.getElementById('review_rating').value = rating;
        
        // تحديث مظهر النجوم
        document.querySelectorAll('.star').forEach(s => {
            const starValue = parseInt(s.getAttribute('data-value'));
            if (starValue <= rating) {
                s.style.color = '#FFD700';
            } else {
                s.style.color = '#ddd';
            }
        });
    });
});

// إرسال التقييم
function submitReview() {
    const rating = document.getElementById('review_rating').value;
    const comment = document.getElementById('review_comment').value;
    const bookingId = <?php echo $booking_id; ?>;
    const hotelId = <?php echo $booking['hotel_id']; ?>;
    
    if (!comment.trim()) {
        alert('يرجى إضافة تعليق للتقييم');
        return;
    }
    
    fetch('../user/add-review.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            booking_id: bookingId,
            hotel_id: hotelId,
            rating: rating,
            comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('شكراً لك على تقييمك!');
            bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
        } else {
            alert('حدث خطأ: ' + data.message);
        }
    })
    .catch(error => {
        alert('حدث خطأ أثناء إرسال التقييم');
    });
}

// العد التنازلي لتاريخ الوصول
function updateCountdown() {
    const checkInDate = new Date('<?php echo $booking['check_in']; ?>');
    const now = new Date();
    const timeDiff = checkInDate.getTime() - now.getTime();
    
    if (timeDiff > 0) {
        const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
        
        const countdownElement = document.createElement('div');
        countdownElement.className = 'alert alert-info text-center';
        countdownElement.innerHTML = `
            <i class="fas fa-clock"></i> الوقت المتبقي للوصول: 
            ${days} يوم و ${hours} ساعة و ${minutes} دقيقة
        `;
        
        document.querySelector('.card-body').prepend(countdownElement);
    }
}

// تحديث العد التنازلي كل دقيقة
if (new Date('<?php echo $booking['check_in']; ?>') > new Date()) {
    updateCountdown();
    setInterval(updateCountdown, 60000);
}
</script>

<style>
.card {
    border-radius: 10px;
    overflow: hidden;
}

.card-header {
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.table th {
    font-weight: 600;
    color: #555;
}

.star-rating .fas.fa-star {
    color: #FFD700;
}

@media print {
    .navbar, .list-group, .btn-group, .card-footer, .modal {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>