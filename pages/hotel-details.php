<?php
require_once '../config.php';
require_once '../includes/functions.php';

// التحقق من وجود معرف الفندق
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('hotels.php');
}

$hotel_id = intval($_GET['id']);
$page_title = 'تفاصيل الفندق';

// جلب بيانات الفندق
$hotel = getHotelById($hotel_id);
if (!$hotel || $hotel['is_active'] == 0) {
    redirect('hotels.php');
}

// جلب غرف الفندق
$rooms = getHotelRooms($hotel_id);

// جلب التقييمات
$reviews_sql = "SELECT r.*, u.full_name, u.profile_image 
                FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.hotel_id = ? 
                ORDER BY r.review_date DESC 
                LIMIT 5";
$reviews_stmt = $conn->prepare($reviews_sql);
$reviews_stmt->bind_param("i", $hotel_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
$reviews = [];
while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}

// حساب متوسط التقييم
$avg_rating_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                   FROM reviews WHERE hotel_id = ?";
$avg_stmt = $conn->prepare($avg_rating_sql);
$avg_stmt->bind_param("i", $hotel_id);
$avg_stmt->execute();
$avg_result = $avg_stmt->get_result();
$rating_data = $avg_result->fetch_assoc();
$avg_rating = $rating_data['avg_rating'] ?? 0;
$total_reviews = $rating_data['total_reviews'] ?? 0;
?>

<?php include '../includes/header.php'; ?>

