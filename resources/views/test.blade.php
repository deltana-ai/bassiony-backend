<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>test</title>
  </head>
  <body>
    @if(session('success'))

<div class="alert alert-success alert-dismissible ">
  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
   <strong>@lang('lang.'.session('success')) </strong>
 </div>
@endif
@if(session('status'))

<div class="alert alert-success alert-dismissible ">
  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
   <strong>@lang('lang.'.session('status')) </strong>
 </div>
@endif
@if(session('fail'))

<div class="alert alert-danger alert-dismissible  " >
  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
   <strong> {{ __('lang.'.session('fail'))}}</strong>
 </div>
@endif
@if ($errors->any())

@foreach($errors->all() as $error)
<div class="alert alert-danger alert-dismissible  " >
  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
  <strong> {{ $error }} </strong>
</div>

@endforeach
@endif
    register
    <form class="" action="{{url('company/register')}}" method="post">
      @csrf
    name :  <input type="text" name="name" value=""> <br>
      email :<input type="email" name="email" value=""><br>
    password :  <input type="password" name="password" value=""><br>
      password_confirmation :<input type="password" name="password_confirmation" value=""><br>
    phone:  <input type="tel" name="phone" value=""><br>
      address :<input type="text" name="address" value=""><br>
      <button type="submit" name="button">save</button>
    </form>

<hr>
    login
    <form class="" action="{{url('company/login')}}" method="post">
      @csrf
      email :<input type="email" name="email" value=""><br>
      password :  <input type="password" name="password" value=""><br>
      <button type="submit" name="button">save</button>
    </form>
  </body>
</html>
