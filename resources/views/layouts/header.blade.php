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
    $avatarLetter = strtoupper(substr($admin?->name ?? 'A', 0, 1));
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
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Company Logo" class="admin-company-logo">
                @endif
                <div>
                    <p class="admin-profile-name">{{ $admin?->name ?? 'Admin Devgen' }}</p>
                    <p class="admin-profile-role">{{ $setting?->company_name ?? 'Administrator' }}</p>
                </div>
                <div class="admin-avatar">{{ $avatarLetter }}</div>
            </a>
        </div>
    </div>
</header>