<!-- مسار التنقل -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../index.php">الرئيسية</a></li>
        <li class="breadcrumb-item"><a href="hotels.php">الفنادق</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo $hotel['name']; ?></li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8">
        <!-- معرض صور الفندق -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div id="hotelCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner rounded" style="height: 400px;">
                        <?php
                       
                         $images = [];

                         if (!empty($hotel['images'])) {
                             $decoded = json_decode($hotel['images'], true);
                                 if (is_array($decoded)) {
                                      $images = $decoded;
                                     }
                                     }

                                 // إذا ما في صور → نحط صورة افتراضية
                                     if (empty($images)) {
                                        $images = ['default.jpg'];
                                         }
                         ?>          

                                     <?php foreach ($images as $index => $image): ?>

                            <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                                <img src="<?php echo HOTEL_IMG_PATH . $image; ?>" 
                                     class="d-block w-100 h-100" 
                                     alt="صورة الفندق <?php echo $index + 1; ?>"
                                     style="object-fit: cover;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#hotelCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">السابق</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#hotelCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">التالي</span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- معلومات الفندق -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h4 class="mb-0">معلومات الفندق</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h3 class="mb-2"><?php echo $hotel['name']; ?></h3>
                        <div class="d-flex align-items-center mb-3">
                            <div class="star-rating me-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?php echo $i <= $hotel['star_rating'] ? '' : '-o'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-muted me-3"><?php echo $hotel['star_rating']; ?> نجوم</span>
                            <span class="text-muted">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo $hotel['city']; ?>، <?php echo $hotel['country']; ?>
                            </span>
                        </div>
                        
                        <p class="card-text"><?php echo nl2br($hotel['description']); ?></p>
                        
                        <!-- المزايا -->
                        <?php if (!empty($hotel['amenities'])): 
                            $amenities = json_decode($hotel['amenities'], true);
                            if (is_array($amenities)): ?>
                                <div class="mt-4">
                                    <h5 class="mb-3">المزايا والخدمات</h5>
                                    <div class="row">
                                        <?php foreach (array_chunk($amenities, 2) as $chunk): ?>
                                            <div class="col-md-6">
                                                <?php foreach ($chunk as $amenity): ?>
                                                    <p class="mb-2">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        <?php echo $amenity; ?>
                                                    </p>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">معلومات الاتصال</h6>
                            </div>
                            <div class="card-body">
                                <p><i class="fas fa-map-marker-alt text-primary"></i> 
                                   <strong>العنوان:</strong><br>
                                   <?php echo nl2br($hotel['address']); ?>
                                </p>
                                
                                <?php if ($hotel['manager_name']): ?>
                                    <p><i class="fas fa-user-tie text-primary"></i> 
                                       <strong>المدير:</strong><br>
                                       <?php echo $hotel['manager_name']; ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" form="bookingForm" class="btn btn-primary w-100 mb-2">
                                      <i class="fas fa-calendar-check"></i> احجز الآن
                                           </button>

                                    </a>
                                    <a href="#" class="btn btn-outline-primary w-100 mb-2">
                                        <i class="fas fa-share-alt"></i> مشاركة
                                    </a>
                                    <?php if (isLoggedIn()): ?>
                                        <button class="btn btn-outline-warning w-100" id="saveFavorite">
                                            <i class="far fa-heart"></i> حفظ في المفضلة
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- التقييمات -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">تقييمات العملاء</h5>
                <span class="badge bg-primary"><?php echo $total_reviews; ?> تقييم</span>
            </div>
            <div class="card-body">
                <?php if ($total_reviews > 0): ?>
                    <!-- متوسط التقييم -->
                    <div class="text-center mb-4">
                        <h1 class="display-4 text-primary"><?php echo number_format($avg_rating, 1); ?></h1>
                        <div class="star-rating mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i <= round($avg_rating) ? '' : '-o'; ?> fa-2x"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-muted">بناءً على <?php echo $total_reviews; ?> تقييم</p>
                    </div>
                    
                    <!-- قائمة التقييمات -->
                    <div class="reviews-list">
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-item border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo PROFILE_IMG_PATH . $review['profile_image']; ?>" 
                                             class="rounded-circle me-2" width="40" height="40">
                                        <div>
                                            <h6 class="mb-0"><?php echo $review['full_name']; ?></h6>
                                            <small class="text-muted">
                                                <?php echo date('Y-m-d', strtotime($review['review_date'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="star-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="mb-0"><?php echo nl2br($review['comment']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($total_reviews > 5): ?>
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-outline-primary">عرض المزيد من التقييمات</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد تقييمات بعد</p>
                        <?php if (isLoggedIn()): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                <i class="fas fa-star"></i> كن أول من يقيّم
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- حجز الفندق -->
        <div class="card shadow-sm sticky-top" style="top: 100px;" id="bookingSection">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> احجز الآن</h5>
            </div>
            <div class="card-body">
                <?php if (empty($rooms)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> لا توجد غرف متاحة حالياً
                    </div>
                <?php else: ?>
                    <form id="bookingForm" action="booking.php" method="GET">
                        <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                        
                        <div class="mb-3">
                            <label for="check_in" class="form-label">تاريخ الوصول</label>
                            <input type="date" class="form-control" id="check_in" name="check_in" 
                                   value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nights" class="form-label">عدد الليالي</label>
                            <select class="form-select" id="nights" name="nights" required>
                                <?php for ($i = 1; $i <= 30; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i == 2 ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> ليلة
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="guests" class="form-label">عدد الضيوف</label>
                            <select class="form-select" id="guests" name="guests" required>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> ضيف</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="room_type" class="form-label">نوع الغرفة</label>
                            <select class="form-select" id="room_type" name="room_type" required>
                                <option value="">اختر نوع الغرفة</option>
                                <?php 
                                $room_types = [];
                                foreach ($rooms as $room) {
                                    if (!in_array($room['room_type'], $room_types)) {
                                        $room_types[] = $room['room_type'];
                                        echo '<option value="' . $room['room_type'] . '">' . $room['room_type'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div id="roomDetails" class="d-none">
                            <div class="alert alert-info">
                                <h6 class="alert-heading" id="selectedRoomType"></h6>
                                <p class="mb-2">السعر: <span id="roomPrice" class="fw-bold"></span> ريال/ليلة</p>
                                <p class="mb-2">السعة: <span id="roomCapacity" class="fw-bold"></span> أشخاص</p>
                                <p class="mb-0">متوفر: <span id="availableRooms" class="fw-bold"></span> غرفة</p>
                            </div>
                            
                            <div class="mb-3">
                                <label for="room_id" class="form-label">اختر رقم الغرفة</label>
                                <select class="form-select" id="room_id" name="room_id" required>
                                    <option value="">اختر غرفة</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <?php if (isLoggedIn()): ?>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-calendar-check"></i> متابعة الحجز
                                </button>
                            <?php else: ?>
                                <a href="../auth/login.php?redirect=<?php echo urlencode('pages/booking.php?hotel_id=' . $hotel_id); ?>" 
                                   class="btn btn-warning btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> سجل الدخول للحجز
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php endif; ?>
                
                <!-- ملاحظات مهمة -->
                <div class="alert alert-light border mt-4">
                    <h6 class="alert-heading"><i class="fas fa-info-circle"></i> ملاحظات مهمة</h6>
                    <ul class="mb-0 small">
                        <li>التأكيد الفوري بعد الدفع</li>
                        <li>إمكانية الإلغاء المجاني قبل 48 ساعة</li>
                        <li>تسجيل الوصول من الساعة 2:00 مساءً</li>
                        <li>تسجيل المغادرة حتى الساعة 12:00 ظهراً</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- فندق مشابه -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-hotel"></i> فنادق مشابهة</h6>
            </div>
            <div class="card-body">
                <?php
                $similar_hotels_sql = "SELECT * FROM hotels 
                                      WHERE city = ? AND id != ? AND is_active = 1 
                                      LIMIT 3";
                $similar_stmt = $conn->prepare($similar_hotels_sql);
                $similar_stmt->bind_param("si", $hotel['city'], $hotel_id);
                $similar_stmt->execute();
                $similar_result = $similar_stmt->get_result();
                
                while ($similar = $similar_result->fetch_assoc()):
                ?>
                    <div class="similar-hotel mb-3">
                        <a href="hotel-details.php?id=<?php echo $similar['id']; ?>" class="text-decoration-none">
                            <div class="d-flex">
                                <img src="<?php echo HOTEL_IMG_PATH . (isset($similar['images']) && !empty($similar['images']) ? json_decode($similar['images'], true)[0] : 'default.jpg'); ?>" 
                                     class="rounded me-3" width="80" height="80" style="object-fit: cover;">
                                <div>
                                    <h6 class="mb-1"><?php echo $similar['name']; ?></h6>
                                    <div class="star-rating small mb-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $similar['star_rating'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo $similar['city']; ?>
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<!-- نافذة التقييم -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">أضف تقييمك</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../user/add-review.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">التقييم</label>
                        <div class="star-rating mb-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star fa-2x star" data-value="<?php echo $i; ?>" 
                                   style="cursor: pointer; color: #ddd;"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="ratingValue" value="5">
                    </div>
                    
                    <div class="mb-3">
                        <label for="comment" class="form-label">التعليق</label>
                        <textarea class="form-control" id="comment" name="comment" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إرسال التقييم</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// عرض تفاصيل الغرفة عند اختيار النوع
document.getElementById('room_type').addEventListener('change', function() {
    const roomType = this.value;
    const roomDetails = document.getElementById('roomDetails');
    
    if (roomType) {
        // جلب بيانات الغرف عبر AJAX مع الرأس X-Requested-With
        fetch(`../api/get-rooms.php?hotel_id=<?php echo $hotel_id; ?>&room_type=${roomType}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.rooms.length > 0) {
                const room = data.rooms[0];
                
                document.getElementById('selectedRoomType').textContent = room.room_type;
                document.getElementById('roomPrice').textContent = room.price_per_night;
                document.getElementById('roomCapacity').textContent = room.capacity;
                document.getElementById('availableRooms').textContent = room.available_rooms;
                
                // ملء اختيار الغرف
                const roomSelect = document.getElementById('room_id');
                roomSelect.innerHTML = '<option value="">اختر غرفة</option>';
                data.rooms.forEach(r => {
                    const option = document.createElement('option');
                    option.value = r.id;
                    option.textContent = `غرفة ${r.room_number || r.id} - ${r.price_per_night} ريال`;
                    roomSelect.appendChild(option);
                });
                
                roomDetails.classList.remove('d-none');
            } else {
                roomDetails.classList.add('d-none');
                alert('لا توجد غرف متاحة من هذا النوع');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            roomDetails.classList.add('d-none');
        });
    } else {
        roomDetails.classList.add('d-none');
    }
});

// تقييم النجوم
document.querySelectorAll('.star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = parseInt(this.getAttribute('data-value'));
        document.getElementById('ratingValue').value = rating;
        
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

// حاسبة السعر
document.getElementById('nights').addEventListener('change', updatePrice);
document.getElementById('room_type').addEventListener('change', updatePrice);

function updatePrice() {
    const nights = parseInt(document.getElementById('nights').value);
    const roomPriceElement = document.getElementById('roomPrice');
    
    if (roomPriceElement && roomPriceElement.textContent) {
        const roomPrice = parseFloat(roomPriceElement.textContent);
        const totalPrice = nights * roomPrice;
        
        // يمكنك عرض السعر الإجمالي في مكان ما
        console.log('السعر الإجمالي:', totalPrice);
    }
}

// تحميل الغرف عند فتح الصفحة
document.addEventListener('DOMContentLoaded', function() {
    const roomTypeSelect = document.getElementById('room_type');
    if (roomTypeSelect.value) {
        roomTypeSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<?php include '../includes/footer.php'; ?>