<?php
require_once '../config.php';
require_once '../includes/functions.php';

// التحقق من صلاحيات المدير
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$page_title = 'إعدادات النظام';

// معالجة حفظ الإعدادات
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // إعدادات الموقع
    if (isset($_POST['update_site_settings'])) {
        // هنا يمكن حفظ الإعدادات في قاعدة بيانات أو ملف
        $success_messages[] = 'تم تحديث إعدادات الموقع بنجاح';
    }
    
    // إعدادات البريد
    if (isset($_POST['update_email_settings'])) {
        $success_messages[] = 'تم تحديث إعدادات البريد بنجاح';
    }
    
    // إعدادات الدفع
    if (isset($_POST['update_payment_settings'])) {
        $success_messages[] = 'تم تحديث إعدادات الدفع بنجاح';
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-md-3">
        <?php include 'admin-sidebar.php'; ?>
    </div>
    
    <div class="col-md-9">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-cog"></i> إعدادات النظام</h5>
            </div>
            <div class="card-body">
                <?php displayMessages(); ?>
                
                <!-- تباديل الإعدادات -->
                <ul class="nav nav-tabs mb-4" id="settingsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="site-tab" data-bs-toggle="tab" data-bs-target="#site" type="button">
                            <i class="fas fa-globe"></i> إعدادات الموقع
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button">
                            <i class="fas fa-envelope"></i> إعدادات البريد
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button">
                            <i class="fas fa-credit-card"></i> إعدادات الدفع
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button">
                            <i class="fas fa-server"></i> إعدادات النظام
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="settingsTabContent">
                    <!-- إعدادات الموقع -->
                    <div class="tab-pane fade show active" id="site" role="tabpanel">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="site_name" class="form-label">اسم الموقع *</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" 
                                           value="<?php echo SITE_NAME; ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="site_url" class="form-label">رابط الموقع *</label>
                                    <input type="url" class="form-control" id="site_url" name="site_url" 
                                           value="<?php echo SITE_URL; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="admin_email" class="form-label">بريد المدير *</label>
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                           value="admin@hotel.com" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="support_email" class="form-label">بريد الدعم</label>
                                    <input type="email" class="form-control" id="support_email" name="support_email" 
                                           value="support@hotel.com">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">رقم الهاتف</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="00966123456789">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">العنوان</label>
                                    <input type="text" class="form-control" id="address" name="address" 
                                           value="الرياض، المملكة العربية السعودية">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">وصف الموقع</label>
                                <textarea class="form-control" id="description" name="description" rows="3">نظام حجز الفنادق الأفضل في المملكة العربية السعودية</textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="keywords" class="form-label">الكلمات المفتاحية</label>
                                <input type="text" class="form-control" id="keywords" name="keywords" 
                                       value="حجز فنادق, فنادق السعودية, حجز غرف">
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="maintenance" name="maintenance">
                                <label class="form-check-label" for="maintenance">
                                    وضع الصيانة
                                </label>
                            </div>
                            
                            <button type="submit" name="update_site_settings" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ إعدادات الموقع
                            </button>
                        </form>
                    </div>
                    
                    <!-- إعدادات البريد -->
                    <div class="tab-pane fade" id="email" role="tabpanel">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_host" class="form-label">خادم SMTP</label>
                                    <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                           value="smtp.gmail.com">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_port" class="form-label">منفذ SMTP</label>
                                    <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                           value="587">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_username" class="form-label">اسم مستخدم SMTP</label>
                                    <input type="text" class="form-control" id="smtp_username" name="smtp_username">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_password" class="form-label">كلمة مرور SMTP</label>
                                    <input type="password" class="form-control" id="smtp_password" name="smtp_password">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_encryption" class="form-label">التشفير</label>
                                    <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                        <option value="tls">TLS</option>
                                        <option value="ssl">SSL</option>
                                        <option value="none">بدون تشفير</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="from_email" class="form-label">البريد المرسل</label>
                                    <input type="email" class="form-control" id="from_email" name="from_email" 
                                           value="noreply@hotel.com">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email_test" class="form-label">اختبار إرسال بريد</label>
                                <input type="email" class="form-control" id="email_test" name="email_test" 
                                       placeholder="أدخل بريدك للاختبار">
                                <small class="text-muted">أرسل بريد تجريبي للتحقق من الإعدادات</small>
                            </div>
                            
                            <div class="btn-group" role="group">
                                <button type="submit" name="update_email_settings" class="btn btn-primary">
                                    <i class="fas fa-save"></i> حفظ إعدادات البريد
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="testEmail()">
                                    <i class="fas fa-paper-plane"></i> اختبار الإرسال
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- إعدادات الدفع -->
                    <div class="tab-pane fade" id="payment" role="tabpanel">
                        <form method="POST" action="">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                إعدادات بوابات الدفع الإلكتروني
                            </div>
                            
                            <!-- PayPal -->
                            <h6 class="mt-4 mb-3"><i class="fab fa-paypal"></i> إعدادات PayPal</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="paypal_client_id" class="form-label">معرف العميل</label>
                                    <input type="text" class="form-control" id="paypal_client_id" name="paypal_client_id">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="paypal_secret" class="form-label">المفتاح السري</label>
                                    <input type="password" class="form-control" id="paypal_secret" name="paypal_secret">
                                </div>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="paypal_sandbox" name="paypal_sandbox">
                                <label class="form-check-label" for="paypal_sandbox">
                                    وضع التجربة (Sandbox)
                                </label>
                            </div>
                            
                            <!-- Stripe -->
                            <h6 class="mt-4 mb-3"><i class="fab fa-stripe"></i> إعدادات Stripe</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="stripe_public_key" class="form-label">المفتاح العام</label>
                                    <input type="text" class="form-control" id="stripe_public_key" name="stripe_public_key">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="stripe_secret_key" class="form-label">المفتاح السري</label>
                                    <input type="password" class="form-control" id="stripe_secret_key" name="stripe_secret_key">
                                </div>
                            </div>
                            
                            <!-- الدفع عند الوصول -->
                            <h6 class="mt-4 mb-3"><i class="fas fa-building"></i> الدفع عند الوصول</h6>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="pay_at_hotel_enabled" name="pay_at_hotel_enabled" checked>
                                <label class="form-check-label" for="pay_at_hotel_enabled">
                                    تفعيل خيار الدفع عند الوصول
                                </label>
                            </div>
                            
                            <!-- العملة -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="currency" class="form-label">العملة</label>
                                    <select class="form-select" id="currency" name="currency">
                                        <option value="SAR" selected>ريال سعودي (SAR)</option>
                                        <option value="USD">دولار أمريكي (USD)</option>
                                        <option value="EUR">يورو (EUR)</option>
                                        <option value="AED">درهم إماراتي (AED)</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="tax_rate" class="form-label">نسبة الضريبة (%)</label>
                                    <input type="number" class="form-control" id="tax_rate" name="tax_rate" 
                                           value="15" step="0.1" min="0" max="100">
                                </div>
                            </div>
                            
                            <button type="submit" name="update_payment_settings" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ إعدادات الدفع
                            </button>
                        </form>
                    </div>
                    
                    <!-- إعدادات النظام -->
                    <div class="tab-pane fade" id="system" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-primary mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fas fa-database"></i> معلومات قاعدة البيانات</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong>المضيف:</strong> <?php echo DB_HOST; ?></p>
                                        <p class="mb-1"><strong>اسم القاعدة:</strong> <?php echo DB_NAME; ?></p>
                                        <p class="mb-1"><strong>حجم القاعدة:</strong> 
                                            <?php 
                                            $result = $conn->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size 
                                                                   FROM information_schema.tables 
                                                                   WHERE table_schema = '" . DB_NAME . "'");
                                            $size = $result->fetch_assoc()['size'] ?? 0;
                                            echo $size . ' MB';
                                            ?>
                                        </p>
                                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="backupDatabase()">
                                            <i class="fas fa-download"></i> نسخ احتياطي
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border-success mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-server"></i> معلومات النظام</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong>إصدار PHP:</strong> <?php echo phpversion(); ?></p>
                                        <p class="mb-1"><strong>إصدار MySQL:</strong> <?php echo $conn->server_info; ?></p>
                                        <p class="mb-1"><strong>الحد الأقصى للرفع:</strong> <?php echo ini_get('upload_max_filesize'); ?></p>
                                        <p class="mb-0"><strong>وقت التشغيل:</strong> <?php echo round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2); ?> ثانية</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- أدوات النظام -->
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0"><i class="fas fa-tools"></i> أدوات النظام</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <button class="btn btn-outline-primary w-100" onclick="clearCache()">
                                            <i class="fas fa-broom"></i> مسح الذاكرة المؤقتة
                                        </button>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <button class="btn btn-outline-success w-100" onclick="optimizeDatabase()">
                                            <i class="fas fa-database"></i> تحسين قاعدة البيانات
                                        </button>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <button class="btn btn-outline-info w-100" onclick="checkUpdates()">
                                            <i class="fas fa-sync-alt"></i> التحقق من التحديثات
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- إعادة تعيين النظام -->
                                <div class="alert alert-danger mt-4">
                                    <h6><i class="fas fa-exclamation-triangle"></i> منطقة الخطر</h6>
                                    <p class="mb-2">هذه الإجراءات لا يمكن التراجع عنها:</p>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-outline-danger" onclick="clearLogs()">
                                            <i class="fas fa-trash"></i> مسح السجلات
                                        </button>
                                        <button class="btn btn-danger" onclick="resetSystem()">
                                            <i class="fas fa-redo"></i> إعادة تعيين النظام
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// اختبار إرسال البريد
function testEmail() {
    const email = document.getElementById('email_test').value;
    
    if (!email) {
        alert('يرجى إدخال بريد إلكتروني للاختبار');
        return;
    }
    
    if (confirm('هل تريد إرسال بريد اختباري إلى ' + email + '؟')) {
        fetch('test-email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
        })
        .catch(error => {
            alert('حدث خطأ: ' + error);
        });
    }
}

