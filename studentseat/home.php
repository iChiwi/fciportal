<?php
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

        studentCodeInput.addEventListener("input",
            function() {
                this.value = this.value.replace(/[^\w]/g, "").toUpperCase();

                if (this.value.length == 14) {
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

            if (studentCode.length != 14) {
                showError("يجب أن يكون كود الطالب 14 حرفًا.");
                return;
            }

            searchResult.style.display = 'block';
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

                const seatNumber = parseInt(data.seat_number);
                let examRoom = "غير متاح";
                let roomLocation = "غير متاح";

                if (!isNaN(seatNumber)) {
                    if (seatNumber >= 1 && seatNumber <= 25) {
                        examRoom = "لجنة 1";
                        roomLocation = "مدرج رقم (1) بالمبني التعليمي";
                    } else if (seatNumber >= 26 && seatNumber <= 50) {
                        examRoom = "لجنة 2";
                        roomLocation = "مدرج رقم (1) بالمبني التعليمي";
                    } else if (seatNumber >= 51 && seatNumber <= 75) {
                        examRoom = "لجنة 3";
                        roomLocation = "مدرج رقم (1) بالمبني التعليمي";
                    } else if (seatNumber >= 76 && seatNumber <= 100) {
                        examRoom = "لجنة 4";
                        roomLocation = "مدرج رقم (1) بالمبني التعليمي";
                    } else if (seatNumber >= 101 && seatNumber <= 125) {
                        examRoom = "لجنة 5";
                        roomLocation = "مدرج رقم (2) بالمبني التعليمي";
                    } else if (seatNumber >= 126 && seatNumber <= 150) {
                        examRoom = "لجنة 6";
                        roomLocation = "مدرج رقم (2) بالمبني التعليمي";
                    } else if (seatNumber >= 151 && seatNumber <= 175) {
                        examRoom = "لجنة 7";
                        roomLocation = "مدرج رقم (2) بالمبني التعليمي";
                    } else if (seatNumber >= 176 && seatNumber <= 200) {
                        examRoom = "لجنة 8";
                        roomLocation = "مدرج رقم (2) بالمبني التعليمي";
                    } else if (seatNumber >= 201 && seatNumber <= 225) {
                        examRoom = "لجنة 9";
                        roomLocation = "مدرج رقم (3) بالمبني التعليمي";
                    } else if (seatNumber >= 226 && seatNumber <= 250) {
                        examRoom = "لجنة 10";
                        roomLocation = "مدرج رقم (3) بالمبني التعليمي";
                    } else if (seatNumber >= 251 && seatNumber <= 275) {
                        examRoom = "لجنة 11";
                        roomLocation = "مدرج رقم (3) بالمبني التعليمي";
                    } else if (seatNumber >= 276 && seatNumber <= 300) {
                        examRoom = "لجنة 12";
                        roomLocation = "مدرج رقم (3) بالمبني التعليمي";
                    } else if (seatNumber >= 301 && seatNumber <= 325) {
                        examRoom = "لجنة 13";
                        roomLocation = "مدرج رقم (4) بالمبني التعليمي";
                    } else if (seatNumber >= 326 && seatNumber <= 350) {
                        examRoom = "لجنة 14";
                        roomLocation = "مدرج رقم (4) بالمبني التعليمي";
                    } else if (seatNumber >= 351 && seatNumber <= 375) {
                        examRoom = "لجنة 15";
                        roomLocation = "مدرج رقم (4) بالمبني التعليمي";
                    } else if (seatNumber >= 376 && seatNumber <= 400) {
                        examRoom = "لجنة 16";
                        roomLocation = "مدرج رقم (4) بالمبني التعليمي";
                    } else if (seatNumber >= 401 && seatNumber <= 425) {
                        examRoom = "لجنة 17";
                        roomLocation = "مدرج رقم (1) برامج جديدة";
                    } else if (seatNumber >= 426 && seatNumber <= 450) {
                        examRoom = "لجنة 18";
                        roomLocation = "مدرج رقم (2) برامج جديدة";
                    } else if (seatNumber >= 451 && seatNumber <= 475) {
                        examRoom = "لجنة 19";
                        roomLocation = "مدرج رقم (3) برامج جديدة";
                    } else if (seatNumber >= 476 && seatNumber <= 500) {
                        examRoom = "لجنة 20";
                        roomLocation = "مدرج رقم (4) برامج جديدة";
                    } else if (seatNumber >= 501 && seatNumber <= 525) {
                        examRoom = "لجنة 21";
                        roomLocation = "معمل رقم (1) مركز الاستشارات";
                    } else if (seatNumber >= 526 && seatNumber <= 550) {
                        examRoom = "لجنة 22";
                        roomLocation = "معمل رقم (2) مركز الاستشارات";
                    } else if (seatNumber >= 551 && seatNumber <= 575) {
                        examRoom = "لجنة 23";
                        roomLocation = "طرقة مركز الاستشارات";
                    } else if (seatNumber >= 576 && seatNumber <= 600) {
                        examRoom = "لجنة 24";
                        roomLocation = "مدرج رقم (5) بالمبني الاداري";
                    } else if (seatNumber >= 601 && seatNumber <= 625) {
                        examRoom = "لجنة 25";
                        roomLocation = "مدرج رقم (5) بالمبني الاداري";
                    } else if (seatNumber >= 626 && seatNumber <= 650) {
                        examRoom = "لجنة 26";
                        roomLocation = "مدرج رقم (6) بالمبني الاداري";
                    } else if (seatNumber >= 651 && seatNumber <= 675) {
                        examRoom = "لجنة 27";
                        roomLocation = "مدرج رقم (6) بالمبني الاداري";
                    } else if (seatNumber >= 676 && seatNumber <= 700) {
                        examRoom = "لجنة 28";
                        roomLocation = "مدرج رقم (7) بالمبني الاداري";
                    } else if (seatNumber >= 701 && seatNumber <= 725) {
                        examRoom = "لجنة 29";
                        roomLocation = "مدرج رقم (7) بالمبني الاداري";
                    } else if (seatNumber >= 726 && seatNumber <= 750) {
                        examRoom = "لجنة 30";
                        roomLocation = "طرق رعاية الشباب";
                    } else if (seatNumber >= 751 && seatNumber <= 775) {
                        examRoom = "لجنة 31";
                        roomLocation = "قاعة رعاية الشباب";
                    } else if (seatNumber >= 776 && seatNumber <= 800) {
                        examRoom = "لجنة 32";
                        roomLocation = "المدرج الرئيسي";
                    } else if (seatNumber >= 801 && seatNumber <= 825) {
                        examRoom = "لجنة 33";
                        roomLocation = "المدرج الرئيسي";
                    } else if (seatNumber >= 826 && seatNumber <= 850) {
                        examRoom = "لجنة 34";
                        roomLocation = "المدرج الرئيسي";
                    } else if (seatNumber >= 851 && seatNumber <= 875) {
                        examRoom = "لجنة 35";
                        roomLocation = "المدرج الرئيسي";
                    } else if (seatNumber >= 876 && seatNumber <= 900) {
                        examRoom = "لجنة 36";
                        roomLocation = "المدرج الرئيسي";
                    } else if (seatNumber >= 901 && seatNumber <= 925) {
                        examRoom = "لجنة 37";
                        roomLocation = "المدرج الرئيسي";
                    } else if (seatNumber >= 926 && seatNumber <= 950) {
                        examRoom = "لجنة 38";
                        roomLocation = "المدرج الرئيسي";
                    } else if (seatNumber >= 951 && seatNumber <= 975) {
                        examRoom = "لجنة 39";
                        roomLocation = "القاعة المجاورة للمدرج الرئيسي";
                    } else if (seatNumber >= 976 && seatNumber <= 1000) {
                        examRoom = "لجنة 40";
                        roomLocation = "القاعة المجاورة للمدرج الرئيسي";
                    } else if (seatNumber >= 1001 && seatNumber <= 1025) {
                        examRoom = "لجنة 41";
                        roomLocation = "معمل رقم (1) بالدور الرابع";
                    } else if (seatNumber >= 1026 && seatNumber <= 1050) {
                        examRoom = "لجنة 42";
                        roomLocation = "معمل رقم (2) بالدور الرابع";
                    } else if (seatNumber >= 1051 && seatNumber <= 1075) {
                        examRoom = "لجنة 43";
                        roomLocation = "معمل رقم (3) بالدور الرابع";
                    } else if (seatNumber >= 1076 && seatNumber <= 1100) {
                        examRoom = "لجنة 44";
                        roomLocation = "معمل رقم (4) بالدور الرابع";
                    } else if (seatNumber >= 1101 && seatNumber <= 1125) {
                        examRoom = "لجنة 45";
                        roomLocation = "معمل رقم (5) بالدور الرابع";
                    } else if (seatNumber >= 1126 && seatNumber <= 1150) {
                        examRoom = "لجنة 46";
                        roomLocation = "معمل رقم (7) بالدور الرابع";
                    } else if (seatNumber >= 1151 && seatNumber <= 1175) {
                        examRoom = "لجنة 47";
                        roomLocation = "معمل رقم (8) بالدور الرابع";
                    } else if (seatNumber >= 1176 && seatNumber <= 1200) {
                        examRoom = "لجنة 48";
                        roomLocation = "معمل رقم (9) بالدور الرابع";
                    } else if (seatNumber >= 1201 && seatNumber <= 1225) {
                        examRoom = "لجنة 49";
                        roomLocation = "معمل رقم (10) بالدور الرابع";
                    } else if (seatNumber >= 1226 && seatNumber <= 1250) {
                        examRoom = "لجنة 50";
                        roomLocation = "معمل رقم (11) بالدور الرابع";
                    } else if (seatNumber >= 1251 && seatNumber <= 1275) {
                        examRoom = "لجنة 51";
                        roomLocation = "المحيط الخارجي بالدور الرابع";
                    } else if (seatNumber >= 1276 && seatNumber <= 1300) {
                        examRoom = "لجنة 52";
                        roomLocation = "المحيط الخارجي بالدور الرابع";
                    } else if (seatNumber >= 1301 && seatNumber <= 1325) {
                        examRoom = "لجنة 53";
                        roomLocation = "المحيط الخارجي بالدور الرابع";
                    } else if (seatNumber >= 1326 && seatNumber <= 1350) {
                        examRoom = "لجنة 54";
                        roomLocation = "المحيط الخارجي بالدور الرابع";
                    } else if (seatNumber >= 1351 && seatNumber <= 1375) {
                        examRoom = "لجنة 55";
                        roomLocation = "المحيط الخارجي بالدور الرابع";
                    } else if (seatNumber >= 1376 && seatNumber <= 1389) {
                        examRoom = "لجنة 56";
                        roomLocation = "المحيط الخارجي بالدور الرابع";
                    } else {
                        examRoom = "غير معروفة";
                        roomLocation = "غير معروف";
                    }
                } else {
                    examRoom = "غير متاح";
                    roomLocation = "غير متاح";
                }

                const resultClass = data.seat_number ? 'result-success' : 'result-warning';

                searchResult.innerHTML = `
                    <div class="result-container ${resultClass}">
                        <h2>معلومات الجلوس</h2>
                        <p><strong>اسم الطالب:</strong> ${data.name || "غير متاح"}</p>
                        <p><strong>رقم الجلوس:</strong> ${data.seat_number || "غير متاح"}</p>
                        <p><strong>اللجنة:</strong> ${examRoom}</p>
                        <p><strong>مكان اللجنة:</strong> ${roomLocation}</p>
                        ${!data.seat_number ? '<div class="result-note"><i class="fas fa-exclamation-triangle"></i> لم يتم تخصيص رقم جلوس لهذا الطالب بعد. يرجى المراجعة لاحقًا.</div>' : ''}
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
