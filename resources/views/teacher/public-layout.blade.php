<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'AMIS Staff Attendance Inquiry' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/AMIS_Logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            color: #0f172a;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }
        .public-wrapper {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .public-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        .public-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #0f172a;
        }
        .public-brand img {
            height: 40px;
            width: auto;
        }
        .public-brand-text strong {
            display: block;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.2;
        }
        .public-brand-text small {
            font-size: 11px;
            color: #64748b;
            font-weight: 550;
        }
        .portal-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #0f766e;
            background-color: #f0fdfa;
            border: 1px solid #ccfbf1;
            text-decoration: none;
            transition: all 0.2s;
        }
        .portal-btn:hover {
            background-color: #e6fffa;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="public-wrapper">
        <header class="public-header">
            <a href="/" class="public-brand">
                <img src="{{ asset('images/AMIS_Logo.png') }}" alt="AMIS">
                <div class="public-brand-text">
                    <strong>AMIS</strong>
                    <small>Faculty Portal</small>
                </div>
            </a>
            <a href="{{ route('teacher.login') }}" class="portal-btn">
                <i data-lucide="log-in" style="width: 16px; height: 16px;"></i>
                <span>Portal Login</span>
            </a>
        </header>

        @if(session('success'))
            <div class="teacher-alert" style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px; padding: 12px 16px; background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 8px; font-size: 13px; font-weight: 600;">
                <i data-lucide="check-circle" style="color: #15803d; width: 18px; height: 18px;"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="teacher-alert" style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px; padding: 12px 16px; background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 8px; font-size: 13px; font-weight: 600;">
                <i data-lucide="alert-circle" style="color: #b91c1c; width: 18px; height: 18px;"></i>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    <script>
        const refreshIcons = () => window.lucide?.createIcons();
        refreshIcons();
        document.addEventListener('DOMContentLoaded', refreshIcons);
        window.addEventListener('load', refreshIcons);
    </script>
</body>
</html>
