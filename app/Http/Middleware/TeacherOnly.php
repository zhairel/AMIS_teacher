<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\TeacherAuthService;
use App\Services\TeacherPortalService;
use Closure;
use Illuminate\Http\Request;

class TeacherOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->session()->get('teacher_portal_authenticated')) {
            return redirect()->route('teacher.login');
        }

        $email = $request->session()->get('teacher_email');

        // If logged in via traditional/Microsoft, validate against the DB on each request
        if ($email) {
            $user = User::where('email', $email)->first();

            if (
                ! $user ||
                $user->role !== 'teacher' ||
                ($user->account_status ?? 'verified') !== 'verified' ||
                ! str_ends_with(strtolower($email), '@amis.edu.ph')
            ) {
                // Logout local session
                $request->session()->forget(['teacher_portal_authenticated', 'teacher_name', 'teacher_email', 'teacher_portal_data']);
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Store error message in cookie
                $errorMessage = 'Access denied. This account is not allowed to access the Faculty Portal.';
                $cookie = cookie('microsoft_auth_error', $errorMessage, 5);

                // Redirect to Microsoft global logout endpoint
                $tenantId = config('services.azure.tenant_id');
                $redirectUri = config('services.azure.redirect_uri_teacher');
                $logoutUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/logout?".http_build_query([
                    'post_logout_redirect_uri' => $redirectUri,
                ]);

                return redirect()->away($logoutUrl)->withCookie($cookie);
            }

            if (! $request->session()->has('teacher_dept') || $request->session()->get('teacher_name') === 'AMIS Teacher') {
                $authService = resolve(TeacherAuthService::class);
                $resolved = $authService->resolveTeacherDetails($email);
                $request->session()->put('teacher_name', $resolved['name'] ?? $user->name);
                $request->session()->put('teacher_dept', $resolved['dept'] ?? 'Islamic School and Arabic Language Department');
            }
        }

        $contexts = resolve(TeacherPortalService::class)->availableAcademicContexts($request);
        $activeContext = $request->session()->get('teacher_academic_context');
        if (! $activeContext || ! $contexts->pluck('key')->contains($activeContext)) {
            $request->session()->put('teacher_academic_context', $contexts->first()['key'] ?? null);
        }

        return $next($request);
    }
}
