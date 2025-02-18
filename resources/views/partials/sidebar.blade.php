<div class="left side-menu">
   <div class="slimscroll-menu" id="remove-scroll">
      <div id="sidebar-menu">
      <ul class="metismenu" id="side-menu">
         <li class="menu-title">Main</li>
         
         <li>
            <a href="{{route('admin')}}" class="waves-effect {{ request()->is('admin') || request()->is('admin/*') ? 'mm active' : '' }}">
                  <i class="fas fa-chart-line"></i> <span> Dashboard </span>
            </a>
         </li>
         
         <li>
            <a href="/students" class="waves-effect {{ request()->is('students') || request()->is('/students/*') ? 'mm active' : '' }}">
                  <i class="fas fa-user-graduate"></i><span>Students</span>
            </a>
         </li>
         
         <li>
            <a href="/semesters" class="waves-effect {{ request()->is('semesters') || request()->is('/semesters/*') ? 'mm active' : '' }}">
                  <i class="fas fa-calendar-alt"></i><span>Semesters</span>
            </a>
         </li>
         
         <li>
            <a href="/holidays" class="waves-effect {{ request()->is('holidays') || request()->is('/holidays/*') ? 'mm active' : '' }}">
                  <i class="fas fa-calendar-day"></i><span>School Events</span>
            </a>
         </li>
         
         <li>
            <a href="{{route('reports.visitor.index')}}" class="waves-effect {{ request()->is('reports/visitors') || request()->is('reports/visitors/*') ? 'mm active' : '' }}">
                  <i class="fas fa-user-check"></i><span>Reports - Visitors</span>
            </a>
         </li>
         
         <li>
            <a href="{{route('reports.students.index')}}" class="waves-effect {{ request()->is('reports/students') || request()->is('reports/students/*') ? 'mm active' : '' }}">
                  <i class="fas fa-file-alt"></i><span>Reports - Students</span>
            </a>
         </li>
         
         <li>
            <a href="{{route('reports.courses.index')}}" class="waves-effect {{ request()->is('reports/students') || request()->is('reports/students/*') ? 'mm active' : '' }}">
                  <i class="fas fa-building"></i><span>Reports - Department</span>
            </a>
         </li>
         
         <li>
            <a href="{{route('absences.index')}}" class="waves-effect {{ request()->is('absences') || request()->is('/absences/*') ? 'mm active' : '' }}">
                  <i class="fas fa-user-times"></i><span>Absence Records</span>
            </a>
         </li>
      </ul>

      </div>
      <div class="clearfix"></div>
   </div>
</div>
