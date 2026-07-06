<?php
require_once __DIR__ . '../config.php';
require_once __DIR__ . '../includes/functions.php';

$page_title = 'الرئيسية';

// جلب الفنادق المميزة
$hotels = getHotels(6);
?>

<?php include __DIR__ . '../includes/header.php'; ?>

<!-- قسم البطل -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 mb-4">ابحث عن فندقك المثالي</h1>
        <p class="lead mb-5">أفضل الفنادق بأسعار تنافسية وخدمات متميزة</p>
        
        <!-- نموذج البحث -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <form action="pages/hotels.php" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="city" class="form-label">المدينة</label>
                                <input type="text" class="form-control" id="city" name="city" placeholder="أدخل اسم المدينة">
                            </div>
                            <div class="col-md-3">
                                <label for="check_in" class="form-label">تاريخ الوصول</label>
                                <input type="date" class="form-control" id="check_in" name="check_in" min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="nights" class="form-label">عدد الليالي</label>
                                <select class="form-select" id="nights" name="nights">
                                    <option value="1">1 ليلة</option>
                                    <option value="2">2 ليالي</option>
                                    <option value="3">3 ليالي</option>
                                    <option value="7">أسبوع</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="guests" class="form-label">عدد الضيوف</label>
                                <select class="form-select" id="guests" name="guests">
                                    <option value="1">1 ضيف</option>
                                    <option value="2">2 ضيوف</option>
                                    <option value="3">3 ضيوف</option>
                                    <option value="4">4 ضيوف</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-search"></i> بحث
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- عرض الفنادق المميزة -->
<section class="featured-hotels mb-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="mb-3">فنادق مميزة</h2>
                <p class="text-muted">أفضل الفنادق التي نرشحها لك</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="pages/hotels.php" class="btn btn-primary">عرض جميع الفنادق</a>
            </div>
        </div>
        
        <div class="row">
            <?php if (empty($hotels)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"> لا توجد فنادق متاحة حالياً</i>
                                        </div>
            <?php else: ?>
                <?php foreach ($hotels as $hotel): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card hotel-card h-100">
                            <img src="<?php echo HOTEL_IMG_PATH . ($hotel['images'] ? json_decode($hotel['images'], true)[0] : 'default.jpg'); ?>" 
                                 class="card-img-top" alt="<?php echo $hotel['name']; ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?php echo $hotel['name']; ?></h5>
                                    <div class="star-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $hotel['star_rating'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="card-text text-muted">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo $hotel['city']; ?>، <?php echo $hotel['country']; ?>
                                </p>
                                <p class="card-text"><?php echo substr($hotel['description'], 0, 100) . '...'; ?></p>
                                
                                <?php if (!empty($hotel['amenities'])): 
                                    $amenities = json_decode($hotel['amenities'], true);
                                    if (is_array($amenities)): ?>
                                        <div class="mb-3">
                                            <?php foreach (array_slice($amenities, 0, 3) as $amenity): ?>
                                                <span class="amenity-badge d-inline-block"><?php echo $amenity; ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="pages/hotel-details.php?id=<?php echo $hotel['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> عرض التفاصيل
                                    </a>
                                    <?php if (isLoggedIn()): ?>
                                        <a href="pages/booking.php?hotel_id=<?php echo $hotel['id']; ?>" class="btn btn-success">
                                            <i class="fas fa-calendar-check"></i> احجز الآن
                                        </a>
                                    <?php else: ?>
                                        <a href="auth/login.php" class="btn btn-warning">
                                            <i class="fas fa-sign-in-alt"></i> سجل الدخول للحجز
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- إحصائيات -->
<section class="statistics mb-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-hotel fa-3x text-primary mb-3"></i>
                        <h3 class="count" data-count="250">0</h3>
                        <p class="text-muted">فندق</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x text-success mb-3"></i>
                        <h3 class="count" data-count="50000">0</h3>
                        <p class="text-muted">عميل سعيد</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-calendar-check fa-3x text-warning mb-3"></i>
                        <h3 class="count" data-count="10000">0</h3>
                        <p class="text-muted">حجز مكتمل</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-globe fa-3x text-info mb-3"></i>
                        <h3 class="count" data-count="50">0</h3>
                        <p class="text-muted">مدينة</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- لماذا تختارنا -->
<section class="why-us mb-5">
    <div class="container">
        <h2 class="text-center mb-5">لماذا تختار نظامنا؟</h2>
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div class="p-4">
                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                    <h4>آمن وسهل</h4>
                    <p class="text-muted">نظام حجز آمن وسهل الاستخدام مع دعم متعدد اللغات</p>
                </div>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="p-4">
                    <i class="fas fa-handshake fa-3x text-success mb-3"></i>
                    <h4>خدمة عملاء متميزة</h4>
                    <p class="text-muted">فريق خدمة عملاء متاح 24/7 لمساعدتك في أي استفسار</p>
                </div>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="p-4">
                    <i class="fas fa-percentage fa-3x text-warning mb-3"></i>
                    <h4>أفضل الأسعار</h4>
                    <p class="text-muted">نضمن لك أفضل الأسعار مع خصومات حصرية للمستخدمين</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '../includes/footer.php'; ?>

<script>
// كود لعدادات الإحصائيات
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.count');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-count'));
        const increment = target / 200;
        let current = 0;
        
        const updateCounter = () => {
            if (current < target) {
                current += increment;
                counter.innerText = Math.ceil(current);
                setTimeout(updateCounter, 10);
            } else {
                counter.innerText = target;
            }
        };
        
        updateCounter();
    });
});
</script>