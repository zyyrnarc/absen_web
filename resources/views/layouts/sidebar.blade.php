<aside class="sidebar">
    <div class="px-4 mb-8">
        <div class="text-white text-xl font-bold text-center">Devgenfour</div>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="sidebar-icon">D</span> Dashboard</a>
        <a href="{{ route('monthly-absence') }}" class="sidebar-link {{ request()->routeIs('monthly-absence') ? 'active' : '' }}"><span class="sidebar-icon">M</span> Monthly Absence</a>
        <a href="{{ route('weekly-activity') }}" class="sidebar-link {{ request()->routeIs('weekly-activity') ? 'active' : '' }}"><span class="sidebar-icon">W</span> Weekly Activity</a>
        <a href="{{ route('manage-student') }}" class="sidebar-link {{ request()->routeIs('manage-student*') ? 'active' : '' }}"><span class="sidebar-icon">S</span> Manage Student</a>
        <a href="{{ route('manage-major') }}" class="sidebar-link {{ request()->routeIs('manage-major') ? 'active' : '' }}"><span class="sidebar-icon">J</span> Manage Major</a>
        <a href="{{ route('manage-campus') }}" class="sidebar-link {{ request()->routeIs('manage-campus*') ? 'active' : '' }}"><span class="sidebar-icon">C</span> Manage Campus</a>
        <a href="{{ route('manage-mentor') }}" class="sidebar-link {{ request()->routeIs('manage-mentor*') ? 'active' : '' }}"><span class="sidebar-icon">T</span> Manage Mentor</a>
        <a href="{{ route('setting') }}" class="sidebar-link {{ request()->routeIs('setting') ? 'active' : '' }}"><span class="sidebar-icon">G</span> Setting</a>
        <div class="flex-1"></div>
        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="sidebar-link text-red-400 w-full text-left">
                <span class="sidebar-icon">L</span> Logout
            </button>
        </form>
    </nav>
</aside>