// نسخ احتياطي لقاعدة البيانات
function backupDatabase() {
    if (confirm('هل تريد إنشاء نسخة احتياطية لقاعدة البيانات؟')) {
        fetch('backup-database.php')
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.download_url) {
                    window.open(data.download_url, '_blank');
                }
            })
            .catch(error => {
                alert('حدث خطأ: ' + error);
            });
    }
}

// مسح الذاكرة المؤقتة
function clearCache() {
    if (confirm('هل تريد مسح الذاكرة المؤقتة؟')) {
        fetch('clear-cache.php')
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            })
            .catch(error => {
                alert('حدث خطأ: ' + error);
            });
    }
}

// تحسين قاعدة البيانات
function optimizeDatabase() {
    if (confirm('هل تريد تحسين قاعدة البيانات؟')) {
        fetch('optimize-database.php')
            .then(response => response.text())
            .then(data => {
                alert(data);
            })
            .catch(error => {
                alert('حدث خطأ: ' + error);
            });
    }
}

// التحقق من التحديثات
function checkUpdates() {
    fetch('check-updates.php')
        .then(response => response.json())
        .then(data => {
            if (data.update_available) {
                alert('يتوفر تحديث جديد: ' + data.latest_version + '\n\n' + data.changelog);
            } else {
                alert('أنت تستخدم أحدث نسخة من النظام');
            }
        })
        .catch(error => {
            alert('حدث خطأ أثناء التحقق من التحديثات');
        });
}

// مسح السجلات
function clearLogs() {
    if (confirm('هل تريد مسح جميع سجلات النظام؟\nلا يمكن التراجع عن هذا الإجراء.')) {
        fetch('clear-logs.php')
            .then(response => response.text())
            .then(data => {
                alert(data);
            })
            .catch(error => {
                alert('حدث خطأ: ' + error);
            });
    }
}

// إعادة تعيين النظام
function resetSystem() {
    if (confirm('تحذير: هذا الإجراء سيعيد تعيين النظام إلى الإعدادات الافتراضية ويمسح بعض البيانات.\nهل أنت متأكد؟')) {
        const password = prompt('أدخل كلمة مرور المدير للتأكيد:');
        if (password) {
            fetch('reset-system.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ password: password })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    setTimeout(() => {
                        window.location.href = 'logout.php';
                    }, 2000);
                }
            })
            .catch(error => {
                alert('حدث خطأ: ' + error);
            });
        }
    }
}
</script>

<?php include '../includes/footer.php'; ?>