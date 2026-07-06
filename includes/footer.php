</div> <!-- إغلاق container -->

<!-- تذييل الصفحة -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>نظام حجز الفنادق</h5>
                <p>أفضل نظام لحجز الفنادق عبر الإنترنت. نوفر لك تجربة حجز سلسة ومريحة.</p>
            </div>

            <div class="col-md-4">
                <h5>روابط سريعة</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>index.php" class="text-white">الرئيسية</a></li>
                    <li><a href="<?php echo BASE_URL; ?>pages/hotels.php" class="text-white">الفنادق</a></li>

                    <?php if (!isLoggedIn()): ?>
                        <li><a href="<?php echo BASE_URL; ?>auth/login.php" class="text-white">تسجيل الدخول</a></li>
                        <li><a href="<?php echo BASE_URL; ?>auth/register.php" class="text-white">تسجيل جديد</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="col-md-4">
                <h5>تواصل معنا</h5>

                <p>
                    <i class="fas fa-phone"></i>
                    <a href="tel:967775441092" class="text-white">967775441092</a> |
                    <a href="https://wa.me/967775441092" target="_blank" class="text-white">واتساب</a>
                </p>

                <p>
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:shehabmtwan@gmail.com" class="text-white">
                        shehabmtwan@gmail.com
                    </a>
                </p>

                <p><i class="fas fa-map-marker-alt"></i> يد</p>
            </div>
        </div>

        <hr class="bg-white">

        <div class="text-center">
            <p>
                © <?php echo date('Y'); ?> 
                جميع الحقوق محفوظة | 
                <a href="https://wa.me/967775441092" target="_blank" style="color:#0f0;">
                    Eng. Shihab Mtwan
                </a>
            </p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript الخاص -->
<script src="<?php echo BASE_URL; ?>js/script.js"></script>

<script>
    // تأكيد العمليات الحساسة
    function confirmAction(message) {
        return confirm(message || 'هل أنت متأكد من تنفيذ هذا الإجراء؟');
    }
    
    // عرض/إخفاء كلمة المرور
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
    }
</script>
</body>
</html>