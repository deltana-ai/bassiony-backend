<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f2f2f2;
            padding: 30px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 0 10px #ccc;
            text-align: center;
        }

        .logo {
            max-width: 120px;
            margin-bottom: 20px;
        }

        .otp {
            font-size: 36px;
            font-weight: bold;
            color: #2e7d32;
            letter-spacing: 5px;
            margin: 20px 0;
        }

        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <img class="logo" src="{{ asset('logo.png') }}" alt="Logo">

        <h2>رمز التحقق الخاص بك</h2>
        <p>يرجى استخدام رمز التحقق التالي لتأكيد حسابك. هذا الرمز صالح لمدة 10 دقائق فقط.</p>
        <div class="otp">{{ $code }}</div>
        <p>إذا لم تطلب هذا الرمز، يرجى تجاهل هذه الرسالة.</p>

        <hr style="margin: 40px 0;">

        <h2>Your Verification Code</h2>
        <p>Please use the code below to verify your account. This code is valid for 10 minutes only.</p>
        <div class="otp">{{ $code }}</div>
        <p>If you didn’t request this code, you can safely ignore this email.</p>

        <div class="footer">
            © {{ date('Y') }} YourAppName. All rights reserved.
        </div>
    </div>
</body>
</html>
