<div class="topbar">
   <div class="topbar-left">
      <a href="/" class="logo">
         <span>
            <h1 style="color: white; " class="text-small">BC</h1>
         </span>
         <i>
            <h1>BC</h1>
         </i>
      </a>
   </div>
   <nav class="navbar-custom">
      <ul class="navbar-right d-flex list-inline float-right mb-0">
         <li class="dropdown notification-list d-none d-md-block">
            <a class="nav-link waves-effect" href="#" id="btn-fullscreen">
            <i class="mdi mdi-fullscreen noti-icon"></i>
            </a>
         </li>
         <li class="dropdown notification-list">
            <div class="dropdown notification-list nav-pro-img">
               <a class="dropdown-toggle nav-link arrow-none waves-effect nav-user" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
               <i class="mdi mdi-account-circle noti-icon"></i>
               </a>
               <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                  <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault();
                     document.getElementById('logout-form').submit();"><i class="mdi mdi-power text-danger"></i> {{ __('Logout') }}</a>
                  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                     @csrf
                  </form>
               </div>
            </div>
         </li>
      </ul>
      <ul class="list-inline menu-left mb-0">
         <li class="float-left">
            <button class="button-menu-mobile open-left waves-effect">
            <i class="mdi mdi-menu"></i>
            </button>
         </li>
      </ul>
   </nav>
</div>