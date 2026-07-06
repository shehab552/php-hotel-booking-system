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
$sql = "SELECT 
            b.*, 
            h.name AS hotel_name, 
            h.address AS hotel_address, 
            h.city, 
            h.country,
            r.room_type, 
            r.price_per_night,
            u.full_name, 
            u.email
        FROM bookings b 
        JOIN hotels h ON b.hotel_id = h.id 
        JOIN rooms r ON b.room_id = r.id 
        JOIN users u ON b.user_id = u.id 
        WHERE b.id = ? AND b.user_id = ?";

        
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    redirect('hotels.php');
}

$page_title = 'تأكيد الحجز';
?>

<?php include '../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-lg">
            <div class="card-header bg-success text-white text-center py-4">
                <h2 class="mb-0"><i class="fas fa-check-circle"></i> تم تأكيد حجزك بنجاح!</h2>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                    <h4 class="text-success">شكراً لك <?php echo $_SESSION['full_name']; ?>!</h4>
                    <p class="text-muted">تم تأكيد حجزك بنجاح، إليك تفاصيل الحجز:</p>
                </div>
                
                <!-- معلومات الحجز -->
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>رقم الحجز:</strong> 
                                <span class="badge bg-primary">#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></span>
                            </p>
                            <p class="mb-1"><strong>تاريخ الحجز:</strong> <?php echo date('Y-m-d H:i', strtotime($booking['booking_date'])); ?></p>
                            <p class="mb-1"><strong>حالة الحجز:</strong> 
                                <span class="badge bg-<?php 
                                    $status_colors = [
                                        'pending' => 'warning',
                                        'confirmed' => 'success',
                                        'cancelled' => 'danger',
                                        'completed' => 'info'
                                    ];
                                    echo $status_colors[$booking['status']] ?? 'secondary';
                                ?>">
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
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>تاريخ الوصول:</strong> <?php echo date('d/m/Y', strtotime($booking['check_in'])); ?></p>
                            <p class="mb-1"><strong>تاريخ المغادرة:</strong> <?php echo date('d/m/Y', strtotime($booking['check_out'])); ?></p>
                            <p class="mb-0"><strong>عدد الليالي:</strong> <?php echo $booking['total_nights']; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- تفاصيل الفندق -->
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-hotel"></i> معلومات الفندق</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5><?php echo htmlspecialchars($booking['hotel_name']); ?></h5>
                                <p class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?php echo htmlspecialchars($booking['hotel_address']); ?><br>
                                    <?php echo htmlspecialchars($booking['city']); ?>، <?php echo htmlspecialchars($booking['country']); ?>
                                </p>
                                <?php if ($booking['hotel_phone']): ?>
                                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($booking['hotel_phone']); ?></p>
                                <?php endif; ?>
                                <?php if ($booking['hotel_email']): ?>
                                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($booking['hotel_email']); ?></p>
                                <?php endif; ?>
                                
                                <p><strong>نوع الغرفة:</strong> <?php echo htmlspecialchars($booking['room_type']); ?></p>
                                <p><strong>رقم الغرفة:</strong> <?php echo htmlspecialchars($booking['room_number'] ?? 'سيتم تحديده لاحقاً'); ?></p>
                                <p><strong>عدد الضيوف:</strong> <?php echo $booking['guests']; ?></p>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="qrcode" id="qrcode"></div>
                                <small class="text-muted">QR Code لحجزك</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- تفاصيل الدفع -->
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-receipt"></i> ملخص الدفع</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <td>سعر الليلة الواحدة</td>
                                <td class="text-end"><?php echo number_format($booking['price_per_night'], 2); ?> ريال</td>
                            </tr>
                            <tr>
                                <td>عدد الليالي</td>
                                <td class="text-end">× <?php echo $booking['total_nights']; ?></td>
                            </tr>
                            <tr>
                                <td>عدد الضيوف</td>
                                <td class="text-end">× <?php echo $booking['guests']; ?></td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>المبلغ الإجمالي</strong></td>
                                <td class="text-end">
                                    <strong><?php echo number_format($booking['total_price'], 2); ?> ريال</strong>
                                </td>
                            </tr>
                        </table>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i> 
                            <strong>ملاحظة:</strong> سيتم الدفع عند الوصول إلى الفندق
                        </div>
                        
                        <?php if (!empty($booking['special_requests'])): ?>
                            <div class="alert alert-light border">
                                <strong>طلباتك الخاصة:</strong><br>
                                <?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- معلومات مهمة -->
                <div class="alert alert-light border mt-4">
                    <h6><i class="fas fa-lightbulb"></i> معلومات مهمة للإقامة:</h6>
                    <ul class="mb-0">
                        <li>يرجى إحضار بطاقة الهوية عند الوصول</li>
                        <li>تسجيل الوصول من الساعة 2:00 مساءً</li>
                        <li>تسجيل المغادرة حتى الساعة 12:00 ظهراً</li>
                        <li>للاستفسارات يمكنك التواصل مع الفندق مباشرة</li>
                        <li>احفظ رقم الحجز للرجوع إليه عند الحاجة</li>
                    </ul>
                </div>
                
                <!-- أزرار الإجراءات -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                    <a href="../user/bookings.php" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i> عرض جميع حجوزاتي
                    </a>
                    <button onclick="printInvoice()" class="btn btn-success">
                        <i class="fas fa-print"></i> طباعة الفاتورة
                    </button>
                    <button onclick="downloadPDF()" class="btn btn-outline-primary">
                        <i class="fas fa-download"></i> تحميل PDF
                    </button>
                    <a href="hotels.php" class="btn btn-outline-info">
                        <i class="fas fa-hotel"></i> حجز فندق آخر
                    </a>
                </div>
            </div>
            
            <div class="card-footer bg-light text-center">
                <p class="mb-0 text-muted">
                    <i class="fas fa-envelope"></i> 
                    تم إرسال تفاصيل الحجز إلى بريدك الإلكتروني: <?php echo $_SESSION['email']; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- نموذج الطباعة -->
