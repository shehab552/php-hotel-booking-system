// JavaScript لنظام حجز الفنادق

// تهيئة الموقع عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // تهيئة التواريخ
    initDatePickers();
    
    // تهيئة حقل البحث
    initSearch();
    
    // إضافة زر العودة للأعلى
    addBackToTopButton();
    
    // تفعيل الحقول ذات الإدخال التلقائي
    initAutocomplete();
    
    // معالجة النماذج
    initForms();
    
    // تهيئة الرسوم البيانية إذا كانت موجودة
    initCharts();
});

// تهيئة انتقاء التواريخ
function initDatePickers() {
    const today = new Date().toISOString().split('T')[0];
    const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
    
    // تعيين تاريخ الوصول الافتراضي
    const checkInInput = document.getElementById('check_in');
    if (checkInInput && !checkInInput.value) {
        checkInInput.value = tomorrow;
        checkInInput.min = today;
    }
    
    // تعيين تاريخ المغادرة الافتراضي
    const checkOutInput = document.getElementById('check_out');
    if (checkOutInput && !checkOutInput.value) {
        const threeDaysLater = new Date(Date.now() + 3 * 86400000).toISOString().split('T')[0];
        checkOutInput.value = threeDaysLater;
        checkOutInput.min = tomorrow;
    }
}

// تهيئة حقل البحث
function initSearch() {
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    }
}

// إضافة زر العودة للأعلى
function addBackToTopButton() {
    const button = document.createElement('a');
    button.href = '#';
    button.className = 'back-to-top';
    button.innerHTML = '<i class="fas fa-arrow-up"></i>';
    button.title = 'العودة للأعلى';
    document.body.appendChild(button);
    
    // إظهار/إخفاء الزر عند التمرير
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            button.classList.add('show');
        } else {
            button.classList.remove('show');
        }
    });
    
    // التمرير للأعلى عند النقر
    button.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// تهيئة الإدخال التلقائي للمدن
function initAutocomplete() {
    const cityInput = document.getElementById('city');
    if (cityInput) {
        // هنا يمكن إضافة كود لجلب المدن من API
        cityInput.addEventListener('input', function() {
            const query = this.value;
            if (query.length > 2) {
                fetchCities(query);
            }
        });
    }
}

// جلب المدن (مثال)
function fetchCities(query) {
    // هذه مجرد مثال، يمكن استبدالها بـ API حقيقي
    const cities = ['الرياض', 'جدة', 'الدمام', 'مكة', 'المدينة', 'الخبر', 'الطائف'];
    const filtered = cities.filter(city => 
        city.includes(query) || city.startsWith(query)
    );
    
    // عرض الاقتراحات
    showSuggestions(filtered);
}

// عرض الاقتراحات
function showSuggestions(suggestions) {
    const datalist = document.getElementById('citiesList');
    if (datalist) {
        datalist.innerHTML = '';
        suggestions.forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            datalist.appendChild(option);
        });
    }
}

// تهيئة النماذج
function initForms() {
    // التحقق من صحة النماذج
    const forms = document.querySelectorAll('form[novalidate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    });
    
    // التحقق من كلمات المرور
    const passwordForms = document.querySelectorAll('form[data-password-check]');
    passwordForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const password = this.querySelector('input[name="password"]');
            const confirm = this.querySelector('input[name="confirm_password"]');
            
            if (password && confirm && password.value !== confirm.value) {
                e.preventDefault();
                alert('كلمات المرور غير متطابقة!');
                confirm.focus();
            }
        });
    });
}

// تهيئة الرسوم البيانية
function initCharts() {
    if (typeof Chart !== 'undefined') {
        // يمكن إضافة تهيئة الرسوم البيانية هنا
    }
}

// دالة عرض/إخفاء كلمة المرور
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// دالة تأكيد الإجراءات
function confirmAction(message = 'هل أنت متأكد من تنفيذ هذا الإجراء؟') {
    return confirm(message);
}

