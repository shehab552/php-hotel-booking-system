<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$page_title = 'إتمام الحجز';
$user_id = $_SESSION['user_id'];

// التحقق من وجود بيانات الحجز
if (!isset($_GET['hotel_id']) || !isset($_GET['room_id']) || 
    !isset($_GET['check_in']) || !isset($_GET['nights'])) {
    redirect('hotels.php');
}

$hotel_id = intval($_GET['hotel_id']);
$room_id = intval($_GET['room_id']);
$check_in = $_GET['check_in'];
$nights = intval($_GET['nights']);
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 1;

// حساب تاريخ المغادرة
$check_out = date('Y-m-d', strtotime($check_in . " + $nights days"));

// جلب بيانات الفندق والغرفة
$hotel = getHotelById($hotel_id);
$room = getRoomById($room_id);

// التحقق من صحة البيانات
if (!$hotel || !$room || $room['hotel_id'] != $hotel_id) {
    redirect('hotels.php');
}

// التحقق من توفر الغرفة
if ($room['available_rooms'] < 1) {
    $error_messages[] = 'عذراً، هذه الغرفة غير متاحة حالياً';
}

// حساب السعر
$total_price = $room['price_per_night'] * $nights;

// معالجة الحجز
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $special_requests = cleanInput($_POST['special_requests'] ?? '');
    
    // إنشاء الحجز
    $booking_id = createBooking($user_id, $room_id, $check_in, $check_out, $guests, $special_requests);
    
    if ($booking_id) {
       redirect('../pages/booking-confirmation.php?id=' . $booking_id);
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- مسار الحجز -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">إتمام الحجز</h3>
                <div class="steps">
                    <span class="badge bg-primary p-2">1. اختيار الغرفة</span>
                    <span class="mx-2">→</span>
                    <span class="badge bg-primary p-2">2. بيانات الحجز</span>
                    <span class="mx-2">→</span>
                    <span class="badge bg-secondary p-2">3. التأكيد</span>
                </div>
            </div>
        </div>
        
        <?php displayMessages(); ?>
        
        <div class="row">
            <!-- تفاصيل الحجز -->
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> تفاصيل الحجز</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <img src="<?php 
                                    if (!empty($hotel['main_image'])) {
                                        echo BASE_URL . 'uploads/hotels/' . $hotel['main_image'];
                                    } else {
                                        echo 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80';
                                    }
                                ?>" 
                                     class="img-fluid rounded mb-3" alt="<?php echo $hotel['name']; ?>">
                            </div>
                            <div class="col-md-8">
                                <h4><?php echo $hotel['name']; ?></h4>
                                <p class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?php echo $hotel['city']; ?>، <?php echo $hotel['country']; ?>
                                </p>
                                
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <h6>تفاصيل الإقامة</h6>
                                        <p class="mb-1">
                                            <strong>تاريخ الوصول:</strong> 
                                            <?php echo date('d/m/Y', strtotime($check_in)); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>تاريخ المغادرة:</strong> 
                                            <?php echo date('d/m/Y', strtotime($check_out)); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>عدد الليالي:</strong> <?php echo $nights; ?>
                                        </p>
                                        <p class="mb-0">
                                            <strong>عدد الضيوف:</strong> <?php echo $guests; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>تفاصيل الغرفة</h6>
                                        <p class="mb-1">
                                            <strong>نوع الغرفة:</strong> <?php echo $room['room_type']; ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>رقم الغرفة:</strong> <?php echo $room['room_number'] ?? 'غير محدد'; ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>السعة:</strong> <?php echo $room['capacity']; ?> أشخاص
                                        </p>
                                        <p class="mb-0">
                                            <strong>السعر/ليلة:</strong> 
                                            <?php echo number_format($room['price_per_night'], 2); ?> ريال
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- معلومات الضيف -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-user"></i> معلومات الضيف</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">الاسم الكامل</label>
                                <input type="text" class="form-control" value="<?php echo $_SESSION['full_name']; ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" value="<?php echo $_SESSION['email']; ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="special_requests" class="form-label">طلبات خاصة (اختياري)</label>
                            <textarea class="form-control" id="special_requests" name="special_requests" 
                                      rows="3" placeholder="أي طلبات خاصة أو احتياجات خاصة..."></textarea>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="save_info" checked>
                            <label class="form-check-label" for="save_info">
                                حفظ معلوماتي للمرة القادمة
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- طريقة الدفع -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-credit-card"></i> طريقة الدفع</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            سيتم الدفع عند الوصول إلى الفندق
                        </div>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="pay_at_hotel" checked>
                            <label class="form-check-label" for="pay_at_hotel">
                                <i class="fas fa-building"></i> الدفع عند الوصول
                            </label>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="credit_card" disabled>
                            <label class="form-check-label text-muted" for="credit_card">
                                <i class="fas fa-credit-card"></i> بطاقة ائتمان (غير متاح حالياً)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ملخص الحجز -->
            <div class="col-md-4">
                <div class="card shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-receipt"></i> ملخص الحجز</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td>السعر/ليلة</td>
                                <td class="text-end"><?php echo number_format($room['price_per_night'], 2); ?> ريال</td>
                            </tr>
                            <tr>
                                <td>عدد الليالي</td>
                                <td class="text-end">× <?php echo $nights; ?></td>
                            </tr>
                            <tr class="border-top">
                                <td><strong>الإجمالي</strong></td>
                                <td class="text-end">
                                    <strong class="text-primary"><?php echo number_format($total_price, 2); ?> ريال</strong>
                                </td>
                            </tr>
                        </table>
                        
                        <div class="alert alert-success small">
                            <i class="fas fa-check-circle"></i> 
                            يشمل السعر: الضرائب والرسوم
                        </div>
                        
                        <!-- الشروط والأحكام -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label small" for="terms">
                                أوافق على <a href="#" class="text-primary">شروط الاستخدام</a> و 
                                <a href="#" class="text-primary">سياسة الإلغاء</a>
                            </label>
                        </div>
                        
                        <form method="POST" action="" onsubmit="return confirmBooking()">
                            <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
                            <input type="hidden" name="check_in" value="<?php echo $check_in; ?>">
                            <input type="hidden" name="nights" value="<?php echo $nights; ?>">
                            <input type="hidden" name="guests" value="<?php echo $guests; ?>">
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-lock"></i> تأكيد الحجز
                                </button>
                                <a href="hotel-details.php?id=<?php echo $hotel_id; ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-right"></i> العودة
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- معلومات مهمة -->
                    <div class="card-footer bg-light">
                        <h6 class="mb-2"><i class="fas fa-shield-alt"></i> حجز آمن</h6>
                        <ul class="small text-muted mb-0">
                            <li>بياناتك محمية ومشفرة</li>
                            <li>لا توجد رسوم إضافية</li>
                            <li>ضمان أفضل سعر</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmBooking() {
    if (!document.getElementById('terms').checked) {
        alert('يرجى الموافقة على الشروط والأحكام');
        return false;
    }
    
    return confirm('هل أنت متأكد من تأكيد الحجز؟');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>