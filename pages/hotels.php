<?php
require_once '../config.php';
require_once '../includes/functions.php';

$page_title = 'الفنادق';

// معلمات البحث
$city = $_GET['city'] ?? '';
$check_in = $_GET['check_in'] ?? date('Y-m-d', strtotime('+1 day'));
$check_out = $_GET['check_out'] ?? date('Y-m-d', strtotime('+3 days'));
$guests = $_GET['guests'] ?? 2;
$nights = $_GET['nights'] ?? 2;
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 10000;
$rating = $_GET['rating'] ?? 0;

// جلب الفنادق مع التصفية
$sql = "SELECT h.*, u.full_name as manager_name, 
               MIN(r.price_per_night) as min_price,
               MAX(r.price_per_night) as max_price
        FROM hotels h 
        LEFT JOIN users u ON h.manager_id = u.id 
        LEFT JOIN rooms r ON h.id = r.hotel_id 
        WHERE h.is_active = 1";

if (!empty($city)) {
    $sql .= " AND h.city LIKE '%" . $conn->real_escape_string($city) . "%'";
}

$sql .= " GROUP BY h.id HAVING 1=1";

if ($min_price > 0) {
    $sql .= " AND min_price >= " . intval($min_price);
}

if ($max_price > 0) {
    $sql .= " AND max_price <= " . intval($max_price);
}

if ($rating > 0) {
    $sql .= " AND h.star_rating >= " . intval($rating);
}

$sql .= " ORDER BY h.star_rating DESC, h.created_at DESC";

$result = $conn->query($sql);
$hotels = [];
while ($row = $result->fetch_assoc()) {
    $hotels[] = $row;
}

