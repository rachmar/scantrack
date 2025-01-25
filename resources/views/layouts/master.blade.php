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
      <div id="wrapper">
         @include('partials.header')
         @include('partials.sidebar')
         <div class="content-page">
            <div class="content">
               <div class="container-fluid">
                  @include('partials.breadcrumb')
                  @yield('content')
               </div>
            </div>
         </div>
         @include('partials.scripts')  
      </div>
   </body>
</html>