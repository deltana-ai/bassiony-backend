<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    {{auth('web-manager')->user()->name}}
    <form method="POST" action="{{ route('company.verification.send') }}">
    @csrf
    <button type="submit">إعادة إرسال رابط التحقق</button>
</form>
  </body>
</html>
