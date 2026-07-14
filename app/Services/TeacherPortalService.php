<?php

namespace App\Services;

use App\DTOs\AnnouncementData;
use App\DTOs\AssessmentData;
use App\DTOs\MeetingData;
use App\Models\ClassAdvisoryAssignment;
use App\Models\GradebookAssessment;
use App\Models\GradebookAuditLog;
use App\Models\GradebookScore;
use App\Models\GradeSubmission;
use App\Models\LearningMaterial;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\StudentSection;
use App\Models\Subject;
use App\Models\SubjectAnnouncement;
use App\Models\SubjectMeeting;
use App\Models\TeacherSubjectAssignment;
use App\Support\EnrollmentStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TeacherPortalService
{
    protected MicrosoftGraphService $graph;

    public function __construct(MicrosoftGraphService $graph)
    {
        $this->graph = $graph;
    }

    public function getPortalData(Request $request): array
    {
        $subjects = $this->subjectsFor($request);
        $subjectIds = $subjects->pluck('subject_id')->filter()->unique();
        $sectionSubjectIds = $subjects->pluck('section_subject_id')->filter()->unique();

        $meetings = SubjectMeeting::query()
            ->where(fn ($query) => $query
                ->whereIn('section_subject_id', $sectionSubjectIds)
                ->orWhereIn('subject_id', $subjectIds))
            ->latest('meeting_date')
            ->get()
            ->map(fn (SubjectMeeting $meeting) => $this->meetingArray($meeting));

        $materials = LearningMaterial::query()
            ->where('visibility', 'published')
            ->where(fn ($query) => $query
                ->whereIn('section_subject_id', $sectionSubjectIds)
                ->orWhereIn('subject_id', $subjectIds))
            ->latest()
            ->get()
            ->map(fn (LearningMaterial $material) => $this->materialArray($material));

        $announcements = SubjectAnnouncement::query()
            ->where(fn ($query) => $query
                ->whereIn('section_subject_id', $sectionSubjectIds)
                ->orWhereIn('subject_id', $subjectIds))
            ->latest('published_at')
            ->get()
            ->map(fn (SubjectAnnouncement $announcement) => $this->announcementArray($announcement));

        $advisorySectionIds = $this->advisorySectionsFor($request);
        $students = $this->studentsFor($subjects, $advisorySectionIds);

        $assessments = GradebookAssessment::with('scores')
            ->whereIn('section_subject_id', $sectionSubjectIds)
            ->orderBy('assessment_date')
            ->get();

        return [
            'subjects' => $subjects->values()->all(),
            'meetings' => $meetings->values()->all(),
            'materials' => $materials->values()->all(),
            'assessments' => $assessments->map(fn ($assessment) => [
                'id' => (string) $assessment->id,
                'subject_id' => 'section-subject-'.$assessment->section_subject_id,
                'title' => $assessment->title,
                'max_score' => $assessment->max_score,
                'date' => $assessment->assessment_date->toDateString(),
                'grading_period' => $assessment->grading_period,
            ])->all(),
            'students' => $students->values()->all(),
            'scores' => $assessments->flatMap(fn ($assessment) => $assessment->scores->mapWithKeys(
                fn ($score) => [$score->student_id.':'.$assessment->id => $score->score]
            ))->all(),
            'announcements' => $announcements->values()->all(),
        ];
    }

    public function workspace(Request $request, string $workspaceId): array
    {
        $data = $this->getPortalData($request);
        $subject = collect($data['subjects'])->firstWhere('id', $workspaceId);
        abort_unless($subject, 404, 'Subject workspace not found.');

        $selectedDate = $request->query('attendance_date', now()->toDateString());
        $subjectAttendances = \App\Models\StudentAttendance::where('section_subject_id', $subject['section_subject_id'])
            ->where('date', $selectedDate)
            ->get();

        return $data + [
            'subject' => $subject,
            'subjectMeetings' => collect($data['meetings'])->where('subject_id', $workspaceId)->values(),
            'subjectMaterials' => collect($data['materials'])->where('subject_id', $workspaceId)->values(),
            'subjectAnnouncements' => collect($data['announcements'])->where('subject_id', $workspaceId)->values(),
            'subjectStudents' => collect($data['students'])->where('section_subject_id', $subject['section_subject_id'])->values(),
            'attendanceDate' => $selectedDate,
            'subjectAttendances' => $subjectAttendances,
        ];
    }

    public function storeMeeting(Request $request, MeetingData $dto): void
    {
        $subject = $this->resolveSubject($request, $dto->subjectId);

        SubjectMeeting::create([
            'subject_id' => $subject['subject_id'],
            'section_subject_id' => $subject['section_subject_id'],
            'teacher_key' => $this->teacherKey($request),
            'teacher_name' => $request->session()->get('teacher_name', 'AMIS Teacher'),
            'teacher_email' => $request->session()->get('teacher_email'),
            'title' => $dto->title,
            'description' => $dto->description,
            'meeting_date' => $dto->date,
            'meeting_time' => $dto->time,
            'duration_minutes' => $dto->duration,
            'meeting_url' => $dto->link ?: ($dto->status->value === 'Live' ? 'https://teams.microsoft.com/' : null),
            'provider' => 'microsoft_teams',
            'status' => Str::lower($dto->status->value),
        ]);
    }

    public function storeMaterial(Request $request, array $data): void
    {
        $subject = $this->resolveSubject($request, $data['subject_id']);
        $file = $request->file('file');
        $path = $file ? $file->store('teacher-materials', 'public') : null;

        LearningMaterial::create([
            'subject_id' => $subject['subject_id'],
            'section_subject_id' => $subject['section_subject_id'],
            'teacher_key' => $this->teacherKey($request),
            'teacher_name' => $request->session()->get('teacher_name', 'AMIS Teacher'),
            'teacher_email' => $request->session()->get('teacher_email'),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $file ? 'file' : 'google_drive',
            'disk' => $file ? 'public' : null,
            'path' => $path,
            'external_url' => $data['external_url'] ?? null,
            'mime_type' => $file?->getClientMimeType(),
            'size_bytes' => $file?->getSize(),
            'visibility' => 'published',
        ]);
    }

    public function storeAssessment(Request $request, AssessmentData $dto): void
    {
        $subject = $this->resolveSubject($request, $dto->subjectId);
        abort_unless($subject['section_subject_id'], 422, 'The subject must be linked to a section.');
        $this->ensureGradebookEditable($request, $subject['section_subject_id']);

        $assessment = GradebookAssessment::create([
            'section_subject_id' => $subject['section_subject_id'],
            'subject_id' => $subject['subject_id'],
            'teacher_key' => $this->teacherKey($request),
            'title' => $dto->title,
            'max_score' => $dto->maxScore,
            'assessment_date' => $dto->date,
            'grading_period' => 'Current',
        ]);
        $this->audit($request, $subject['section_subject_id'], 'assessment_created', 'assessment', $assessment->id);
    }

    public function storeScores(Request $request, string $subjectId, array $studentScores): void
    {
        $subject = $this->resolveSubject($request, $subjectId);
        abort_unless($subject['section_subject_id'], 422, 'The subject must be linked to a section.');
        $this->ensureGradebookEditable($request, $subject['section_subject_id']);
        $assessmentIds = GradebookAssessment::where('section_subject_id', $subject['section_subject_id'])->pluck('id');

        foreach ($studentScores as $studentId => $assessmentScores) {
            foreach ($assessmentScores as $assessmentId => $score) {
                $assessment = GradebookAssessment::whereKey($assessmentId)->whereIn('id', $assessmentIds)->firstOrFail();
                abort_if($score !== null && $score !== '' && (float) $score > $assessment->max_score, 422, 'A score exceeds the assessment maximum.');
                GradebookScore::updateOrCreate(
                    ['assessment_id' => $assessment->id, 'student_id' => (int) $studentId],
                    ['score' => ($score === null || $score === '') ? null : $score, 'teacher_key' => $this->teacherKey($request)]
                );
            }
        }
        $this->audit($request, $subject['section_subject_id'], 'scores_saved');
    }

    public function submitGrades(Request $request, string $workspaceId): GradeSubmission
    {
        $subject = $this->resolveSubject($request, $workspaceId);
        abort_unless($subject['section_subject_id'], 422, 'The subject must be linked to a section.');
        $this->ensureGradebookEditable($request, $subject['section_subject_id']);
        abort_if(GradebookAssessment::where('section_subject_id', $subject['section_subject_id'])->doesntExist(), 422, 'Add at least one assessment before submitting.');

        $submission = GradeSubmission::updateOrCreate(
            ['section_subject_id' => $subject['section_subject_id'], 'grading_period' => 'Current'],
            ['teacher_key' => $this->teacherKey($request), 'status' => 'submitted', 'submitted_at' => now(), 'review_notes' => null]
        );
        $this->audit($request, $subject['section_subject_id'], 'grades_submitted', 'submission', $submission->id);
        return $submission;
    }

    public function gradeSubmissionFor(Request $request, int $sectionSubjectId): GradeSubmission
    {
        return GradeSubmission::firstOrCreate(
            ['section_subject_id' => $sectionSubjectId, 'grading_period' => 'Current'],
            ['teacher_key' => $this->teacherKey($request), 'status' => 'draft']
        );
    }

    private function ensureGradebookEditable(Request $request, int $sectionSubjectId): void
    {
        $submission = GradeSubmission::where('section_subject_id', $sectionSubjectId)->where('grading_period', 'Current')->first();
        abort_if($submission && in_array($submission->status, ['submitted', 'approved', 'locked'], true), 423, 'Grades are not editable while submitted or locked.');
    }

    private function audit(Request $request, int $sectionSubjectId, string $action, ?string $type = null, ?int $id = null): void
    {
        GradebookAuditLog::create(['section_subject_id' => $sectionSubjectId, 'teacher_key' => $this->teacherKey($request), 'action' => $action, 'record_type' => $type, 'record_id' => $id]);
    }

    public function storeAnnouncement(Request $request, AnnouncementData $dto): void
    {
        $subject = $this->resolveSubject($request, $dto->subjectId);

        SubjectAnnouncement::create([
            'subject_id' => $subject['subject_id'],
            'section_subject_id' => $subject['section_subject_id'],
            'teacher_key' => $this->teacherKey($request),
            'teacher_name' => $request->session()->get('teacher_name', 'AMIS Teacher'),
            'teacher_email' => $request->session()->get('teacher_email'),
            'title' => $dto->title,
            'body' => $dto->body,
            'audience' => $dto->audience,
            'published_at' => $dto->date.' '.now()->format('H:i:s'),
        ]);
    }

    public function createClassAndChannels(Request $request, array $data): void
    {
        $grade = $data['grade'];
        $name = $data['name'] ?? null;
        $gender = $data['gender'];
        $mode = $data['mode'];
        $shift = $mode === 'Flexible Online Learning' ? ($data['shift'] ?? null) : null;
        $channels = $data['channels'] ?? [];

        $teacherName = $request->session()->get('teacher_name', 'AMIS Teacher');
        $teacherUpn = $request->session()->get('teacher_email');

        // Construct team name
        if ($grade === 'Kinder 1') {
            $prefix = 'K1';
        } elseif ($grade === 'Kinder 2') {
            $prefix = 'K2';
        } else {
            $prefix = 'G'.str_replace('Grade ', '', $grade);
        }

        $shiftLabel = $shift ? ($shift === '1st Shift' ? '1st Shift' : '2nd Shift') : 'F2F';
        $genderLabel = $gender === 'male' ? 'Boys' : 'Girls';
        $namePart = $name ? " - {$name}" : '';
        $teamName = "{$prefix}{$namePart} [{$genderLabel} & {$shiftLabel}]";

        $msTeamId = null;
        $msTeamUrl = null;

        try {
            $result = $this->graph->createTeam($teamName);
            $msTeamId = $result['id'];
            $msTeamUrl = "https://teams.microsoft.com/l/team/{$msTeamId}";

            // Wait for team to be ready
            $this->graph->waitForTeam($msTeamId);

            // Post welcome card to General channel
            $generalChannelId = $this->graph->getGeneralChannelId($msTeamId);
            if ($generalChannelId) {
                try {
                    $this->graph->postWelcomeCard($msTeamId, $generalChannelId, [
                        'grade_level' => $grade,
                        'learning_mode' => $mode,
                        'shift' => $shift,
                        'gender' => $gender,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Could not post welcome card to General channel: '.$e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to create MS Team [{$teamName}]: ".$e->getMessage());
            throw new \Exception('Failed to create Microsoft Team: '.$e->getMessage());
        }

        // Save section in DB
        $schoolYear = config('services.school.year', '2026-2027');

        $section = Section::create([
            'name' => $name,
            'grade_level' => $grade,
            'learning_mode' => $mode,
            'shift' => $shift,
            'gender' => $gender,
            'school_year' => $schoolYear,
            'ms_team_id' => $msTeamId,
            'ms_team_url' => $msTeamUrl,
        ]);

        // If teacher UPN is available, invite teacher as Team Owner
        if ($msTeamId && $teacherUpn) {
            try {
                $this->graph->addTeamOwner($msTeamId, $teacherUpn);
            } catch (\Exception $e) {
                Log::warning("Could not add teacher [{$teacherUpn}] as Team Owner: ".$e->getMessage());
            }
        }

        // Create subject/channels
        $adminUpn = config('services.microsoft.admin_upn');
        foreach ($channels as $channelName) {
            $channelId = null;
            if ($msTeamId) {
                try {
                    $channelResult = $this->graph->createPrivateChannel($msTeamId, $channelName, $adminUpn);
                    $channelId = $channelResult['id'] ?? null;

                    if ($channelId) {
                        // Post welcome card to private channel
                        try {
                            $this->graph->postWelcomeCard($msTeamId, $channelId, [
                                'grade_level' => $grade,
                                'learning_mode' => $mode,
                                'shift' => $shift,
                                'gender' => $gender,
                                'subject' => $channelName,
                                'teacher' => $teacherName,
                            ]);
                        } catch (\Exception $e) {
                            Log::warning("Could not post welcome card to channel [{$channelName}]: ".$e->getMessage());
                        }

                        // Invite teacher as Channel Owner
                        if ($teacherUpn) {
                            try {
                                $this->graph->addChannelOwner($msTeamId, $channelId, $teacherUpn);
                            } catch (\Exception $e) {
                                Log::warning("Could not invite teacher [{$teacherUpn}] as channel owner: ".$e->getMessage());
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to create MS private channel [{$channelName}]: ".$e->getMessage());
                }
            }

            SectionSubject::create([
                'section_id' => $section->id,
                'subject_name' => $channelName,
                'teacher_name' => $teacherName,
                'schedule' => null,
                'ms_channel_id' => $channelId,
            ]);
        }
    }

    private function subjectsFor(Request $request): Collection
    {
        $context = (string) $request->session()->get('teacher_academic_context');
        $teacherEmail = $request->session()->get('teacher_email');

        if ($teacherEmail === 'sir_monlingasa@amis.edu.ph') {
            $sectionSubjects = SectionSubject::with('section')->get();
            $subjects = $sectionSubjects->map(function (SectionSubject $sectionSubject) {
                $subjectName = $sectionSubject->subject_name;
                $gradeLevel = $sectionSubject->section?->grade_level;

                $catalogSubject = Subject::where('name', $subjectName)
                    ->where('grade_level', $gradeLevel)
                    ->first();

                if (! $catalogSubject) {
                    $catalogSubject = Subject::where('name', $subjectName)->first();
                }

                return $this->sectionSubjectArray($sectionSubject, $catalogSubject);
            });

            $sectionSubjectNames = $sectionSubjects->pluck('subject_name')->map(fn($n) => strtolower($n))->unique();
            $catalogSubjects = Subject::get()->reject(fn($s) => $sectionSubjectNames->contains(strtolower($s->name)))
                ->map(fn($s) => $this->catalogSubjectArray($s));

            $subjects = $subjects->concat($catalogSubjects)->unique('id')->values();

            if (str_starts_with($context, 'subject:')) {
                $subjectId = (int) Str::after($context, 'subject:');
                $subjects = $subjects->where('subject_id', $subjectId)->values();
            }

            if (str_starts_with($context, 'adviser:')) {
                return collect();
            }

            return $subjects;
        }

        if (str_starts_with($context, 'adviser:')) {
            return collect();
        }

        $teacherKey = $this->teacherKey($request);
        $teacherName = $request->session()->get('teacher_name');
        $teacherEmail = $request->session()->get('teacher_email');

        $assigned = TeacherSubjectAssignment::with('subject')
            ->where(function ($query) use ($teacherKey, $teacherEmail) {
                $query->where('teacher_key', $teacherKey);
                if ($teacherEmail) {
                    $query->orWhere('teacher_email', $teacherEmail);
                }
            })
            ->where('status', 'active')
            ->get();

        $sectionSubjects = SectionSubject::with('section')
            ->where(function ($query) use ($teacherName) {
                $query->where('teacher_name', $teacherName)
                    ->orWhere('teacher_name', 'like', '%'.trim((string) $teacherName).'%');
            })
            ->get();

        // Keep track of section subjects that we've already mapped via TeacherSubjectAssignment
        $mappedSectionSubjectIds = collect();

        $assignedSubjects = $assigned->flatMap(function (TeacherSubjectAssignment $assignment) use ($sectionSubjects, &$mappedSectionSubjectIds) {
            $matches = $sectionSubjects->filter(fn ($row) => Str::lower($row->subject_name) === Str::lower($assignment->subject?->name));
            if ($matches->isEmpty()) {
                return [$this->catalogSubjectArray($assignment->subject)];
            }

            return $matches->map(function (SectionSubject $sectionSubject) use ($assignment, &$mappedSectionSubjectIds) {
                $mappedSectionSubjectIds->push($sectionSubject->id);

                return $this->sectionSubjectArray($sectionSubject, $assignment->subject);
            });
        })->filter();

        // Find SectionSubjects where teacher matches, but they weren't matched in assignments
        $unmappedSectionSubjects = $sectionSubjects->reject(fn ($row) => $mappedSectionSubjectIds->contains($row->id));

        $directSubjects = $unmappedSectionSubjects->map(function (SectionSubject $sectionSubject) {
            $subjectName = $sectionSubject->subject_name;
            $gradeLevel = $sectionSubject->section?->grade_level;

            $catalogSubject = Subject::where('name', $subjectName)
                ->where('grade_level', $gradeLevel)
                ->first();

            if (! $catalogSubject) {
                $catalogSubject = Subject::where('name', $subjectName)->first();
            }

            return $this->sectionSubjectArray($sectionSubject, $catalogSubject);
        });

        $subjects = $assignedSubjects->concat($directSubjects)->unique('id')->values();
        if (str_starts_with($context, 'subject:')) {
            $subjectId = (int) Str::after($context, 'subject:');
            $subjects = $subjects->where('subject_id', $subjectId)->values();
        }

        return $subjects;
    }

    public function advisorySectionsFor(Request $request): Collection
    {
        $context = (string) $request->session()->get('teacher_academic_context');
        $teacherEmail = $request->session()->get('teacher_email');

        if ($teacherEmail === 'sir_monlingasa@amis.edu.ph') {
            $sectionIds = Section::pluck('id')->unique();
            if (str_starts_with($context, 'adviser:')) {
                $sectionIds = $sectionIds->intersect([(int) Str::after($context, 'adviser:')]);
            }
            if (str_starts_with($context, 'subject:')) {
                return collect();
            }
            return $sectionIds;
        }

        if (str_starts_with($context, 'subject:')) {
            return collect();
        }

        $teacherKey = $this->teacherKey($request);
        $teacherName = $request->session()->get('teacher_name');
        $teacherEmail = $request->session()->get('teacher_email');

        // 1. Get from database ClassAdvisoryAssignment
        $dbSectionIds = ClassAdvisoryAssignment::where('status', 'active')
            ->where(function ($query) use ($teacherKey, $teacherEmail, $teacherName) {
                $query->where('teacher_key', $teacherKey);
                if ($teacherEmail) {
                    $query->orWhere('teacher_email', $teacherEmail);
                }
                if ($teacherName) {
                    $query->orWhere('teacher_name', $teacherName);
                }
            })
            ->pluck('section_id')
            ->filter()
            ->unique();

        // 2. Get from config('class_advisories') configuration matching the teacher's name
        $configSectionIds = collect();
        if ($teacherName) {
            $cleanTeacherName = trim(str_ireplace('TEACHER ', '', $teacherName));

            $allAdvisories = collect();
            foreach (config('class_advisories', []) as $key => $rows) {
                if (in_array($key, ['elementary', 'high_school'], true)) {
                    $allAdvisories = $allAdvisories->concat($rows);
                }
            }

            $matchingAdvisoryGrades = $allAdvisories->filter(function ($adv) use ($cleanTeacherName) {
                $advTeacher = trim(str_ireplace('TEACHER ', '', $adv['teacher'] ?? ''));

                return str_contains(strtolower($advTeacher), strtolower($cleanTeacherName)) ||
                       str_contains(strtolower($cleanTeacherName), strtolower($advTeacher));
            })->pluck('grade_level')->unique();

            if ($matchingAdvisoryGrades->isNotEmpty()) {
                $configSectionIds = Section::whereIn('grade_level', $matchingAdvisoryGrades)
                    ->pluck('id')
                    ->unique();
            }
        }

        $sectionIds = $dbSectionIds->concat($configSectionIds)->unique();
        if (str_starts_with($context, 'adviser:')) {
            $sectionIds = $sectionIds->intersect([(int) Str::after($context, 'adviser:')]);
        }

        return $sectionIds;
    }

    public function availableAcademicContexts(Request $request): Collection
    {
        $teacherKey = $this->teacherKey($request);
        $teacherEmail = $request->session()->get('teacher_email');
        $teacherName = $request->session()->get('teacher_name');

        if ($teacherEmail === 'sir_monlingasa@amis.edu.ph') {
            $subjectAssignments = Subject::orderBy('grade_level')->orderBy('name')->get()
                ->unique(fn ($s) => $s->name . ' · ' . $s->grade_level)
                ->map(fn ($subject) => [
                    'key' => 'subject:'.$subject->id,
                    'type' => 'subject',
                    'label' => $subject->name.($subject->grade_level ? ' · '.$subject->grade_level : ''),
                ]);

            $advisoryAssignments = Section::orderBy('grade_level')->orderBy('name')->get()
                ->map(fn ($section) => [
                    'key' => 'adviser:'.$section->id,
                    'type' => 'adviser',
                    'label' => 'Adviser · '.$section->section_title,
                ]);

            return $subjectAssignments->concat($advisoryAssignments)->values();
        }

        $subjectAssignments = TeacherSubjectAssignment::with('subject')
            ->where('status', 'active')
            ->where(function ($query) use ($teacherKey, $teacherEmail) {
                $query->where('teacher_key', $teacherKey);
                if ($teacherEmail) {
                    $query->orWhere('teacher_email', $teacherEmail);
                }
            })->get()
            ->filter(fn ($assignment) => $assignment->subject)
            ->unique('subject_id')
            ->map(fn ($assignment) => [
                'key' => 'subject:'.$assignment->subject_id,
                'type' => 'subject',
                'label' => $assignment->subject->name.($assignment->subject->grade_level ? ' · '.$assignment->subject->grade_level : ''),
            ]);

        $advisoryAssignments = ClassAdvisoryAssignment::with('section')
            ->where('status', 'active')
            ->where(function ($query) use ($teacherKey, $teacherEmail, $teacherName) {
                $query->where('teacher_key', $teacherKey);
                if ($teacherEmail) {
                    $query->orWhere('teacher_email', $teacherEmail);
                }
                if ($teacherName) {
                    $query->orWhere('teacher_name', $teacherName);
                }
            })->get()
            ->filter(fn ($assignment) => $assignment->section)
            ->unique('section_id')
            ->map(fn ($assignment) => [
                'key' => 'adviser:'.$assignment->section_id,
                'type' => 'adviser',
                'label' => 'Adviser · '.$assignment->section->section_title,
            ]);

        return $subjectAssignments->concat($advisoryAssignments)->values();
    }

    private function studentsFor(Collection $subjects, Collection $advisorySectionIds): Collection
    {
        $subjectSectionIds = $subjects->pluck('section_id')->filter()->unique();
        $allSectionIds = $subjectSectionIds->concat($advisorySectionIds)->unique();

        $sections = Section::whereIn('id', $allSectionIds)->get()->keyBy('id');

        $studentSections = StudentSection::with(['student.user', 'student.applicant'])
            ->whereIn('section_id', $allSectionIds)
            ->get();

        // For any section that has 0 student_sections, let's dynamically fetch matching students
        $dynamicRows = collect();
        foreach ($allSectionIds as $sectionId) {
            $hasStudents = $studentSections->contains('section_id', $sectionId);
            if (!$hasStudents) {
                $section = $sections[$sectionId] ?? null;
                if ($section) {
                    $query = \App\Models\Student::with(['user', 'applicant'])
                        ->where('grade_level', $section->grade_level);

                    if (str_contains($section->learning_mode ?? '', 'Face') || str_contains($section->learning_mode ?? '', 'f2f')) {
                        $query->whereHas('applicant', function ($q) {
                            $q->where('learning_mode', 'like', '%Face%')
                              ->orWhere('learning_mode', 'like', '%f2f%');
                        });
                    } else {
                        $query->whereHas('applicant', function ($q) use ($section) {
                            $q->where(function ($sub) {
                                $sub->where('learning_mode', 'like', '%Online%')
                                    ->orWhere('learning_mode', 'like', '%Flexible%');
                            });
                            if ($section->shift) {
                                $q->where('learning_mode', 'like', '%' . $section->shift . '%');
                            }
                        });
                    }

                    if ($section->gender === 'male') {
                        $query->whereHas('applicant', function ($q) {
                            $q->where('gender', 'like', 'male%');
                        });
                    } elseif ($section->gender === 'female') {
                        $query->whereHas('applicant', function ($q) {
                            $q->where('gender', 'like', 'female%');
                        });
                    }

                    $matchingStudents = $query->get();
                    foreach ($matchingStudents as $student) {
                        $row = new StudentSection();
                        $row->student_id = $student->id;
                        $row->section_id = $sectionId;
                        $row->setRelation('student', $student);
                        $dynamicRows->push($row);
                    }
                }
            }
        }

        $allRows = $studentSections->concat($dynamicRows);

        return $allRows->flatMap(function ($row) use ($subjects, $advisorySectionIds) {
                $base = [
                    'id' => $row->student_id,
                    'section_id' => $row->section_id,
                    'name' => $row->student?->user?->name ?? 'Student '.$row->student_id,
                    'student_no' => $row->student?->student_number ?? 'N/A',
                    'grade' => $row->student?->grade_level ?? '',
                    'section' => $row->student?->section ?? '',
                    'photo_url' => EnrollmentStorage::url($row->student?->applicant?->photo_2x2_url),
                ];

                $subjectRows = $subjects
                    ->where('section_id', $row->section_id)
                    ->whereNotNull('section_subject_id')
                    ->map(fn ($subject) => $base + ['section_subject_id' => $subject['section_subject_id']]);

                if ($subjectRows->isEmpty() && $advisorySectionIds->contains($row->section_id)) {
                    return [$base + ['section_subject_id' => null]];
                }

                return $subjectRows;
            })
            ->unique(fn ($student) => $student['id'].'|'.($student['section_subject_id'] ?? 'advisory'))
            ->values();
    }

    private function catalogSubjectArray(?Subject $subject): ?array
    {
        if (! $subject) {
            return null;
        }

        return [
            'id' => 'subject-'.$subject->id,
            'subject_id' => $subject->id,
            'section_subject_id' => null,
            'section_id' => null,
            'name' => $subject->name,
            'code' => $subject->code,
            'grade' => $subject->grade_level,
            'section' => 'Not linked to a class section',
            'schedule' => null,
            'room' => null,
            'mode' => 'Assigned',
            'advisor' => null,
        ];
    }

    private function sectionSubjectArray(SectionSubject $row, ?Subject $subject): array
    {
        return [
            'id' => 'section-subject-'.$row->id,
            'subject_id' => $subject?->id,
            'section_subject_id' => $row->id,
            'section_id' => $row->section_id,
            'name' => $row->subject_name,
            'code' => $subject?->code,
            'grade' => $row->section?->grade_level,
            'section' => $row->section?->section_title,
            'schedule' => $row->schedule,
            'room' => null,
            'mode' => $row->section?->learning_mode ?? 'F2F',
            'advisor' => $row->section?->grade_advisor?->teacher_name ?? null,
        ];
    }

    private function resolveSubject(Request $request, string $workspaceId): array
    {
        $subject = $this->subjectsFor($request)->firstWhere('id', $workspaceId);
        abort_unless($subject, 403, 'This subject is not assigned to you.');

        return $subject;
    }

    private function meetingArray(SubjectMeeting $meeting): array
    {
        $workspaceId = $meeting->section_subject_id ? 'section-subject-'.$meeting->section_subject_id : 'subject-'.$meeting->subject_id;

        return [
            'id' => 'meeting-'.$meeting->id,
            'subject_id' => $workspaceId,
            'title' => $meeting->title,
            'date' => optional($meeting->meeting_date)->toDateString(),
            'time' => substr((string) $meeting->meeting_time, 0, 5),
            'duration' => $meeting->duration_minutes,
            'link' => $meeting->meeting_url,
            'agenda' => $meeting->description,
            'status' => Str::headline($meeting->status),
        ];
    }

    private function materialArray(LearningMaterial $material): array
    {
        $workspaceId = $material->section_subject_id ? 'section-subject-'.$material->section_subject_id : 'subject-'.$material->subject_id;

        return [
            'id' => 'material-'.$material->id,
            'subject_id' => $workspaceId,
            'title' => $material->title,
            'description' => $material->description,
            'type' => $material->type,
            'url' => $material->external_url ?: Storage::disk($material->disk ?: 'public')->url($material->path),
            'created_at' => $material->created_at?->format('M d, Y'),
        ];
    }

    private function announcementArray(SubjectAnnouncement $announcement): array
    {
        $workspaceId = $announcement->section_subject_id ? 'section-subject-'.$announcement->section_subject_id : 'subject-'.$announcement->subject_id;

        return [
            'id' => 'announcement-'.$announcement->id,
            'subject_id' => $workspaceId,
            'title' => $announcement->title,
            'audience' => $announcement->audience ?? 'Assigned students',
            'date' => $announcement->published_at?->toDateString(),
            'body' => $announcement->body,
        ];
    }

    private function teacherKey(Request $request): string
    {
        return Str::slug($request->session()->get('teacher_name', 'teacher'));
    }
}
