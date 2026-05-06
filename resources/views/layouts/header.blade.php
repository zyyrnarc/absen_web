@php
    use App\Models\AppSetting;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Storage;

    $routeName = request()->route()?->getName();
    $admin = Auth::guard('admin')->user();
    $setting = AppSetting::query()->first();

    $pageMeta = match ($routeName) {
        'dashboard' => [
            'badge' => 'Admin Dashboard',
            'title' => 'Hello Admin, welcome back.',
            'subtitle' => 'Monitor attendance, permits, and student activity from one clean and focused workspace.',
        ],
        'manage-student' => [
            'badge' => 'Manage Student',
            'title' => 'Manage intern accounts with better clarity.',
            'subtitle' => 'Add, update, and review active student records in one streamlined page.',
        ],
        'manage-major' => [
            'badge' => 'Manage Major',
            'title' => 'Organize majors and study programs quickly.',
            'subtitle' => 'Use this page to keep major data consistent, searchable, and easy to maintain.',
        ],
        'manage-campus' => [
            'badge' => 'Manage Campus',
            'title' => 'Manage campus records in one focused page.',
            'subtitle' => 'Add, update, search, and review campus information so student data stays consistent.',
        ],
        'manage-mentor' => [
            'badge' => 'Manage Mentor',
            'title' => 'Manage mentor data more clearly.',
            'subtitle' => 'Keep mentor names and positions organized so the team can update records with less friction.',
        ],
        'monthly-absence' => [
            'badge' => 'Monthly Absence',
            'title' => 'Keep monthly attendance under control.',
            'subtitle' => 'Review daily attendance, total workdays, and permits waiting for approval in one place.',
        ],
        'weekly-activity' => [
            'badge' => 'Weekly Activity',
            'title' => 'Follow weekly student activity more clearly.',
            'subtitle' => 'Filter by student and month to review weekly progress in a more readable layout.',
        ],
        'profile' => [
            'badge' => 'Admin Profile',
            'title' => 'Review your account information.',
            'subtitle' => 'See your login identity and jump quickly to company settings from here.',
        ],
        'setting' => [
            'badge' => 'Settings',
            'title' => 'Update company details and password.',
            'subtitle' => 'Manage your company logo, company identity, address, and account security in one place.',
        ],
        default => [
            'badge' => 'Admin Panel',
            'title' => 'Welcome to the admin panel.',
            'subtitle' => 'Manage your data and daily activity from one consistent workspace.',
        ],
    };

    $logoUrl = $setting?->company_logo_path ? Storage::url($setting->company_logo_path) : null;
    $avatarLetter = 'A';
    $profileImageUrl = $logoUrl ?: ($admin?->avatar_path ? Storage::url($admin->avatar_path) : null);
    $displayAdminName = 'Admin';
    $displayAdminRole = $setting?->company_name ?: 'Admin Panel';
@endphp

<header class="top-header">
    <div class="dashboard-header">
        <div class="dashboard-header-copy">
            <span class="dashboard-badge">{{ $pageMeta['badge'] }}</span>
            <h1 class="dashboard-title">{{ $pageMeta['title'] }}</h1>
            <p class="dashboard-subtitle">{{ $pageMeta['subtitle'] }}</p>
        </div>

        <div class="dashboard-header-side">
            <a href="{{ route('setting') }}" class="admin-profile">
                <div class="admin-profile-copy">
                    <p class="admin-profile-name">{{ $displayAdminName }}</p>
                    <p class="admin-profile-role">{{ $displayAdminRole }}</p>
                </div>
                @if ($profileImageUrl)
                    <img src="{{ $profileImageUrl }}" alt="Logo company" class="admin-avatar admin-avatar-logo">
                @else
                    <div class="admin-avatar">{{ $avatarLetter }}</div>
                @endif
            </a>
        </div>
    </div>
</header>
