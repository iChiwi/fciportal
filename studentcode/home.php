<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>البحث عن طالب — مساعد الطالب</title>
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
                <a href="/study/" class="nav-button">
                    <i class="fas fa-book" aria-hidden="true"></i>
                    <span>بوابة الطلاب</span>
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
            <h1>البحث عن طالب</h1>
            <p>ابحث عن معلومات طالب باستخدام الرقم القومي</p>
        </div>
    </header>

    <main>
        <div class="container">
            <section id="search-section">
                <h2>
                    <i class="fas fa-id-card"></i>
                    الرقم القومي
                </h2>
                <form id="search-form" class="standard-form" novalidate>
                    <div id="national-search">
                        <input type="text" id="national-number" name="national-number" minlength="14" maxlength="14"
                            required placeholder="ادخل الرقم القومي المكون من 14 رقم" />
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
    const form = document.getElementById("search-form");
    const searchResult = document.getElementById("search-result");

    const nationalInput = document.getElementById("national-number");
    nationalInput.addEventListener("input", function() {
        this.value = this.value.replace(/[^0-9]/g, "");
        if (this.value.length === 14) {
            this.classList.add("valid");
        } else {
            this.classList.remove("valid");
        }
    });

    form.addEventListener("submit", async function(e) {
        e.preventDefault();

        const nationalNumber = nationalInput.value.trim();
        if (nationalNumber.length !== 14) {
            showError("يجب أن يكون الرقم القومي 14 رقمًا.");
            return;
        }

        searchResult.innerHTML = `
            <div class="loading-spinner" role="status">
                <i class="fas fa-spinner fa-spin"></i>
                <span>جارِ البحث...</span>
            </div>`;

        try {
            const response = await fetch(
                "https://fci.ichiwi.me/api/studentcode", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                    credentials: "include",
                    mode: "cors",
                    body: JSON.stringify({
                        national_number: nationalNumber
                    }),
                }
            );

            let data;
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                throw new Error(
                    "الخادم أرجع استجابة غير صالحة. يرجى المحاولة مرة أخرى لاحقًا.");
            }

            try {
                data = await response.json();
            } catch (jsonError) {
                throw new Error("غير قادر على معالجة استجابة الخادم. يرجى المحاولة مرة أخرى لاحقًا.");
            }

            if (!response.ok) {
                throw new Error(data.error || "خطأ في الخادم");
            }

            if (data.student_code === "N/A") {
                showError(
                    "لم يتم العثور على الطالب. يرجى التحقق من المعلومات والمحاولة مرة أخرى."
                );
                return;
            }

            searchResult.innerHTML = `
                <div class="result-container">
                    <h2>معلومات الطالب</h2>
                    <p><strong>الاسم:</strong> ${data.student_name}</p>
                    <p><strong>الكود:</strong> ${data.student_code}</p>
                    <p><strong>الكلية:</strong> ${data.faculty_name}</p>
                    <p><strong>المرحلة:</strong> ${data.phase_name}</p>
                    <p><strong>القسم:</strong> ${data.node_name}</p>
                    <p><strong>النوع:</strong> ${data.gender}</p>
                    <p><strong>الجنسية:</strong> ${data.nationality}</p>
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
    </script>
    <script src="<?php echo auto_version('../static/js/main.js'); ?>"></script>
</body>

</html>
