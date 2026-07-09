<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - AMIS Faculty Portal</title>
    <link rel="icon" type="image/png" href="{{ asset('images/AMIS_Logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800;900&family=Noto+Naskh+Arabic:wght@600;700&display=swap" rel="stylesheet">
</head>
<body class="teacher-login-body">
    <main class="teacher-login">
        <section class="teacher-login-grid">
            <div class="teacher-login-identity">
                <div class="teacher-login-lockup">
                    <img src="{{ asset('images/AMIS_Logo.png') }}" alt="AMIS Logo">
                    <div class="teacher-login-wordmark">
                        <p class="teacher-login-arabic" lang="ar" dir="rtl">المدرسة الإسلامية المنورة</p>
                        <h1>AL MUNAWWARA ISLAMIC SCHOOL</h1>
                        <strong>Faculty Portal</strong>
                    </div>
                </div>
                <p>Manage subjects, meetings, grades, and class updates from one focused portal.</p>
            </div>

            <div class="teacher-login-panel">
                <div class="teacher-login-brand">
                    <div>
                        <h1>Sign in</h1>
                        <p>Use your AMIS faculty account to continue.</p>
                    </div>
                </div>

                @if($errors->any())
                    <div class="teacher-error">
                        <i data-lucide="alert-circle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 24px;">
                    <a href="{{ route('teacher.login.microsoft.redirect') }}" style="display: flex; align-items: center; justify-content: center; gap: 12px; height: 50px; border-radius: 8px; background: #059669; color: #fff; text-decoration: none; font-size: 15px; font-weight: 600; transition: background 140ms var(--ease); box-shadow: var(--shadow-sm);" onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">
                        <svg viewBox="0 0 23 23" style="width: 20px; height: 20px;">
                            <path fill="#f25022" d="M1 1h10v10H1z"/>
                            <path fill="#7fba00" d="M12 1h10v10H12z"/>
                            <path fill="#00a4ef" d="M1 12h10v10H1z"/>
                            <path fill="#ffb900" d="M12 12h10v10H12z"/>
                        </svg>
                        Sign in with Microsoft
                    </a>
                </div>


            </div>
        </section>
    </main>
    <script>window.lucide?.createIcons();</script>
</body>
</html>
