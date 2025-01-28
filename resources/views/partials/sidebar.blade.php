<div class="left side-menu">
   <div class="slimscroll-menu" id="remove-scroll">
      <!--- Sidemenu -->
      <div id="sidebar-menu">
         <!-- Left Menu Start -->
         <ul class="metismenu" id="side-menu">
            <li class="menu-title">Main</li>
            <li class="">
               <a href="{{route('admin')}}" class="waves-effect {{ request()->is("admin") || request()->is("admin/*") ? "mm active" : "" }}">
               <i class="ti-home"></i> <span> Dashboard </span>
               </a>
            </li>
            <li>
               <a href="/students" class="waves-effect {{ request()->is("students") || request()->is("/students/*") ? "mm active" : "" }}"><i class="ti-user"></i><span>Students</span></a>
            </li>
            <li class="menu-title">Management</li>
            <li class="">
               <a href="/school-reports" class="waves-effect {{ request()->is("school-reports") || request()->is("school-reports/*") ? "mm active" : "" }}">
                  <i class="ti-time"></i> <span> School Reports </span>
               </a>
            </li>
            <li class="">
               <a href="/student-reports" class="waves-effect {{ request()->is("student-reports") || request()->is("student-reports/*") ? "mm active" : "" }}">
                  <i class="ti-time"></i> <span> Student Reports </span>
               </a>
            </li>
         </ul>
      </div>
      <div class="clearfix"></div>
   </div>
</div>