// جلب المدن الفريدة للتكملة التلقائية
$cities_sql = "SELECT DISTINCT city FROM hotels WHERE is_active = 1 ORDER BY city";
$cities_result = $conn->query($cities_sql);
$cities = [];
while ($row = $cities_result->fetch_assoc()) {
    $cities[] = $row['city'];
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- نموذج البحث المتقدم -->
<div class="card shadow-lg mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-search"></i> بحث متقدم</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
                <label for="city" class="form-label">المدينة</label>
                <input type="text" class="form-control" id="city" name="city" 
                       list="citiesList" value="<?php echo htmlspecialchars($city); ?>" 
                       placeholder="ابحث عن مدينة">
                <datalist id="citiesList">
                    <?php foreach ($cities as $city_name): ?>
                        <option value="<?php echo htmlspecialchars($city_name); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            
            <div class="col-md-2">
                <label for="check_in" class="form-label">تاريخ الوصول</label>
                <input type="date" class="form-control" id="check_in" name="check_in" 
                       value="<?php echo $check_in; ?>" min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="col-md-2">
                <label for="nights" class="form-label">عدد الليالي</label>
                <select class="form-select" id="nights" name="nights">
                    <?php for ($i = 1; $i <= 14; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $nights == $i ? 'selected' : ''; ?>>
                            <?php echo $i; ?> ليلة
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="guests" class="form-label">عدد الضيوف</label>
                <select class="form-select" id="guests" name="guests">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $guests == $i ? 'selected' : ''; ?>>
                            <?php echo $i; ?> ضيف
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="rating" class="form-label">التقييم</label>
                <select class="form-select" id="rating" name="rating">
                    <option value="0" <?php echo $rating == 0 ? 'selected' : ''; ?>>جميع التقييمات</option>
                    <option value="5" <?php echo $rating == 5 ? 'selected' : ''; ?>>5 نجوم</option>
                    <option value="4" <?php echo $rating == 4 ? 'selected' : ''; ?>>4 نجوم فما فوق</option>
                    <option value="3" <?php echo $rating == 3 ? 'selected' : ''; ?>>3 نجوم فما فوق</option>
                    <option value="2" <?php echo $rating == 2 ? 'selected' : ''; ?>>2 نجوم فما فوق</option>
                    <option value="1" <?php echo $rating == 1 ? 'selected' : ''; ?>>1 نجمة فما فوق</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="min_price" class="form-label">الحد الأدنى للسعر (ريال)</label>
                <input type="number" class="form-control" id="min_price" name="min_price" 
                       value="<?php echo $min_price; ?>" min="0" step="50">
            </div>
            
            <div class="col-md-3">
                <label for="max_price" class="form-label">الحد الأقصى للسعر (ريال)</label>
                <input type="number" class="form-control" id="max_price" name="max_price" 
                       value="<?php echo $max_price; ?>" min="0" step="50">
            </div>
            
            <div class="col-md-6 d-flex align-items-end">
                <div class="d-grid w-100">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> بحث
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- عرض النتائج -->
<div class="row">
    <div class="col-md-3">
        <!-- الفلاتر الجانبية -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-filter"></i> تصفية النتائج</h6>
            </div>
            <div class="card-body">
                <!-- التصنيف حسب التقييم -->
                <h6 class="mt-3 mb-3">التقييم</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="rating5" name="rating[]" value="5">
                    <label class="form-check-label" for="rating5">
                        <span class="star-rating">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </span>
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="rating4" name="rating[]" value="4">
                    <label class="form-check-label" for="rating4">
                        <span class="star-rating">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </span>
                    </label>
                </div>
                
                <!-- نطاق السعر -->
                <h6 class="mt-4 mb-3">نطاق السعر (ليلة)</h6>
                <div class="range-slider">
                    <input type="range" class="form-range" min="0" max="5000" step="50" 
                           id="priceRangeMin" value="<?php echo $min_price; ?>">
                    <input type="range" class="form-range" min="0" max="5000" step="50" 
                           id="priceRangeMax" value="<?php echo $max_price; ?>">
                    <div class="d-flex justify-content-between mt-2">
                        <span id="priceMinValue"><?php echo $min_price; ?> ريال</span>
                        <span id="priceMaxValue"><?php echo $max_price; ?> ريال</span>
                    </div>
                </div>
                
                <!-- المزايا -->
                <h6 class="mt-4 mb-3">المزايا</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="wifi">
                    <label class="form-check-label" for="wifi">
                        <i class="fas fa-wifi"></i> واي فاي مجاني
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="parking">
                    <label class="form-check-label" for="parking">
                        <i class="fas fa-parking"></i> مواقف مجانية
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="pool">
                    <label class="form-check-label" for="pool">
                        <i class="fas fa-swimming-pool"></i> مسبح
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="spa">
                    <label class="form-check-label" for="spa">
                        <i class="fas fa-spa"></i> سبا
                    </label>
                </div>
                
                <button class="btn btn-outline-primary w-100 mt-4" onclick="applyFilters()">
                    <i class="fas fa-check"></i> تطبيق الفلاتر
                </button>
            </div>
               </div>
        
        <!-- إعلان جانبي -->
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-primary">خصم 25%</h6>
                <p class="small text-muted">على الحجوزات الأولى</p>
                <a href="#" class="btn btn-warning btn-sm">احصل على الخصم</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <!-- رأس النتائج -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">نتائج البحث</h4>
                <p class="text-muted mb-0">عرض <?php echo count($hotels); ?> فندق</p>
            </div>
            <div>
                <select class="form-select" onchange="sortHotels(this.value)">
                    <option value="rating">الترتيب حسب التقييم</option>
                    <option value="price_low">السعر: من الأقل إلى الأعلى</option>
                    <option value="price_high">السعر: من الأعلى إلى الأقل</option>
                    <option value="name">الاسم: أبجدي</option>
                </select>
            </div>
        </div>
        
        <!-- عرض الفنادق -->
        <?php if (empty($hotels)): ?>
            <div class="text-center py-5">
                <i class="fas fa-hotel fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">لا توجد فنادق متطابقة مع بحثك</h4>
                <p class="text-muted mb-4">حاول تغيير معايير البحث أو إزالة بعض الفلاتر</p>
                <a href="hotels.php" class="btn btn-primary">
                    <i class="fas fa-redo"></i> عرض جميع الفنادق
                </a>
            </div>
        <?php else: ?>
            <div class="row" id="hotelsList">
                <?php foreach ($hotels as $hotel): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card hotel-card h-100">
                            <div class="row g-0">
                                <div class="col-md-5">
                                    <div class="position-relative">
                                        <img src="<?php echo HOTEL_IMG_PATH . (isset($hotel['images']) && !empty($hotel['images']) ? json_decode($hotel['images'], true)[0] : 'default.jpg'); ?>" 
                                             class="card-img-top h-100" alt="<?php echo $hotel['name']; ?>" 
                                             style="height: 200px; object-fit: cover;">
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-success">
                                                <?php echo $hotel['min_price']; ?> ريال/ليلة
                                            </span>
                                        </div>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-warning">
                                                <?php echo $hotel['star_rating']; ?> <i class="fas fa-star"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="card-body d-flex flex-column h-100">
                                        <h5 class="card-title"><?php echo $hotel['name']; ?></h5>
                                        <p class="card-text text-muted">
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?php echo $hotel['city']; ?>، <?php echo $hotel['country']; ?>
                                        </p>
                                        <p class="card-text small">
                                            <?php echo strlen($hotel['description']) > 80 ? substr($hotel['description'], 0, 80) . '...' : $hotel['description']; ?>
                                        </p>
                                        
                                        <?php if (!empty($hotel['amenities'])): 
                                            $amenities = json_decode($hotel['amenities'], true);
                                            if (is_array($amenities)): ?>
                                                <div class="mt-2 mb-3">
                                                    <?php foreach (array_slice($amenities, 0, 3) as $amenity): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-check-circle text-success"></i> 
                                                            <?php echo $amenity; ?>
                                                        </small><br>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <div class="mt-auto d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="text-primary mb-0">
                                                    <?php echo $hotel['min_price']; ?> ريال
                                                </h5>
                                                <small class="text-muted">لكل ليلة</small>
                                            </div>
                                            <div class="btn-group">
                                                <a href="hotel-details.php?id=<?php echo $hotel['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-info-circle"></i> التفاصيل
                                                </a>
                                                <?php if (isLoggedIn()): ?>
                                                    <a href="booking.php?hotel_id=<?php echo $hotel['id']; ?>" 
                                                       class="btn btn-sm btn-success">
                                                        <i class="fas fa-calendar-check"></i> احجز الآن
                                                    </a>
                                                <?php else: ?>
                                                    <a href="../auth/login.php?redirect=<?php echo urlencode('pages/booking.php?hotel_id=' . $hotel['id']); ?>" 
                                                       class="btn btn-sm btn-warning">
                                                        <i class="fas fa-sign-in-alt"></i> سجل للحجز
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- ترقيم الصفحات -->
            <nav aria-label="ترقيم الصفحات" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">السابق</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">التالي</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
        
        <!-- خريطة توضح مواقع الفنادق -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-map-marked-alt"></i> خريطة الفنادق</h6>
            </div>
            <div class="card-body">
                <div id="hotelMap" style="height: 300px; background-color: #f0f0f0;" class="rounded">
                    <div class="text-center py-5">
                        <i class="fas fa-map fa-3x text-muted mb-3"></i>
                        <p class="text-muted">خريطة تفاعلية لعرض مواقع الفنادق</p>
                        <small class="text-muted">(تتطلب مفتاح API من Google Maps)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// تطبيق الفلاتر
function applyFilters() {
    const city = document.getElementById('city').value;
    const checkIn = document.getElementById('check_in').value;
    const nights = document.getElementById('nights').value;
    const guests = document.getElementById('guests').value;
    const rating = document.getElementById('rating').value;
    const minPrice = document.getElementById('priceRangeMin').value;
    const maxPrice = document.getElementById('priceRangeMax').value;
    
    // بناء رابط البحث
    let url = 'hotels.php?';
    if (city) url += 'city=' + encodeURIComponent(city) + '&';
    if (checkIn) url += 'check_in=' + checkIn + '&';
    if (nights) url += 'nights=' + nights + '&';
    if (guests) url += 'guests=' + guests + '&';
    if (rating) url += 'rating=' + rating + '&';
    if (minPrice) url += 'min_price=' + minPrice + '&';
    if (maxPrice) url += 'max_price=' + maxPrice + '&';
    
    // إزالة علامة & الأخيرة إذا موجودة
    if (url.endsWith('&')) {
        url = url.slice(0, -1);
    }
    
    window.location.href = url;
}

// تحديث عرض نطاق السعر
document.addEventListener('DOMContentLoaded', function() {
    const priceMin = document.getElementById('priceRangeMin');
    const priceMax = document.getElementById('priceRangeMax');
    const priceMinValue = document.getElementById('priceMinValue');
    const priceMaxValue = document.getElementById('priceMaxValue');
    
    if (priceMin && priceMax && priceMinValue && priceMaxValue) {
        priceMin.addEventListener('input', function() {
            priceMinValue.textContent = this.value + ' ريال';
        });
        
        priceMax.addEventListener('input', function() {
            priceMaxValue.textContent = this.value + ' ريال';
        });
    }
});

// ترتيب الفنادق
function sortHotels(sortBy) {
    const hotelsList = document.getElementById('hotelsList');
    const hotels = Array.from(hotelsList.getElementsByClassName('col-lg-6'));
    
    hotels.sort(function(a, b) {
        const hotelA = a.querySelector('.card-title').textContent;
        const hotelB = b.querySelector('.card-title').textContent;
        const priceA = parseFloat(a.querySelector('.text-primary').textContent);
        const priceB = parseFloat(b.querySelector('.text-primary').textContent);
        const ratingA = parseFloat(a.querySelector('.badge.bg-warning').textContent);
        const ratingB = parseFloat(b.querySelector('.badge.bg-warning').textContent);
        
        switch(sortBy) {
            case 'name':
                return hotelA.localeCompare(hotelB);
            case 'price_low':
                return priceA - priceB;
            case 'price_high':
                return priceB - priceA;
            case 'rating':
                return ratingB - ratingA;
            default:
                return 0;
        }
    });
    
    // إعادة ترتيب العناصر
    hotels.forEach(function(hotel) {
        hotelsList.appendChild(hotel);
    });
}

// البحث التلقائي للمدن
document.addEventListener('DOMContentLoaded', function() {
    const cityInput = document.getElementById('city');
    if (cityInput) {
        cityInput.addEventListener('input', function() {
            // هنا يمكن إضافة كود للبحث التلقائي إذا كان لديك API
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>