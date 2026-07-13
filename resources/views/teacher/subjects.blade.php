@extends('teacher.layout', ['heading' => 'Classroom Workspace'])

@section('content')
@php
    // Get unique grade levels from the assigned subjects
    $assignedGrades = $subjects->pluck('grade')->filter(fn($val) => !empty($val) && strtolower($val) !== 'catalog')->unique()->values()->sort();

    // Map each grade level to the list of subjects assigned for that grade
    $gradeSubjects = $subjects->groupBy('grade')->map(function ($items) {
        return $items->pluck('name')->unique()->values();
    });
@endphp

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
[x-cloak] { display: none !important; }
</style>

{{-- Analytics Grid --}}
<section class="dash-stats">
    <article class="dash-stat dash-stat-violet">
        <span class="dash-stat-icon"><i data-lucide="users"></i></span>
        <div>
            <p class="dash-stat-label">Total Students</p>
            <strong class="dash-stat-value">{{ isset($students) ? $students->unique('id')->count() : 0 }}</strong>
        </div>
    </article>
    <article class="dash-stat dash-stat-green">
        <span class="dash-stat-icon"><i data-lucide="folder-open"></i></span>
        <div>
            <p class="dash-stat-label">Published Materials</p>
            <strong class="dash-stat-value">{{ isset($materials) ? $materials->count() : 0 }}</strong>
        </div>
    </article>
    <article class="dash-stat dash-stat-blue">
        <span class="dash-stat-icon"><i data-lucide="video"></i></span>
        <div>
            <p class="dash-stat-label">Scheduled Meetings</p>
            <strong class="dash-stat-value">{{ isset($meetings) ? $meetings->count() : 0 }}</strong>
        </div>
    </article>
    <article class="dash-stat dash-stat-amber">
        <span class="dash-stat-icon"><i data-lucide="megaphone"></i></span>
        <div>
            <p class="dash-stat-label">Announcements</p>
            <strong class="dash-stat-value">{{ isset($announcements) ? $announcements->count() : 0 }}</strong>
        </div>
    </article>
</section>

@php
    $subjectsCount = $subjects->count();
    $targetLoad = 8;
    $loadPercent = min(100, round(($subjectsCount / $targetLoad) * 100));
    
    if ($subjectsCount >= $targetLoad) {
        $loadStatus = 'Full Load';
        $statusColor = 'background-color: #ede9fe; color: #7c3aed; border: 1px solid #ddd6fe;';
        $progressColor = 'background: linear-gradient(90deg, #6366f1, #7c3aed);';
    } elseif ($subjectsCount >= 6) {
        $loadStatus = 'Balanced Load';
        $statusColor = 'background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;';
        $progressColor = 'background: linear-gradient(90deg, #0284c7, #0369a1);';
    } else {
        $loadStatus = 'Needs Load';
        $statusColor = 'background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a;';
        $progressColor = 'background: linear-gradient(90deg, #f59e0b, #d97706);';
    }
@endphp

{{-- Workload Tracker --}}
<div class="teacher-panel" style="margin-bottom: 28px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="background-color: #ecfdf5; border-radius: 10px; padding: 10px; display: inline-flex; align-items: center; justify-content: center; color: #059669;">
                <i data-lucide="activity" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0;">Workload Tracker</h3>
                <p style="font-size: 0.825rem; color: #64748b; margin: 2px 0 0;">Teaching capacity and resource optimization</p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 0.9rem; font-weight: 700; color: #334155;">{{ $subjectsCount }} / {{ $targetLoad }} Classrooms</span>
            <span style="display: inline-flex; align-items: center; justify-content: center; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 750; {{ $statusColor }}">
                {{ $loadStatus }}
            </span>
        </div>
    </div>
    
    <div style="background-color: #e2e8f0; height: 10px; border-radius: 9999px; overflow: hidden; position: relative;">
        <div style="height: 100%; border-radius: 9999px; transition: width 0.5s ease-out; {{ $progressColor }} width: {{ $loadPercent }}%;"></div>
    </div>
</div>