// دالة إظهار رسالة نجاح
function showSuccess(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.container').prepend(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// دالة إظهار رسالة خطأ
function showError(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.container').prepend(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// دالة تحميل البيانات عبر AJAX
function loadData(url, callback) {
    fetch(url)
        .then(response => response.json())
        .then(data => callback(data))
        .catch(error => {
            console.error('Error:', error);
            showError('حدث خطأ أثناء تحميل البيانات');
        });
}

// دالة تحديث كمية العناصر
function updateQuantity(itemId, change) {
    const input = document.getElementById(`quantity-${itemId}`);
    let value = parseInt(input.value) + change;
    
    if (value < 1) value = 1;
    if (value > 10) value = 10;
    
    input.value = value;
    
    // تحديث السعر الإجمالي
    updateTotalPrice();
}

// دالة تحديث السعر الإجمالي
function updateTotalPrice() {
    let total = 0;
    
    document.querySelectorAll('.item-price').forEach(item => {
        const price = parseFloat(item.dataset.price);
        const quantity = parseInt(item.closest('.item').querySelector('.quantity-input').value);
        total += price * quantity;
    });
    
    document.getElementById('total-price').textContent = total.toFixed(2);
}

// دالة إضافة إلى السلة (في حالة التوسع لمتجر)
function addToCart(productId, quantity = 1) {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({ id: productId, quantity: quantity });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    showSuccess('تمت الإضافة إلى السلة');
}

// دالة تحديث عداد السلة
function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    const counter = document.getElementById('cart-count');
    if (counter) {
        counter.textContent = count;
        counter.style.display = count > 0 ? 'inline' : 'none';
    }
}

// دالة البحث في الوقت الفعلي
function liveSearch(inputSelector, itemsSelector) {
    const input = document.querySelector(inputSelector);
    const items = document.querySelectorAll(itemsSelector);
    
    if (input) {
        input.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
}

// دالة تحميل المزيد من المحتوى (للاستدعاء اللانهائي)
function loadMore(containerSelector, loadUrl, page = 1) {
    const container = document.querySelector(containerSelector);
    const button = document.querySelector('.load-more-btn');
    
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحميل...';
        
        fetch(`${loadUrl}&page=${page}`)
            .then(response => response.text())
            .then(html => {
                container.insertAdjacentHTML('beforeend', html);
                button.disabled = false;
                button.innerHTML = 'تحميل المزيد';
                
                // زيادة رقم الصفحة
                button.dataset.page = parseInt(button.dataset.page || 1) + 1;
            })
            .catch(error => {
                console.error('Error:', error);
                button.innerHTML = 'حدث خطأ، حاول مرة أخرى';
            });
    }
}

// دالة مشاركة الصفحة
function sharePage(title, url) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        // نسخ الرابط
        navigator.clipboard.writeText(url)
            .then(() => showSuccess('تم نسخ الرابط إلى الحافظة'))
            .catch(() => showError('حدث خطأ أثناء نسخ الرابط'));
    }
}

// تهيئة كافة العناصر عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // تحديث عداد السلة
    updateCartCount();
    
    // تفعيل البحث المباشر إذا كان موجوداً
    liveSearch('#live-search-input', '.live-search-item');
    
    // تهيئة أزرار "تحميل المزيد"
    document.querySelectorAll('.load-more-btn').forEach(button => {
        button.addEventListener('click', function() {
            const container = this.dataset.container;
            const url = this.dataset.url;
            const page = this.dataset.page || 1;
            loadMore(container, url, page);
        });
    });
    
    // تهيئة أزرار المشاركة
    document.querySelectorAll('.share-btn').forEach(button => {
        button.addEventListener('click', function() {
            const title = this.dataset.title || document.title;
            const url = this.dataset.url || window.location.href;
            sharePage(title, url);
        });
    });
    
    // تهيئة المنبثقات (Tooltips)
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // تهيئة المنبثقات المنبثقة (Popovers)
    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
    popovers.forEach(popover => {
        new bootstrap.Popover(popover);
    });
});