<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>العد التنازلي لافتتاح صيدليتنا الجديدة</title>
    <style>
        body {
            font-family: 'Tajawal', 'Arial', sans-serif;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            background-size: 400% 400%;
            animation: gradient 20s ease infinite;  /* تقليل سرعة الأنيميشن */
            color: #fff;
            text-align: center;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .loading-bar {
            width: 100%;
            height: 6px;
            background-color: rgba(255, 255, 255, 0.3);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 9999;
            border-radius: 3px;
        }

        .loading-bar .progress {
            width: 0;
            height: 100%;
            background: linear-gradient(90deg, #4b6cb7, #182848);
            border-radius: 3px;
            transition: width 1s linear;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 40px;
            border-radius: 15px;
            max-width: 800px;
            margin: 20px auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo {
            max-width: 120px;  /* تقليص حجم الأيقونة */
            margin-bottom: 20px;
            filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.5));
            color: #ffd700;
        }

        h1 {
            font-size: 2.8rem;
            margin-bottom: 10px;
            color: #fff;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.7);
        }

        .subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: #ffd700;
        }

        #countdown {
            font-size: 2rem;
            font-weight: bold;
            margin-top: 30px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }

        .countdown-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 15px;
            min-width: 100px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }

        .countdown-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.2);
        }

        .countdown-value {
            font-size: 3.5rem;
            font-weight: 700;
            color: #ffd700;
            margin-bottom: 5px;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
        }

        .label {
            font-size: 1.3rem;
            color: #fff;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 40px;
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .highlight {
            color: #ffd700;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 20px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .subtitle {
                font-size: 1.2rem;
            }
            
            .countdown-item {
                margin: 5px;
                min-width: 70px;
                padding: 10px;
            }
            
            .countdown-value {
                font-size: 2rem;
            }
            
            .label {
                font-size: 1rem;
            }
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- شريط التحميل -->
    <div class="loading-bar">
        <div class="progress" id="timeProgress"></div>
    </div>

    <div class="container">
        <!-- أيقونة صيدلية -->
        <i class="fas fa-pills logo"></i> 
        
        <h1>العد التنازلي للافتتاح</h1>
        <p class="subtitle">صيدليتنا الجديدة تفتح أبوابها قريباً!</p>
        
        <div id="countdown">
            <div class="countdown-item">
                <span class="countdown-value" id="days"></span>
                <span class="label">أيام</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-value" id="hours"></span>
                <span class="label">ساعات</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-value" id="minutes"></span>
                <span class="label">دقائق</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-value" id="seconds"></span>
                <span class="label">ثواني</span>
            </div>
        </div>
        
        <div class="footer">
            نحن نعمل بجد لتقديم أفضل الخدمات والصيادلة المختصين لرعايتكم الصحية
        </div>
    </div>

    <script>
        // تحديد التاريخ النهائي (10 أغسطس 2025)
        const targetDate = new Date("2025-08-10T00:00:00").getTime();
        const totalDuration = targetDate - new Date().getTime();

        // تحديث العد التنازلي كل ثانية
        const countdown = setInterval(function() {
            const now = new Date().getTime();
            const distance = targetDate - now;

            // حساب النسبة المئوية للوقت المتبقي
            const percentage = Math.max(0, Math.min(100, 100 - (distance / totalDuration * 100)));
            document.getElementById("timeProgress").style.width = percentage + "%";

            // حساب الأيام، الساعات، الدقائق، والثواني المتبقية
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // عرض العد التنازلي
            document.getElementById("days").innerHTML = days;
            document.getElementById("hours").innerHTML = hours.toString().padStart(2, '0');
            document.getElementById("minutes").innerHTML = minutes.toString().padStart(2, '0');
            document.getElementById("seconds").innerHTML = seconds.toString().padStart(2, '0');

            // إذا انتهى الوقت
            if (distance < 0) {
                clearInterval(countdown);
                document.getElementById("countdown").innerHTML = `
                    <div style="font-size: 2rem; color: #ffd700; text-shadow: 0 0 10px rgba(255,215,0,0.7);">
                        لقد تم الافتتاح! زورونا الآن في صيدليتنا الجديدة
                    </div>
                `;
                document.getElementById("timeProgress").style.width = "100%";
            }
        }, 1000);
    </script>
</body>
</html>
