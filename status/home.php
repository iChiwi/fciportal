<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حالة النظام — مساعد الطالب</title>
    <meta name="description" content="منصتك الشاملة للموارد والأدوات الأكاديمية لطلاب كلية الحاسبات والمعلومات" />
    <?php 
    require_once '../config.php';
    
    function auto_version($file) {
        if (file_exists($file)) {
            return $file . '?v=' . filemtime($file);
        }
        return $file;
    }
    ?>
    <link href="<?php echo auto_version('../static/css/main.css'); ?>" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="../static/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../static/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../static/img/favicon-16x16.png">
    <link rel="manifest" href="../static/img/site.webmanifest">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>
    <nav class="nav-container">
        <div class="nav-content">
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <div class="nav-links" id="nav-links">
                <a href="/" class="nav-button">
                    <i class="fas fa-home" aria-hidden="true"></i>
                    <span>الصفحة الرئيسية</span>
                </a>
                <a href="/student/" class="nav-button">
                    <i class="fas fa-book" aria-hidden="true"></i>
                    <span>بوابة الطلاب</span>
                </a>
                <a href="/studentcode/" class="nav-button">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <span>البحث عن طالب</span>
                </a>
                <a href="/results/" class="nav-button">
                    <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                    <span>نتائج الطلاب</span>
                </a>
                <a href="/links/" class="nav-button">
                    <i class="fas fa-link" aria-hidden="true"></i>
                    <span>روابط الجروبات</span>
                </a>
                <a href="/admin/" class="nav-button">
                    <i class="fas fa-shield-alt" aria-hidden="true"></i>
                    <span>بوابة المسؤول</span>
                </a>
            </div>
        </div>
    </nav>

    <header>
        <div class="header-content">
            <h1>حالة النظام</h1>
            <p>الحالة الحالية لجميع أنظمة البوابة</p>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="info-section">
                <div class="status-overview">
                    <div class="status-summary">
                        <h2>
                            <i class="fas fa-server"></i>
                            حالة الخدمات
                        </h2>
                        <div class="system-summary">
                            <div class="status-badge status-operational">
                                <i class="fas fa-check-circle"></i>
                                جميع الأنظمة تعمل بشكل طبيعي.
                            </div>
                            <p class="last-update">آخر تحديث: <?php echo date("Y/m/d - h:i A"); ?></p>
                        </div>
                    </div>
                </div>

                <div class="status-grid">
                    <div class="status-card">
                        <div class="status-header">
                            <h3>
                                <span class="status-indicator status-online" title="النظام يعمل"></span>
                                <span>الجامعة API</span>
                            </h3>
                            <span class="uptime">
                                <i class="fas fa-history" aria-hidden="true"></i>
                                100% متاح
                            </span>
                        </div>
                        <div class="status-details">
                            <p>يعمل نظام ايجاد كود الطالب والنتائج بشكل طبيعي.</p>
                        </div>
                    </div>
                    <div class="status-card">
                        <div class="status-header">
                            <h3>
                                <span class="status-indicator status-online" title="النظام يعمل"></span>
                                <span>نظام رفع الملفات</span>
                            </h3>
                            <span class="uptime">
                                <i class="fas fa-cloud-upload-alt" aria-hidden="true"></i>
                                100% متاح
                            </span>
                        </div>
                        <div class="status-details">
                            <p>يعمل نظام رفع الملفات بشكل طبيعي.</p>
                        </div>
                    </div>
            </section>
        </div>
    </main>

    <footer class="copyright-footer">
        <p style="direction: ltr;">&copy; <?php echo date("Y"); ?> ichiwi.me</p>
    </footer>
    <script src="<?php echo auto_version('../static/js/main.js'); ?>"></script>
</body>

</html>
