<?php
require_once '../config.php';
require_once '../includes/functions.php';

$page_title = 'تسجيل جديد';

$_SESSION['override'] = true;

// إذا كان المستخدم مسجل دخول بالفعل
if (isLoggedIn() && empty($_SESSION['override'])) {
    redirect('user/dashboard.php');
}


// معالجة التسجيل
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_data = [
        'username' => $_POST['username'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'full_name' => $_POST['full_name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'birth_date' => $_POST['birth_date'] ?? ''
    ];
    
    if (registerUser($user_data)) {
        // بعد التسجيل الناجح، توجيه إلى صفحة تسجيل الدخول
        $_SESSION['success_message'] = 'تم التسجيل بنجاح، يمكنك الآن تسجيل الدخول';
        redirect('auth/login.php');
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card shadow-lg">
            <div class="card-header bg-success text-white text-center py-3">
                <h4 class="mb-0"><i class="fas fa-user-plus"></i> إنشاء حساب جديد</h4>
            </div>
            <div class="card-body p-4">
                <?php displayMessages(); ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">
                                <i class="fas fa-user"></i> الاسم الكامل *
                            </label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   required placeholder="أدخل الاسم الكامل">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">
                                <i class="fas fa-user-circle"></i> اسم المستخدم *
                            </label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   required placeholder="أدخل اسم المستخدم">
                            <small class="text-muted">يجب أن يكون اسم المستخدم فريداً</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> البريد الإلكتروني *
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   required placeholder="example@domain.com">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone"></i> رقم الهاتف
                            </label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   placeholder="05XXXXXXXX">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> كلمة المرور *
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" 
                                       required placeholder="أدخل كلمة المرور (6 أحرف على الأقل)">
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock"></i> تأكيد كلمة المرور *
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required placeholder="أعد إدخال كلمة المرور">
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="birth_date" class="form-label">
                                <i class="fas fa-birthday-cake"></i> تاريخ الميلاد
                            </label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date" 
                                   max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> العنوان
                            </label>
                            <textarea class="form-control" id="address" name="address" 
                                      rows="2" placeholder="أدخل العنوان"></textarea>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            أوافق على <a href="#" class="text-primary">شروط الاستخدام</a> و <a href="#" class="text-primary">سياسة الخصوصية</a>
                        </label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-user-plus"></i> إنشاء الحساب
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-2">لديك حساب بالفعل؟</p>
                    <a href="login.php" class="btn btn-outline-primary">
                        <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>