<div id="invoiceContent" style="display: none;">
    <div style="padding: 20px; font-family: Arial, sans-serif;">
        <h2 style="text-align: center; color: #2A4B8C;">فاتورة الحجز</h2>
        <hr>
        
        <div style="margin-bottom: 20px;">
            <h4>معلومات الحجز</h4>
            <p><strong>رقم الحجز:</strong> #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
            <p><strong>تاريخ الحجز:</strong> <?php echo date('Y-m-d H:i', strtotime($booking['booking_date'])); ?></p>
            <p><strong>حالة الحجز:</strong> <?php echo $status_text[$booking['status']] ?? $booking['status']; ?></p>
        </div>
        
        <div style="margin-bottom: 20px;">
            <h4>معلومات الفندق</h4>
            <p><strong>اسم الفندق:</strong> <?php echo htmlspecialchars($booking['hotel_name']); ?></p>
            <p><strong>العنوان:</strong> <?php echo htmlspecialchars($booking['hotel_address']); ?></p>
            <p><strong>المدينة:</strong> <?php echo htmlspecialchars($booking['city']); ?>، <?php echo htmlspecialchars($booking['country']); ?></p>
        </div>
        
        <div style="margin-bottom: 20px;">
            <h4>تفاصيل الإقامة</h4>
            <p><strong>تاريخ الوصول:</strong> <?php echo date('d/m/Y', strtotime($booking['check_in'])); ?></p>
            <p><strong>تاريخ المغادرة:</strong> <?php echo date('d/m/Y', strtotime($booking['check_out'])); ?></p>
            <p><strong>عدد الليالي:</strong> <?php echo $booking['total_nights']; ?></p>
            <p><strong>عدد الضيوف:</strong> <?php echo $booking['guests']; ?></p>
            <p><strong>نوع الغرفة:</strong> <?php echo htmlspecialchars($booking['room_type']); ?></p>
        </div>
        
        <div style="margin-bottom: 20px;">
            <h4>الدفع</h4>
            <p><strong>سعر الليلة:</strong> <?php echo number_format($booking['price_per_night'], 2); ?> ريال</p>
            <p><strong>الإجمالي:</strong> <?php echo number_format($booking['total_price'], 2); ?> ريال</p>
            <p><strong>طريقة الدفع:</strong> الدفع عند الوصول</p>
        </div>
        
        <div style="margin-top: 30px; border-top: 1px solid #ccc; padding-top: 10px;">
            <p><strong>ملاحظات:</strong></p>
            <p>• يرجى إحضار هذه الفاتورة معك عند الوصول</p>
            <p>• تسجيل الوصول من الساعة 2:00 مساءً</p>
            <p>• تسجيل المغادرة حتى الساعة 12:00 ظهراً</p>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <p>شكراً لك على اختيارك <?php echo SITE_NAME; ?></p>
            <p><?php echo date('Y-m-d'); ?> - <?php echo SITE_URL; ?></p>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// إنشاء QR Code
