<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'AMIS Faculty Portal' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/AMIS_Logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="teacher-body">
<div class="teacher-shell">
    <aside class="teacher-sidebar">
        <div class="teacher-sidebar-top">
            <a href="{{ route('teacher.dashboard') }}" class="teacher-brand">
                <img src="{{ asset('images/AMIS_Logo.png') }}" alt="AMIS">
                <div class="teacher-brand-text">
                    <strong>AMIS</strong>
                    <small>Faculty Portal</small>
                </div>
            </a>

            @php
                $menu = [
                    ['route' => 'teacher.dashboard', 'icon' => 'gauge', 'label' => 'Dashboard', 'tone' => 'emerald'],
                    ['route' => 'teacher.subjects', 'icon' => 'book-open', 'label' => 'Classroom Workspace', 'tone' => 'sky'],
                    ['route' => 'teacher.ebook', 'icon' => 'book-open-check', 'label' => 'eBook', 'tone' => 'indigo'],
                    ['route' => 'teacher.meetings', 'icon' => 'video', 'label' => 'Meetings', 'tone' => 'indigo'],
                    ['route' => 'teacher.grades', 'icon' => 'clipboard-list', 'label' => 'Gradebook', 'tone' => 'amber'],
                    ['route' => 'teacher.students', 'icon' => 'users', 'label' => 'Students', 'tone' => 'violet'],
                    ['route' => 'teacher.announcements', 'icon' => 'megaphone', 'label' => 'Announcements', 'tone' => 'rose'],
                    ['route' => 'teacher.attendance', 'icon' => 'clock', 'label' => 'Attendance', 'tone' => 'emerald'],
                    ['route' => 'teacher.settings', 'icon' => 'settings', 'label' => 'Settings', 'tone' => 'indigo'],
                ];
            @endphp

            <div class="teacher-nav-section">
                <span class="teacher-nav-label">Navigation</span>
                <nav class="teacher-nav">
                    @foreach($menu as $item)
                        @php
                            $itemRoute = $item['route'] ?? null;
                            $itemHref = $itemRoute ? route($itemRoute) : $item['href'];
                            $isActive = $itemRoute ? request()->routeIs($itemRoute.'*') : false;
                        @endphp
                        <a href="{{ $itemHref }}" class="teacher-nav-{{ $item['tone'] }} {{ $isActive ? 'active' : '' }}">
                            <span class="teacher-nav-icon-box">
                                <i data-lucide="{{ $item['icon'] }}" class="teacher-nav-icon"></i>
                            </span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>

        <div class="teacher-sidebar-bottom">
            <div class="teacher-profile-card">
                <div class="teacher-avatar">
                    {{ strtoupper(substr(session('teacher_name', 'UA'), 0, 2)) }}
                </div>
                <div class="teacher-profile-info">
                    <strong>{{ session('teacher_name', 'AMIS Teacher') }}</strong>
                    <small>{{ session('teacher_dept') }}</small>
                </div>
                <form method="POST" action="{{ route('teacher.logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="teacher-profile-chevron" title="Sign Out">
                        <i data-lucide="log-out" style="width:14px;height:14px;"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="teacher-main">
        <div class="teacher-container">
            <header class="teacher-topbar">
                <div class="teacher-topbar-start">
                    <div class="teacher-topbar-eyebrow">Al Munawwara Islamic School</div>
                    <h1>{{ $heading ?? 'Faculty Portal' }}</h1>
                </div>
                <div class="teacher-topbar-end">
                    <a href="{{ route('teacher.meetings') }}" class="teacher-icon-btn" aria-label="Meetings">
                        <i data-lucide="calendar-clock"></i>
                    </a>
                    <a href="{{ route('teacher.announcements') }}" class="teacher-icon-btn" aria-label="Announcements">
                        <i data-lucide="bell"></i>
                    </a>
                    <div class="teacher-topbar-divider"></div>
                    <form method="POST" action="{{ route('teacher.logout') }}" class="teacher-logout-form">
                        @csrf
                        <button type="submit" class="teacher-outline-btn"><i data-lucide="log-out"></i> Sign Out</button>
                    </form>
                </div>
            </header>

            @if(session('success'))
                <div class="teacher-alert">
                    <i data-lucide="check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<script>
    const refreshTeacherIcons = () => window.lucide?.createIcons();
    refreshTeacherIcons();
    document.addEventListener('DOMContentLoaded', refreshTeacherIcons);
    window.addEventListener('load', refreshTeacherIcons);
    setTimeout(refreshTeacherIcons, 100);
    setTimeout(refreshTeacherIcons, 500);
</script>
</body>
</html>