{{-- Classroom Workspaces Grid Header --}}
<div class="teacher-panel-header" style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.25rem; font-weight: 800; color: #0d1117; margin: 0;">Classroom Workspaces</h2>
        <span style="font-size: 0.85rem; color: #8b949e; font-weight: 500;">{{ $subjects->count() }} total workspaces assigned</span>
    </div>
</div>

<div class="teacher-card-grid">
    @forelse($subjects as $subject)
        @php
            $isLinked = !empty($subject['section_subject_id']);
            $cardBorderColor = $isLinked ? '#e9ebee' : '#fde68a';
            $modeColor = str_contains($subject['mode'], 'Flexible') ? 'background-color: #e0e7ff; color: #4f46e5;' : 'background-color: #ecfdf5; color: #059669;';
        @endphp
        <div class="teacher-subject-card" style="display: flex; flex-direction: column; justify-content: space-between; min-height: 220px; border-color: {{ $cardBorderColor }};">
            <div>
                {{-- Top Badge row --}}
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 14px;">
                    <span class="teacher-subject-code-pill" style="margin: 0;">
                        {{ $subject['code'] ?: strtoupper(substr($subject['name'] ?? 'SUB', 0, 3)) }}
                    </span>
                    <span style="display: inline-flex; padding: 3px 9px; border-radius: 6px; font-size: 10.5px; font-weight: 650; {{ $modeColor }}">
                        {{ $subject['mode'] }}
                    </span>
                </div>

                {{-- Subject Name --}}
                <h3 style="margin: 0 0 10px; font-size: 1.15rem; font-weight: 800; color: #0d1117; letter-spacing: -0.3px;">
                    {{ $subject['name'] }}{{ $subject['grade'] ? ' - ' . $subject['grade'] : '' }}
                </h3>

                {{-- Details list --}}
                <div style="display: flex; flex-direction: column; gap: 6px; font-size: 0.85rem; color: #57606a;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="school" style="width: 14px; height: 14px; color: #8b949e;"></i>
                        <span>{{ $subject['grade'] ?: 'Catalog' }} · <span style="font-weight: 600;">{{ $subject['section'] }}</span></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="calendar" style="width: 14px; height: 14px; color: #8b949e;"></i>
                        <span>{{ $subject['schedule'] ?: 'Unscheduled' }}</span>
                    </div>
                    @if(!empty($subject['advisor']))
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="user-check" style="width: 14px; height: 14px; color: #8b949e;"></i>
                            <span>Advisor: <span style="font-weight: 600; color: #1f2328;">{{ $subject['advisor'] }}</span></span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Bottom row with sync status and button --}}
            <div style="margin-top: 18px; padding-top: 12px; border-top: 1px solid #e9ebee; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 5px; font-size: 0.775rem;">
                    @if($isLinked)
                        <span style="color: #059669; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                            <i data-lucide="check-circle-2" style="width: 13px; height: 13px;"></i> MS Team Sync
                        </span>
                    @else
                        <span style="color: #b45309; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;" title="This subject is not linked to a Microsoft Class Team section yet.">
                            <i data-lucide="alert-circle" style="width: 13px; height: 13px;"></i> No Sync
                        </span>
                    @endif
                </div>
                
                <a href="{{ route('teacher.subjects.workspace', $subject['id']) }}" class="teacher-primary-btn" style="min-height: 32px; padding: 6px 12px; font-size: 0.8rem; border-radius: 8px;">
                    <i data-lucide="layout-dashboard" style="width: 13px; height: 13px;"></i> Open
                </a>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; padding: 48px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; background: white; border: 1px solid #e9ebee; border-radius: var(--r-lg);">
            <div style="background-color: rgba(16, 185, 129, 0.1); border-radius: 50%; padding: 16px; display: inline-flex; align-items: center; justify-content: center;">
                <i data-lucide="book-open" style="width: 36px; height: 36px; color: #10b981; stroke-width: 1.5;"></i>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 600; color: #0d1117; margin: 0;">Preparing Academic Load</h3>
            <p style="font-size: 0.875rem; color: #57606a; max-width: 400px; margin: 0; line-height: 1.5;">Please wait for the administrator in the Admin Portal to assign subjects to your account first.</p>
        </div>
    @endforelse
</div>


@endsection
