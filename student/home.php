<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>بوابة الطلاب — مساعد الطالب</title>
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
    <link rel="apple-touch-icon" sizes="180x180" href="../static/img/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="../static/img/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="../static/img/favicon-16x16.png" />
    <link rel="manifest" href="../static/img/site.webmanifest" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
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
            <h1>بوابة الطلاب</h1>
            <p>ابحث عن ملخصات المواد المختلفة التي أعدها المشرفين</p>
        </div>
    </header>

    <main>
        <div class="container">
            <!-- File Manager Section -->
            <section id="file-manager-section" class="info-section">
                <h2>
                    <i class="fas fa-file-alt"></i>
                    عارض الملفات
                </h2>
                <div id="file-browser">
                    <div class="browser-gpa">
                        <div class="form-group">
                            <select id="subject-select" name="subject" required>
                                <option value="" selected disabled>اختر المادة</option>
                            </select>
                        </div>
                    </div>
                    <div id="files-container"></div>
                </div>

                <div class="form-group">
                    <div class="form-actions">
                        <button type="button" id="back-button" class="btn btn-secondary" disabled>
                            <i class="fas fa-arrow-right"></i> العودة للخلف
                        </button>
                    </div>
                </div>

                <input type="hidden" id="current-folder" readonly>
                <input type="hidden" id="folder-path" name="folderPath" value="">
            </section>
        </div>
    </main>

    <footer class="copyright-footer">
        <p style="direction: ltr;">&copy; <?php echo date("Y"); ?> ichiwi.me</p>
    </footer>

    <script src="<?php echo auto_version('../static/js/main.js'); ?>"></script>
    <script src="<?php echo auto_version('../static/js/student-files.js'); ?>"></script>
</body>

</html>
