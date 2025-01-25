<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8" />
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
      <title>Attendance Management System</title>
      @include('partials.styles')
   </head>
   <body>
      <div>
         @yield('content')
         @include('partials.scripts')  
      </div>
   </body>
</html>