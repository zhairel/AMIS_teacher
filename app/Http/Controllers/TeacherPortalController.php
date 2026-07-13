<?php

namespace App\Http\Controllers;

use App\DTOs\AnnouncementData;
use App\DTOs\AssessmentData;
use App\DTOs\MeetingData;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\StoreAssessmentRequest;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\StoreMeetingRequest;
use App\Http\Requests\StoreScoresRequest;
use App\Http\Requests\StoreSubjectRequest;
use App\Models\User;
use App\Services\TeacherPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherPortalController extends Controller
{
    protected TeacherPortalService $portalService;

    public function __construct(TeacherPortalService $portalService)
    {
        $this->portalService = $portalService;
    }

    public function dashboard(Request $request)
    {
        $data = $this->portalService->getPortalData($request);

        return view('teacher.dashboard', [
            'data' => $data,
            'subjects' => collect($data['subjects']),
            'meetings' => collect($data['meetings']),
            'assessments' => collect($data['assessments']),
            'students' => collect($data['students']),
            'announcements' => collect($data['announcements']),
        ]);
    }

    public function switchAcademicContext(Request $request)
    {
        $validated = $request->validate(['context' => ['required', 'string', 'max:80', 'regex:/^(subject|adviser):[0-9]+$/']]);
        $allowed = $this->portalService->availableAcademicContexts($request);
        abort_unless($allowed->pluck('key')->contains($validated['context']), 403, 'This Academic assignment is not assigned to your account.');

        $request->session()->put('teacher_academic_context', $validated['context']);

        $selected = $allowed->firstWhere('key', $validated['context']);

        return redirect()->route('teacher.dashboard')->with('success', 'Academic access switched to '.$selected['label'].'.');
    }

    public function subjects(Request $request)
    {
        $data = $this->portalService->getPortalData($request);

        return view('teacher.subjects', [
            'subjects' => collect($data['subjects']),
            'students' => collect($data['students']),
            'materials' => collect($data['materials']),
            'meetings' => collect($data['meetings']),
            'announcements' => collect($data['announcements']),
        ]);
    }

    public function subjectWorkspace(Request $request, string $subject)
    {
        return view('teacher.subject-workspace', $this->portalService->workspace($request, $subject));
    }

    public function storeSubject(StoreSubjectRequest $request)
    {
        try {
            $this->portalService->createClassAndChannels($request, $request->validated());

            return redirect()->route('teacher.subjects')->with('success', 'Class and Channels successfully created and provisioned on MS Teams!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function meetings(Request $request)
    {
        $data = $this->portalService->getPortalData($request);

        return view('teacher.meetings', [
            'subjects' => collect($data['subjects']),
            'meetings' => collect($data['meetings']),
        ]);
    }

    public function storeMeeting(StoreMeetingRequest $request)
    {
        $dto = MeetingData::fromArray($request->validated());
        $this->portalService->storeMeeting($request, $dto);

        return redirect()->route('teacher.meetings')->with('success', 'Meeting saved.');
    }

    public function updateMeetingStatus(Request $request, $id)
    {
        $teacherKey = \Illuminate\Support\Str::slug($request->session()->get('teacher_name', 'teacher'));
        $meeting = \App\Models\SubjectMeeting::where('id', $id)
            ->where('teacher_key', $teacherKey)
            ->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:scheduled,live,draft,completed,Scheduled,Live,Draft,Completed'],
        ]);

        $meeting->update([
            'status' => strtolower($validated['status']),
        ]);

        return back()->with('success', 'Meeting status updated.');
    }

    public function updateMeetingLink(Request $request, $id)
    {
        $teacherKey = \Illuminate\Support\Str::slug($request->session()->get('teacher_name', 'teacher'));
        $meeting = \App\Models\SubjectMeeting::where('id', $id)
            ->where('teacher_key', $teacherKey)
            ->firstOrFail();

        $validated = $request->validate([
            'link' => ['required', 'url', 'max:255'],
        ]);

        $meeting->update([
            'meeting_url' => $validated['link'],
        ]);

        return back()->with('success', 'Meeting link updated.');
    }

    public function deleteMeeting(Request $request, $id)
    {
        $teacherKey = \Illuminate\Support\Str::slug($request->session()->get('teacher_name', 'teacher'));
        $meeting = \App\Models\SubjectMeeting::where('id', $id)
            ->where('teacher_key', $teacherKey)
            ->firstOrFail();

        $meeting->delete();

        return back()->with('success', 'Meeting deleted.');
    }

    public function storeMaterial(StoreMaterialRequest $request)
    {
        $this->portalService->storeMaterial($request, $request->validated());

        return back()->with('success', 'Learning material published.');
    }

    public function grades(Request $request)
    {
        $data = $this->portalService->getPortalData($request);
        $subjects = collect($data['subjects'])->filter(fn ($subject) => ! empty($subject['section_subject_id']))->values();
        $selectedSubjectId = (string) $request->query('subject', $subjects->first()['id'] ?? '');
        $selectedSubject = $subjects->firstWhere('id', $selectedSubjectId);

        if (! $selectedSubject && $subjects->isNotEmpty()) {
            $selectedSubject = $subjects->first();
            $selectedSubjectId = $selectedSubject['id'];
        }

        $assessments = collect($data['assessments'])
            ->where('subject_id', $selectedSubjectId)
            ->values();

        return view('teacher.grades', [
            'subjects' => $subjects,
            'selectedSubjectId' => $selectedSubjectId,
            'selectedSubject' => $selectedSubject,
            'students' => collect($data['students'])
                ->where('section_subject_id', $selectedSubject['section_subject_id'] ?? null)
                ->values(),
            'assessments' => $assessments,
            'scores' => $data['scores'],
            'submission' => $selectedSubject
                ? $this->portalService->gradeSubmissionFor($request, $selectedSubject['section_subject_id'])
                : null,
        ]);
    }

    public function storeAssessment(StoreAssessmentRequest $request)
    {
        $dto = AssessmentData::fromArray($request->validated());
        $this->portalService->storeAssessment($request, $dto);

        return redirect()->route('teacher.grades', ['subject' => $dto->subjectId])->with('success', 'Assessment added.');
    }

    public function storeScores(StoreScoresRequest $request)
    {
        $validated = $request->validated();
        $this->portalService->storeScores($request, $validated['subject_id'], $validated['scores'] ?? []);

        return redirect()->route('teacher.grades', ['subject' => $validated['subject_id']])->with('success', 'Scores saved.');
    }

    public function submitGrades(Request $request, string $subject)
    {
        $this->portalService->submitGrades($request, $subject);

        return redirect()->route('teacher.grades', ['subject' => $subject])->with('success', 'Grades submitted for Academic review.');
    }

    public function exportGrades(Request $request, string $subject)
    {
        $workspace = $this->portalService->workspace($request, $subject);
        $data = $this->portalService->getPortalData($request);
        $assessments = collect($data['assessments'])->where('subject_id', $subject)->values();
        $students = collect($workspace['subjectStudents']);
        $scores = $data['scores'];

        return response()->streamDownload(function () use ($workspace, $assessments, $students, $scores) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_merge(['Student Number', 'Student'], $assessments->pluck('title')->all(), ['Average']));
            foreach ($students as $student) {
                $percentages = [];
                $row = [$student['student_no'], $student['name']];
                foreach ($assessments as $assessment) {
                    $score = $scores[$student['id'].':'.$assessment['id']] ?? null;
                    $row[] = $score;
                    if ($score !== null && $score !== '') {
                        $percentages[] = ((float) $score / (float) $assessment['max_score']) * 100;
                    }
                }
                $row[] = $percentages ? round(array_sum($percentages) / count($percentages), 2) : null;
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'gradebook-'.\Illuminate\Support\Str::slug($workspace['subject']['name']).'.csv', ['Content-Type' => 'text/csv']);
    }

    public function students(Request $request)
    {
        $data = $this->portalService->getPortalData($request);
        $advisorySectionIds = $this->portalService->advisorySectionsFor($request);

        // Get actual Section models for advisory sections
        $advisorySections = \App\Models\Section::whereIn('id', $advisorySectionIds)->get()->map(function ($section) use ($data) {
            return [
                'title' => 'Advisory: ' . $section->section_title . ' (' . $section->learning_mode . ')',
                'students' => collect($data['students'])->where('section_id', $section->id)->unique('id')->values(),
            ];
        })->filter(fn($sec) => $sec['students']->isNotEmpty());

        // Get subjects sections
        $subjectSections = collect($data['subjects'])->map(function ($subject) use ($data) {
            $sectionTitle = str_replace($subject['grade'] . ' - ', '', $subject['section'] ?? '');
            $title = 'Subject: ' . $subject['grade'] . ' - ' . $subject['name'];
            if (!empty($sectionTitle)) {
                $title .= ' (' . $sectionTitle . ')';
            }
            return [
                'title' => $title,
                'students' => collect($data['students'])->where('section_id', $subject['section_id'])->values(),
            ];
        })->filter(fn($sec) => $sec['students']->isNotEmpty());

        return view('teacher.students', [
            'advisorySections' => $advisorySections,
            'subjectSections' => $subjectSections,
            'totalStudentsCount' => collect($data['students'])->unique('id')->count(),
        ]);
    }

    public function announcements(Request $request)
    {
        $data = $this->portalService->getPortalData($request);
        return view('teacher.announcements', [
            'subjects' => $data['subjects'],
            'announcements' => collect($data['announcements']),
        ]);
    }

    public function storeAnnouncement(StoreAnnouncementRequest $request)
    {
        $dto = AnnouncementData::fromArray($request->validated());
        $this->portalService->storeAnnouncement($request, $dto);

        return redirect()->route('teacher.announcements')->with('success', 'Announcement posted.');
    }

    public function updateAnnouncement(Request $request, $id)
    {
        $teacherKey = \Illuminate\Support\Str::slug($request->session()->get('teacher_name', 'teacher'));
        $announcement = \App\Models\SubjectAnnouncement::where('id', $id)
            ->where('teacher_key', $teacherKey)
            ->firstOrFail();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'audience' => ['required', 'string', 'max:120'],
        ]);

        $announcement->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'audience' => $validated['audience'],
        ]);

        return back()->with('success', 'Announcement updated successfully.');
    }

    public function deleteAnnouncement(Request $request, $id)
    {
        $teacherKey = \Illuminate\Support\Str::slug($request->session()->get('teacher_name', 'teacher'));
        $announcement = \App\Models\SubjectAnnouncement::where('id', $id)
            ->where('teacher_key', $teacherKey)
            ->firstOrFail();

        $announcement->delete();

        return back()->with('success', 'Announcement deleted successfully.');
    }

    public function settings(Request $request)
    {
        return view('teacher.coming-soon', [
            'heading' => 'Settings',
            'icon' => 'settings',
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('email', session('teacher_email'))->first();
        if (! $user || ! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password changed successfully!');
    }

    public function storeStudentAttendance(Request $request, string $subjectId)
    {
        $teacherKey = \Illuminate\Support\Str::slug($request->session()->get('teacher_name', 'teacher'));
        $data = $this->portalService->workspace($request, $subjectId);
        $subject = $data['subject'];

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'attendance' => ['required', 'array'],
            'attendance.*.student_id' => ['required', 'integer'],
            'attendance.*.status' => ['required', 'string', 'in:Present,Late,Excused,Absent'],
            'attendance.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($validated['attendance'] as $item) {
            \App\Models\StudentAttendance::updateOrCreate(
                [
                    'student_id' => $item['student_id'],
                    'date' => $validated['date'],
                    'section_subject_id' => $subject['section_subject_id'],
                ],
                [
                    'subject_id' => $subject['subject_id'],
                    'status' => $item['status'],
                    'remarks' => $item['remarks'] ?? null,
                    'teacher_key' => $teacherKey,
                ]
            );
        }

        return redirect()->route('teacher.subjects.workspace', ['subject' => $subjectId, 'tab' => 'attendance', 'attendance_date' => $validated['date']])
            ->with('success', 'Student attendance recorded successfully.');
    }

    public function ebook(Request $request)
    {
        $teacherKey = \Illuminate\Support\Str::slug($request->session()->get('teacher_name', 'teacher'));
        $teacherEmail = $request->session()->get('teacher_email');
        $teacherName = $request->session()->get('teacher_name');

        $hasAssignments = \App\Models\TeacherSubjectAssignment::where(function ($query) use ($teacherKey, $teacherEmail) {
                $query->where('teacher_key', $teacherKey);
                if ($teacherEmail) {
                    $query->orWhere('teacher_email', $teacherEmail);
                }
            })
            ->where('status', 'active')
            ->exists();

        $hasSections = \App\Models\SectionSubject::where(function ($query) use ($teacherName) {
                $query->where('teacher_name', $teacherName)
                    ->orWhere('teacher_name', 'like', '%'.trim((string) $teacherName).'%');
            })
            ->exists();

        $isAssignedTeacher = ($hasAssignments || $hasSections);

        // Fetch user matching the teacher email
        $user = \App\Models\User::where('email', $teacherEmail)->first();
        $userId = $user ? $user->id : null;

        // Fetch all ebooks sorted by title naturally
        $ebooks = \App\Models\Ebook::with('creator')->get()->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)->values();

        // Filter teacher's own uploads if logged in
        $myUploads = $userId ? $ebooks->where('created_by', $userId)->values() : collect();

        // Grade levels from AdminBookController
        $gradeLevels = [
            'Kindergarten',
            'Kinder 1',
            'Kinder 2',
            'Grade 1',
            'Grade 2',
            'Grade 3',
            'Grade 4',
            'Grade 5',
            'Grade 6',
            'Grade 7',
            'Grade 8',
            'Grade 9',
            'Grade 10',
            'K11',
            'K12',
        ];

        return view('teacher.ebook', [
            'heading' => 'eBook',
            'isAssignedTeacher' => $isAssignedTeacher,
            'ebooks' => $ebooks,
            'myUploads' => $myUploads,
            'gradeLevels' => $gradeLevels,
        ]);
    }

    public function storeEbook(Request $request)
    {
        $teacherEmail = $request->session()->get('teacher_email');
        $user = \App\Models\User::where('email', $teacherEmail)->first();

        if (!$user || $user->role !== 'teacher') {
            return redirect()->back()->withErrors(['error' => 'Unauthorized action.']);
        }

        // Verify if teacher is assigned subject teacher
        $teacherKey = \Illuminate\Support\Str::slug($request->session()->get('teacher_name', 'teacher'));
        $teacherName = $request->session()->get('teacher_name');

        $hasAssignments = \App\Models\TeacherSubjectAssignment::where(function ($query) use ($teacherKey, $teacherEmail) {
                $query->where('teacher_key', $teacherKey);
                if ($teacherEmail) {
                    $query->orWhere('teacher_email', $teacherEmail);
                }
            })
            ->where('status', 'active')
            ->exists();

        $hasSections = \App\Models\SectionSubject::where(function ($query) use ($teacherName) {
                $query->where('teacher_name', $teacherName)
                    ->orWhere('teacher_name', 'like', '%'.trim((string) $teacherName).'%');
            })
            ->exists();

        if (!$hasAssignments && !$hasSections) {
            return redirect()->back()->withErrors(['error' => 'Only assigned subject teachers can upload ebooks.']);
        }

        $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'grade_level'    => 'required|string|max:255',
            'pdf_file'       => 'required|file|mimes:pdf|max:51200', // max 50MB
            'cover_image'    => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            'is_downloadable'=> 'nullable|boolean',
            'status'         => 'required|string|in:draft,published',
        ]);

        // 1. Upload private PDF file
        $pdfPath = null;
        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            $pdfPath = $file->storeAs('private/ebooks', $filename, 'local');
        }

        // 2. Generate cover or use default / manual cover
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $uuid = \Illuminate\Support\Str::uuid();
            $filename = "{$uuid}.webp";
            $coversDir = \Illuminate\Support\Facades\Storage::disk('public')->path('covers');
            if (!is_dir($coversDir)) {
                mkdir($coversDir, 0755, true);
            }
            $targetPath = "{$coversDir}/{$filename}";
            $tempPath = $file->getRealPath();

            $resized = false;
            $info = @getimagesize($tempPath);
            if ($info) {
                $mime = $info['mime'];
                $sourceImage = null;
                if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
                    $sourceImage = @imagecreatefromjpeg($tempPath);
                } elseif ($mime === 'image/png') {
                    $sourceImage = @imagecreatefrompng($tempPath);
                } elseif ($mime === 'image/webp') {
                    $sourceImage = @imagecreatefromwebp($tempPath);
                }

                if ($sourceImage) {
                    $origWidth = imagesx($sourceImage);
                    $origHeight = imagesy($sourceImage);
                    $targetWidth = 400;
                    if ($origWidth > $targetWidth) {
                        $targetHeight = (int) (($origHeight / $origWidth) * $targetWidth);
                    } else {
                        $targetWidth = $origWidth;
                        $targetHeight = $origHeight;
                    }

                    $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
                    if ($targetImage) {
                        if ($mime === 'image/png' || $mime === 'image/webp') {
                            imagealphablending($targetImage, false);
                            imagesavealpha($targetImage, true);
                            $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
                            imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $transparent);
                        }

                        if (imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $origWidth, $origHeight)) {
                            if (function_exists('imagewebp')) {
                                $resized = @imagewebp($targetImage, $targetPath, 60);
                            }
                        }
                        imagedestroy($targetImage);
                    }
                    imagedestroy($sourceImage);
                }
            }

            if ($resized) {
                $coverPath = "covers/{$filename}";
            } else {
                $ext = $file->getClientOriginalExtension();
                $fallbackName = "{$uuid}.{$ext}";
                $file->move($coversDir, $fallbackName);
                $coverPath = "covers/{$fallbackName}";
            }
        }

        \App\Models\Ebook::create([
            'title'            => $request->title,
            'description'      => $request->description,
            'grade_level'      => trim($request->grade_level ?? ''),
            'file_path'        => $pdfPath,
            'cover_image_path' => $coverPath,
            'is_downloadable'  => $request->has('is_downloadable') || $request->boolean('is_downloadable'),
            'status'           => $request->status,
            'created_by'       => $user->id,
        ]);

        return redirect()->route('teacher.ebook')->with('success', 'E-Book uploaded successfully.');
    }

    public function deleteEbook(Request $request, $id)
    {
        $teacherEmail = $request->session()->get('teacher_email');
        $user = \App\Models\User::where('email', $teacherEmail)->first();

        if (!$user) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized action.']);
        }

        $book = \App\Models\Ebook::findOrFail($id);

        if ($book->created_by !== $user->id && $user->role !== 'admin') {
            return redirect()->back()->withErrors(['error' => 'You can only delete eBooks you have uploaded.']);
        }

        if ($book->cover_image_path) {
            $coverFile = \Illuminate\Support\Facades\Storage::disk('public')->path($book->cover_image_path);
            if (file_exists($coverFile)) {
                @unlink($coverFile);
            }
        }

        if ($book->file_path) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($book->file_path);
        }

        $book->delete();

        return redirect()->route('teacher.ebook')->with('success', 'E-Book deleted successfully.');
    }

    public function readEbook(Request $request, $id)
    {
        $teacherEmail = $request->session()->get('teacher_email');
        $user = \App\Models\User::where('email', $teacherEmail)->first();

        if (!$user) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized action.']);
        }

        $book = \App\Models\Ebook::findOrFail($id);

        $token = \Illuminate\Support\Str::random(64);
        \Illuminate\Support\Facades\DB::table('sso_tokens')->insert([
            'token'         => $token,
            'user_id'       => $user->id,
            'source_portal' => 'amis_teacher',
            'expires_at'    => now()->addSeconds(30),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $ebookPortalUrl = rtrim(config('services.ebook.url', 'https://ebook.amis.edu.ph'), '/');
        $redirectUrl = "{$ebookPortalUrl}/sso/login?sso_token={$token}&redirect=/books/{$book->id}";

        return redirect()->away($redirectUrl);
    }

    public function digitalId(Request $request)
    {
        $myBiometricId = $request->query('biometric_id') ?: $request->query('id');
        $searchName = $request->query('search_name');

        $selectedBiometricUser = null;
        $isAuthenticated = (bool) $request->session()->get('teacher_portal_authenticated');
        $loggedInEmail = $request->session()->get('teacher_email');

        // 1. If not specified, but logged in, get current teacher's biometric ID
        if (!$myBiometricId && !$searchName && $isAuthenticated && $loggedInEmail) {
            $user = User::where('email', $loggedInEmail)->first();
            $myBiometricId = $user ? $user->biometric_id : null;
        }

        // 2. Look up by search name if query is active
        if (!$myBiometricId && $searchName) {
            $matchedUser = \Illuminate\Support\Facades\DB::table('zk_users')
                ->where('name', 'like', '%' . $searchName . '%')
                ->first();
            if ($matchedUser) {
                $myBiometricId = $matchedUser->employee_id;
            } else {
                return redirect()->route('teacher.id')->with('error', 'Faculty member not found in biometric directory.');
            }
        }

        // 3. Load user details
        if ($myBiometricId) {
            $selectedBiometricUser = \Illuminate\Support\Facades\DB::table('zk_users')->where('employee_id', $myBiometricId)->first();
        }

        $displayName = 'Faculty Member';
        $departmentName = 'Islamic School and Arabic Language Department';

        if ($selectedBiometricUser) {
            $displayName = $selectedBiometricUser->name;
            // Resolve department name
            $dept = \Illuminate\Support\Facades\DB::table('zk_departments')->where('id', $selectedBiometricUser->department_id)->first();
            $departmentName = $dept ? $dept->name : 'Main Department';
        } elseif ($isAuthenticated) {
            $displayName = $request->session()->get('teacher_name', 'Faculty Member');
            $departmentName = $request->session()->get('teacher_dept', 'Islamic School and Arabic Language Department');
        }

        // 4. Generate barcode & QR code urls
        $barcodeUrl = null;
        $qrCodeUrl = null;
        if ($myBiometricId) {
            // Standard Code128 Barcode containing the raw Employee ID PIN
            $barcodeUrl = 'https://bwipjs-api.metafloor.com/?bcid=code128&text=' . urlencode($myBiometricId) . '&scale=3&rotate=N&includecheck=true';
            
            // Standard High-Contrast QR Code containing the raw Employee ID PIN (for optical scanners / camera attendance loggers)
            $qrCodeUrl = 'https://quickchart.io/qr?text=' . urlencode($myBiometricId) . '&dark=000000&light=ffffff&margin=1&format=png&size=300';
        }

        // Fetch all zk users for picker fallback (in case they want to search or link)
        $zkUsers = \Illuminate\Support\Facades\DB::table('zk_users')->orderBy('name')->get()->map(fn($u) => (array)$u)->toArray();

        return view('teacher.digital-id', [
            'myBiometricId' => $myBiometricId,
            'selectedBiometricUser' => $selectedBiometricUser,
            'displayName' => $displayName,
            'departmentName' => $departmentName,
            'barcodeUrl' => $barcodeUrl,
            'qrCodeUrl' => $qrCodeUrl,
            'zkUsers' => $zkUsers,
            'isAuthenticated' => $isAuthenticated,
        ]);
    }
}
