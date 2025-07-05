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
        <h1>
          {{__('auth.from')}}
          @if($contact->contactable_type ==="App\Models\Driver")
          {{__('auth.driver')}}
          @elseif($contact->contactable_type ==="App\Models\Pharmacist")
          {{__('auth.pharmacist')}}
          @elseif($contact->contactable_type ==="App\Models\User")
          {{__('auth.client')}}
          @endif
        </h1>

        <h2>تم ارسال رسالة اتصال من

           {{$contact->name}}</h2>
        <p> البريد الالكتروني : {{$contact->email}} </p>
        <p> الرسالة: {{$contact->message}}</p>

        <hr style="margin: 40px 0;">

        <h2> new message has been sent from  {{$contact->name}} </h2>
        <p> Email : {{$contact->email}}</p>
        <p> Message : {{$contact->message}}</p>


        <div class="footer">
            © {{ date('Y') }} {{__('lang.'.config('app.name'))}}. All rights reserved.
        </div>
    </div>
</body>
</html>
