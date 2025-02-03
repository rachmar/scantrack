<div class="left side-menu">
   <div class="slimscroll-menu" id="remove-scroll">
      <div id="sidebar-menu">
         <ul class="metismenu" id="side-menu">
            <li class="menu-title">Main</li>
            <li class="">
               <a href="{{route('admin')}}" class="waves-effect {{ request()->is("admin") || request()->is("admin/*") ? "mm active" : "" }}">
                  <i class="fas fa-tachometer-alt"></i> <span> Dashboard </span>
               </a>
            </li>
            <li>
               <a href="/students" class="waves-effect {{ request()->is("students") || request()->is("/students/*") ? "mm active" : "" }}">
                  <i class="fas fa-users"></i><span>Students</span>
               </a>
            </li>
            <li class="">
               <a href="/school-reports" class="waves-effect {{ request()->is("school-reports") || request()->is("school-reports/*") ? "mm active" : "" }}">
                  <i class="fas fa-chart-bar"></i><span>School Report</span>
               </a>
            </li>
            <li class="">
               <a href="{{route('reports.courses.index')}}" class="waves-effect {{ request()->is("reports/students") || request()->is("reports/students/*") ? "mm active" : "" }}">
                  <i class="fas fa-file-alt"></i><span>Reports - Courses</span>
               </a>
            </li>
            <li class="">
               <a href="{{route('reports.students.index')}}" class="waves-effect {{ request()->is("reports/students") || request()->is("reports/students/*") ? "mm active" : "" }}">
                  <i class="fas fa-file-alt"></i><span>Reports - Students</span>
               </a>
            </li>
         </ul>
      </div>
      <div class="clearfix"></div>
   </div>
</div>
