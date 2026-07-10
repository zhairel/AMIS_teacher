<?php

use App\Http\Controllers\TeacherAuthController;
use App\Http\Controllers\TeacherPortalController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [TeacherAuthController::class, 'showLogin'])->name('teacher.login');
    Route::post('/login', [TeacherAuthController::class, 'login'])->name('teacher.login.store');
    Route::post('/login/change-password', [TeacherAuthController::class, 'changePassword'])->name('teacher.login.change-password.store');
    Route::get('/auth/microsoft/teacher', [TeacherAuthController::class, 'redirectMicrosoft'])->name('teacher.login.microsoft.redirect');
    Route::get('/auth/microsoft/teacher/callback', [TeacherAuthController::class, 'callbackMicrosoft'])->name('teacher.login.microsoft.callback');
});

// Public ZKTeco Attendance Parser & Report
Route::get('/attendance', [App\Http\Controllers\AttendanceController::class, 'index'])->name('teacher.attendance');
Route::post('/attendance/import', [App\Http\Controllers\AttendanceController::class, 'import'])->name('teacher.attendance.import');
Route::post('/attendance/users', [App\Http\Controllers\AttendanceController::class, 'storeUser'])->name('teacher.attendance.users.store');
Route::post('/attendance/users/{id}/delete', [App\Http\Controllers\AttendanceController::class, 'deleteUser'])->name('teacher.attendance.users.delete');
Route::get('/attendance/users/download', [App\Http\Controllers\AttendanceController::class, 'downloadUsers'])->name('teacher.attendance.users.download');
Route::post('/attendance/link', [App\Http\Controllers\AttendanceController::class, 'linkBiometricProfile'])->name('teacher.attendance.link');
Route::post('/attendance/remarks', [App\Http\Controllers\AttendanceController::class, 'storeRemark'])->name('teacher.attendance.remarks.store');

Route::middleware('teacher')->group(function () {
    Route::post('/logout', [TeacherAuthController::class, 'logout'])->name('teacher.logout');

    Route::get('/dashboard', [TeacherPortalController::class, 'dashboard'])->name('teacher.dashboard');
    Route::get('/subjects', [TeacherPortalController::class, 'subjects'])->name('teacher.subjects');
    Route::post('/subjects', [TeacherPortalController::class, 'storeSubject'])->name('teacher.subjects.store');
    Route::get('/subjects/{subject}', [TeacherPortalController::class, 'subjectWorkspace'])->name('teacher.subjects.workspace');
    Route::post('/subjects/{subject}/attendance', [TeacherPortalController::class, 'storeStudentAttendance'])->name('teacher.subjects.attendance.store');
    Route::post('/materials', [TeacherPortalController::class, 'storeMaterial'])->name('teacher.materials.store');

    Route::get('/meetings', [TeacherPortalController::class, 'meetings'])->name('teacher.meetings');
    Route::post('/meetings', [TeacherPortalController::class, 'storeMeeting'])->name('teacher.meetings.store');
    Route::post('/meetings/{id}/status', [TeacherPortalController::class, 'updateMeetingStatus'])->name('teacher.meetings.status');
    Route::post('/meetings/{id}/link', [TeacherPortalController::class, 'updateMeetingLink'])->name('teacher.meetings.link');
    Route::delete('/meetings/{id}', [TeacherPortalController::class, 'deleteMeeting'])->name('teacher.meetings.delete');

    Route::get('/grades', [TeacherPortalController::class, 'grades'])->name('teacher.grades');
    Route::post('/grades/assessments', [TeacherPortalController::class, 'storeAssessment'])->name('teacher.assessments.store');
    Route::post('/grades/scores', [TeacherPortalController::class, 'storeScores'])->name('teacher.grades.scores.store');

    Route::get('/students', [TeacherPortalController::class, 'students'])->name('teacher.students');
    Route::get('/ebook', [TeacherPortalController::class, 'ebook'])->name('teacher.ebook');
    Route::post('/ebook', [TeacherPortalController::class, 'storeEbook'])->name('teacher.ebook.store');
    Route::post('/ebook/{id}/delete', [TeacherPortalController::class, 'deleteEbook'])->name('teacher.ebook.delete');
    Route::get('/ebook/{id}/read', [TeacherPortalController::class, 'readEbook'])->name('teacher.ebook.read');

    Route::get('/announcements', [TeacherPortalController::class, 'announcements'])->name('teacher.announcements');
    Route::post('/announcements', [TeacherPortalController::class, 'storeAnnouncement'])->name('teacher.announcements.store');
    Route::post('/announcements/{id}/update', [TeacherPortalController::class, 'updateAnnouncement'])->name('teacher.announcements.update');
    Route::delete('/announcements/{id}', [TeacherPortalController::class, 'deleteAnnouncement'])->name('teacher.announcements.delete');

    // Settings & Microsoft Link
    Route::get('/settings', [TeacherPortalController::class, 'settings'])->name('teacher.settings');
    Route::post('/settings/password', [TeacherPortalController::class, 'updatePassword'])->name('teacher.settings.password');
    Route::get('/settings/microsoft/connect', [TeacherAuthController::class, 'connectMicrosoft'])->name('teacher.settings.microsoft.connect');
    Route::post('/settings/microsoft/disconnect', [TeacherAuthController::class, 'disconnectMicrosoft'])->name('teacher.settings.microsoft.disconnect');
});