new QRCode(document.getElementById("qrcode"), {
    text: "Booking ID: <?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>\n" +
          "Hotel: <?php echo htmlspecialchars($booking['hotel_name']); ?>\n" +
          "Check-in: <?php echo date('d/m/Y', strtotime($booking['check_in'])); ?>\n" +
          "Check-out: <?php echo date('d/m/Y', strtotime($booking['check_out'])); ?>\n" +
          "Guest: <?php echo htmlspecialchars($booking['full_name']); ?>",
    width: 100,
    height: 100
});

// طباعة الفاتورة
function printInvoice() {
    const printContent = document.getElementById('invoiceContent').innerHTML;
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    location.reload();
}

// تحميل PDF (وهمي حالياً)
function downloadPDF() {
    alert('سيتم تحميل ملف PDF قريباً. شكراً لك!');
    // يمكن إضافة مكتبة jsPDF هنا لإنشاء ملف PDF ديناميكياً
}

// إضافة إلى التقويم
function addToCalendar() {
    const event = {
        title: 'حجز فندق <?php echo htmlspecialchars($booking['hotel_name']); ?>',
        start: '<?php echo $booking['check_in']; ?>',
        end: '<?php echo $booking['check_out']; ?>',
        location: '<?php echo htmlspecialchars($booking['hotel_address']); ?>',
        description: 'رقم الحجز: <?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>'
    };
    
    // إنشاء ملف ICS
    const icsContent = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'BEGIN:VEVENT',
        'SUMMARY:' + event.title,
        'DTSTART:' + event.start.replace(/-/g, ''),
        'DTEND:' + event.end.replace(/-/g, ''),
        'LOCATION:' + event.location,
        'DESCRIPTION:' + event.description,
        'END:VEVENT',
        'END:VCALENDAR'
    ].join('\n');
    
    const blob = new Blob([icsContent], { type: 'text/calendar' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'booking-<?php echo $booking_id; ?>.ics';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    
    alert('تم إنشاء ملف التقويم. افتح الملف لإضافة الحدث إلى تقويمك.');
}

// مشاركة الحجز
function shareBooking() {
    if (navigator.share) {
        navigator.share({
            title: 'حجز فندق <?php echo htmlspecialchars($booking['hotel_name']); ?>',
            text: 'لقد قمت بحجز فندق <?php echo htmlspecialchars($booking['hotel_name']); ?>',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('تم نسخ رابط الحجز إلى الحافظة');
    }
}

// تهيئة الأزرار
document.addEventListener('DOMContentLoaded', function() {
    // زر إضافة إلى التقويم
    const calendarBtn = document.createElement('button');
    calendarBtn.className = 'btn btn-outline-info mt-2';
    calendarBtn.innerHTML = '<i class="fas fa-calendar-plus"></i> إضافة إلى التقويم';
    calendarBtn.onclick = addToCalendar;
    
    // زر المشاركة
    const shareBtn = document.createElement('button');
    shareBtn.className = 'btn btn-outline-warning mt-2';
    shareBtn.innerHTML = '<i class="fas fa-share-alt"></i> مشاركة الحجز';
    shareBtn.onclick = shareBooking;
    
    // إضافة الأزرار إلى الصفحة
    const actionsDiv = document.querySelector('.d-grid');
    if (actionsDiv) {
        actionsDiv.appendChild(calendarBtn);
        actionsDiv.appendChild(shareBtn);
    }
    
    // تتبع انتهاء صلاحية الصفحة
    let expireTime = 5; // دقائق
    const expireMsg = document.createElement('div');
    expireMsg.className = 'alert alert-warning text-center mt-3';
    expireMsg.innerHTML = '<i class="fas fa-hourglass-end"></i> هذه الصفحة ستنتهي صلاحيتها خلال <span id="expireTimer">05:00</span>';
    
    document.querySelector('.card-body').appendChild(expireMsg);
    
    let expireSeconds = expireTime * 60;
    const expireInterval = setInterval(() => {
        expireSeconds--;
        const minutes = Math.floor(expireSeconds / 60);
        const seconds = expireSeconds % 60;
        document.getElementById('expireTimer').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        if (expireSeconds <= 0) {
            clearInterval(expireInterval);
            alert('انتهت صلاحية صفحة التأكيد. تم حفظ تفاصيل حجزك في حجوزاتك.');
            window.location.href = '../user/bookings.php';
        }
    }, 1000);
});
</script>

<style>
.qrcode {
    display: inline-block;
    padding: 10px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

@media print {
    .navbar, .footer, .btn, #expireMsg, .qrcode {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    animation: fadeIn 0.5s ease-out;
}س
</style>

<?php include '../includes/footer.php'; ?>