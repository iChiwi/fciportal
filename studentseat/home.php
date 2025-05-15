<?php
// filepath: /mnt/ddrive/Workspace/VPS/var/www/fci.ichiwi.me/studentseat/home.php
require_once '../config.php';

function auto_version($file) {
    if (file_exists($file)) {
        return $file . '?v=' . filemtime($file);
    }
    return $file;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>أرقام الجلوس — مساعد الطالب</title>
    <meta name="description" content="ابحث عن رقم جلوسك باستخدام كود الطالب" />
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
                <a href="/study/" class="nav-button">
                    <i class="fas fa-book" aria-hidden="true"></i>
                    <span>بوابة الطلاب</span>
                </a>
                <a href="/studentcode/" class="nav-button">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <span>البحث عن طالب</span>
                </a>
                <a href="/studentresults/" class="nav-button">
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
            <h1>أرقام الجلوس</h1>
            <p>ابحث عن رقم جلوسك باستخدام كود الطالب</p>
        </div>
    </header>

    <main>
        <div class="container">
            <section id="search-section">
                <h2>
                    <i class="fas fa-user-graduate"></i>
                    كود الطالب
                </h2>
                <form id="search-form" class="standard-form" novalidate>
                    <div id="student-code-search">
                        <input type="text" id="student-code" name="student-code" required
                            placeholder="ادخل كود الطالب" />
                    </div>
                    <button type="submit" class="btn">بحث</button>
                </form>
                <div id="search-result"></div>
            </section>
        </div>
    </main>

    <footer class="copyright-footer">
        <p style="direction: ltr;">&copy; <?php echo date("Y"); ?> ichiwi.me</p>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById("search-form");
        const searchResult = document.getElementById("search-result");
        const studentCodeInput = document.getElementById("student-code");

        // Validate student code input
        studentCodeInput.addEventListener("input", function() {
            // Remove non-alphanumeric characters
            this.value = this.value.replace(/[^\w]/g, "").toUpperCase();

            // Add valid class if the input has content
            if (this.value.length > 0) {
                this.classList.add("valid");
            } else {
                this.classList.remove("valid");
            }
        });

        form.addEventListener("submit", async function(e) {
            e.preventDefault();

            const studentCode = studentCodeInput.value.trim();
            if (!studentCode) {
                showError("يجب إدخال كود الطالب");
                return;
            }

            searchResult.innerHTML = `
                <div class="loading-spinner" role="status">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>جارِ البحث...</span>
                </div>`;

            try {
                const response = await fetch(
                    "/api/studentseat", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                        },
                        body: JSON.stringify({
                            student_code: studentCode
                        }),
                    }
                );

                // Check for non-JSON responses
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error(
                        "الخادم أرجع استجابة غير صالحة. يرجى المحاولة مرة أخرى لاحقًا.");
                }

                let data;
                try {
                    data = await response.json();
                } catch (jsonError) {
                    throw new Error(
                        "غير قادر على معالجة استجابة الخادم. يرجى المحاولة مرة أخرى لاحقًا.");
                }

                if (!response.ok) {
                    throw new Error(data.error || "خطأ في الخادم");
                }

                // Display the seat information
                searchResult.innerHTML = `
                    <div class="result-container">
                        <h2>معلومات الجلوس</h2>
                        <p><strong>رقم الجلوس:</strong> ${data.seat_number || "غير متاح"}</p>
                        <p><strong>الشعبة:</strong> ${data.section || "غير متاح"}</p>
                    </div>`;
            } catch (error) {
                showError(error.message || "خطأ في الاتصال. يرجى المحاولة مرة أخرى لاحقاً.");
            }
        });

        function showError(message) {
            searchResult.innerHTML = `
                <div class="result-error" role="alert">
                    <span>${message}</span>
                </div>`;
        }
    });
    </script>
    <script src="<?php echo auto_version('../static/js/main.js'); ?>"></script>
</body>

</html>
