@extends('teacher.layout', ['heading' => $subject['name']])

@section('content')
@php
    $tab = request('tab', 'overview');
    $tabs = ['overview', 'announcements', 'materials', 'assignments', 'meetings', 'students', 'grades', 'attendance'];
@endphp

<section class="dash-welcome">
    <div class="dash-welcome-body">
        <div class="flex flex-wrap items-center gap-2 mb-3">
            <span class="teacher-subject-code-pill">{{ $subject['code'] ?: strtoupper(substr($subject['name'], 0, 3)) }}</span>
            <span class="teacher-subject-list-chip">{{ $subject['mode'] }}</span>
        </div>
        <h2 class="dash-welcome-title">{{ $subject['name'] }}</h2>
        <p class="dash-welcome-sub">{{ $subject['grade'] ?: 'Catalog' }} · {{ $subject['section'] }} · {{ $subject['schedule'] ?: 'Unscheduled' }}</p>
    </div>
    <div class="dash-welcome-actions">
        <a href="{{ route('teacher.subjects') }}" class="teacher-light-btn"><i data-lucide="arrow-left"></i> Classroom Workspace</a>
        <a href="{{ route('teacher.grades', ['subject' => $subject['id']]) }}" class="teacher-primary-btn"><i data-lucide="clipboard-list"></i> Gradebook</a>
    </div>
</section>

<nav class="dash-actions">
    @foreach($tabs as $item)
        <a href="{{ route('teacher.subjects.workspace', ['subject' => $subject['id'], 'tab' => $item]) }}" class="dash-action {{ $tab === $item ? 'dash-action-subjects' : '' }}">
            <span class="dash-action-icon"><i data-lucide="{{ [
                'overview' => 'layout-dashboard',
                'announcements' => 'megaphone',
                'materials' => 'folder-open',
                'assignments' => 'file-check-2',
                'meetings' => 'video',
                'students' => 'users',
                'grades' => 'clipboard-list',
                'attendance' => 'calendar-check',
            ][$item] }}"></i></span>
            <span class="dash-action-text"><strong>{{ Str::headline($item) }}</strong></span>
        </a>
    @endforeach
</nav>

@if($tab === 'overview')
    <section class="dash-stats">
        <article class="dash-stat dash-stat-green"><span class="dash-stat-icon"><i data-lucide="folder-open"></i></span><div><p class="dash-stat-label">Materials</p><strong class="dash-stat-value">{{ $subjectMaterials->count() }}</strong></div></article>
        <article class="dash-stat dash-stat-blue"><span class="dash-stat-icon"><i data-lucide="video"></i></span><div><p class="dash-stat-label">Meetings</p><strong class="dash-stat-value">{{ $subjectMeetings->count() }}</strong></div></article>
        <article class="dash-stat dash-stat-violet"><span class="dash-stat-icon"><i data-lucide="users"></i></span><div><p class="dash-stat-label">Students</p><strong class="dash-stat-value">{{ $subjectStudents->count() }}</strong></div></article>
        <article class="dash-stat dash-stat-amber"><span class="dash-stat-icon"><i data-lucide="megaphone"></i></span><div><p class="dash-stat-label">Announcements</p><strong class="dash-stat-value">{{ $subjectAnnouncements->count() }}</strong></div></article>
    </section>
@endif

@if($tab === 'materials')
    <section class="dash-split">
        <form method="POST" action="{{ route('teacher.materials.store') }}" enctype="multipart/form-data" class="teacher-panel teacher-form">
            @csrf
            <input type="hidden" name="subject_id" value="{{ $subject['id'] }}">
            <div class="teacher-panel-header"><h2>Publish Material</h2><i data-lucide="folder-up" style="color:var(--t-text-muted);"></i></div>
            <label><span>Title</span><input name="title" required value="{{ old('title') }}"></label>
            <label><span>Description</span><textarea name="description">{{ old('description') }}</textarea></label>
            <label><span>File</span><input name="file" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,image/*,video/*"></label>
            <label><span>Google Drive Link</span><input name="external_url" type="url" value="{{ old('external_url') }}" placeholder="https://drive.google.com/..."></label>
            <button type="submit" class="teacher-primary-btn"><i data-lucide="upload-cloud"></i> Publish</button>
        </form>
        <div class="teacher-panel">
            <div class="teacher-panel-header"><h2>Learning Materials</h2><span>{{ $subjectMaterials->count() }} total</span></div>
            <div class="teacher-announcement-list">
                @forelse($subjectMaterials as $material)
                    <article>
                        <span>{{ $material['created_at'] }}</span>
                        <h3>{{ $material['title'] }}</h3>
                        <p>{{ $material['description'] ?: Str::headline($material['type']) }}</p>
                        <a href="{{ $material['url'] }}" target="_blank" class="teacher-outline-btn"><i data-lucide="external-link"></i> Open</a>
                    </article>
                @empty
                    <div class="dash-empty"><i data-lucide="folder-open"></i><p>No materials published yet</p></div>
                @endforelse
            </div>
        </div>
    </section>
@endif

@if($tab === 'meetings')
    <section class="dash-split">
        <div class="teacher-panel">
            <div class="teacher-panel-header"><h2>Start Now</h2><i data-lucide="radio" style="color:var(--t-text-muted);"></i></div>
            <form method="POST" action="{{ route('teacher.meetings.store') }}" class="teacher-form">
                @csrf
                <input type="hidden" name="subject_id" value="{{ $subject['id'] }}">
                <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                <input type="hidden" name="time" value="{{ now()->format('H:i') }}">
                <input type="hidden" name="duration" value="60">
                <input type="hidden" name="status" value="Live">
                <label><span>Title</span><input name="title" required value="Live {{ $subject['name'] }}"></label>
                <label><span>Microsoft Teams Link</span><input name="link" type="url" placeholder="https://teams.microsoft.com/..."></label>
                <button type="submit" class="teacher-primary-btn"><i data-lucide="video"></i> Start Meeting</button>
            </form>
        </div>
        <div class="teacher-panel">
            <div class="teacher-panel-header"><h2>Meetings</h2><span>{{ $subjectMeetings->count() }} total</span></div>
            <div class="dash-timeline">
                @forelse($subjectMeetings as $meeting)
                    @php 
                        $dbMeetingId = str_replace('meeting-', '', $meeting['id']);
                        $statusLower = strtolower($meeting['status']);
                    @endphp
                    <div class="dash-timeline-item" style="display:flex; justify-content:space-between; align-items:flex-start; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                        <div style="display:flex; gap:12px; align-items:flex-start;">
                            <span class="dash-timeline-dot {{ $statusLower }}" style="margin-top: 4px;"></span>
                            <div class="dash-timeline-content">
                                <strong style="font-size:14px; color:#1e293b;">{{ $meeting['title'] }}</strong>
                                <span class="teacher-status-pill" style="font-size: 9px; padding: 1px 6px; margin-left: 6px; vertical-align: middle;">{{ $meeting['status'] }}</span>
                                <p style="font-size:12px; color:#64748b; margin: 4px 0 8px 0;">{{ $meeting['agenda'] ?: 'No description' }}</p>
                                
                                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                    <!-- Status Toggle Action Buttons -->
                                    @if($statusLower === 'scheduled' || $statusLower === 'draft')
                                        <form method="POST" action="{{ route('teacher.meetings.status', $dbMeetingId) }}" style="margin:0;">
                                            @csrf
                                            <input type="hidden" name="status" value="live">
                                            <button type="submit" class="teacher-primary-btn" style="padding: 3px 8px; font-size: 10px; border-radius: 6px; background-color: #10b981; border-color: #10b981; color: white; display: inline-flex; align-items: center; gap: 2px;">
                                                <i data-lucide="play" style="width:10px; height:10px;"></i> Go Live
                                            </button>
                                        </form>
                                    @elseif($statusLower === 'live')
                                        <form method="POST" action="{{ route('teacher.meetings.status', $dbMeetingId) }}" style="margin:0;">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="teacher-outline-btn" style="padding: 3px 8px; font-size: 10px; border-radius: 6px; color: #ef4444; border-color: #fca5a5; background-color: #fef2f2; display: inline-flex; align-items: center; gap: 2px;">
                                                <i data-lucide="square" style="width:10px; height:10px;"></i> End Class
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Link Editor Inline -->
                                    <form method="POST" action="{{ route('teacher.meetings.link', $dbMeetingId) }}" style="margin: 0; display: flex; gap: 4px; align-items: center;">
                                        @csrf
                                        <input type="url" name="link" value="{{ $meeting['link'] }}" placeholder="Teams Link" required style="padding: 4px 8px; font-size: 10px; border-radius: 6px; border: 1px solid #cbd5e1; width: 150px; background-color: white;">
                                        <button type="submit" class="teacher-icon-btn" title="Save Link" style="padding: 2px;"><i data-lucide="check" style="width:12px; height:12px; color: #10b981;"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div style="text-align:right; display:flex; flex-direction:column; gap:6px; align-items:flex-end;">
                            <span class="dash-timeline-time" style="font-size:11px; color:#94a3b8;">{{ $meeting['date'] }} · {{ $meeting['time'] }}</span>
                            <div style="display:flex; gap:6px; align-items:center;">
                                @if($meeting['link'])
                                    <a href="{{ $meeting['link'] }}" target="_blank" class="teacher-outline-btn" style="display:inline-flex; align-items:center; gap:4px; padding: 3px 8px; font-size: 10px; border-radius: 6px; text-decoration: none;">
                                        <i data-lucide="external-link" style="width:11px; height:11px;"></i> Join
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('teacher.meetings.delete', $dbMeetingId) }}" onsubmit="return confirm('Are you sure you want to delete this meeting?')" style="margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="teacher-icon-btn" title="Delete" style="color: #ef4444; border:none; background:none; cursor:pointer; padding:4px;"><i data-lucide="trash-2" style="width:13px; height:13px;"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="dash-empty"><i data-lucide="calendar-off"></i><p>No meetings yet</p></div>
                @endforelse
            </div>
        </div>
    </section>
@endif

@if($tab === 'announcements')
    <section class="dash-split">
        <form method="POST" action="{{ route('teacher.announcements.store') }}" class="teacher-panel teacher-form">
            @csrf
            <input type="hidden" name="subject_id" value="{{ $subject['id'] }}">
            <div class="teacher-panel-header"><h2>Post Announcement</h2><i data-lucide="megaphone" style="color:var(--t-text-muted);"></i></div>
            <label><span>Title</span><input name="title" required></label>
            <label>
                <span>Audience</span>
                <select name="audience" required>
                    <option value="Assigned subject" selected>Assigned Subject Students</option>
                    <option value="Department">My Department ({{ session('teacher_dept', 'Arabic & Islamic Studies') }})</option>
                    <option value="Other">Other (e.g. Specific Group)</option>
                </select>
            </label>
            <label><span>Date</span><input name="date" type="date" value="{{ now()->toDateString() }}" required></label>
            <label><span>Message</span><textarea name="body" required></textarea></label>
            <button type="submit" class="teacher-primary-btn"><i data-lucide="send"></i> Post</button>
        </form>
        <div class="teacher-panel">
            <div class="teacher-panel-header"><h2>Announcements</h2><span>{{ $subjectAnnouncements->count() }} total</span></div>
            <div class="teacher-announcement-list">
                @forelse($subjectAnnouncements as $announcement)
                    @php 
                        $dbAnnId = str_replace('announcement-', '', $announcement['id']);
                    @endphp
                    <article style="border-bottom: 1px solid var(--s-border); padding: 12px 0; width: 100%;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; width: 100%;">
                            <div style="flex: 1; min-width: 0; padding-right: 16px;">
                                <span style="font-size:11px; color:var(--t-tertiary);">{{ $announcement['date'] }}</span>
                                <h3 style="margin: 4px 0 6px 0; font-size:13px; font-weight:700;">{{ $announcement['title'] }}</h3>
                                <p style="margin:0; font-size:12.5px; color:var(--t-secondary); white-space:pre-line; line-height: 1.45;">{{ $announcement['body'] }}</p>
                                <small style="display:block; margin-top:6px; font-size:11px; color:var(--t-tertiary);">{{ $announcement['audience'] ?? 'Assigned students' }}</small>
                            </div>
                            <div style="display:flex; gap:6px; align-items:center; flex-shrink:0;">
                                <!-- Edit Button (Toggles Form) -->
                                <button type="button" onclick="toggleWorkspaceEditAnnouncement('{{ $dbAnnId }}')" class="teacher-icon-btn" title="Edit" style="color: var(--t-secondary); border:none; background:none; cursor:pointer; padding:4px;">
                                    <i data-lucide="edit-3" style="width:13px; height:13px;"></i>
                                </button>
                                
                                <!-- Delete Form -->
                                <form method="POST" action="{{ route('teacher.announcements.delete', $dbAnnId) }}" onsubmit="return confirm('Are you sure you want to delete this announcement?')" style="margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="teacher-icon-btn" title="Delete" style="color: #ef4444; border:none; background:none; cursor:pointer; padding:4px;">
                                        <i data-lucide="trash-2" style="width:13px; height:13px;"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Hidden Edit Form -->
                        <div id="ws-edit-form-{{ $dbAnnId }}" style="display:none; margin-top:12px; padding:12px; border: 1px solid var(--s-border); border-radius: 8px; background-color: var(--s-surface-hover);">
                            <form method="POST" action="{{ route('teacher.announcements.update', $dbAnnId) }}" class="teacher-form" style="margin: 0; gap: 8px;">
                                @csrf
                                <label><span>Title</span><input name="title" value="{{ $announcement['title'] }}" required style="padding: 5px 8px; font-size: 12px; border-radius: 6px;"></label>
                                <label>
                                    <span>Audience</span>
                                    <select name="audience" required style="padding: 5px 8px; font-size: 12px; border-radius: 6px; width: 100%;">
                                        <option value="Assigned subject" @selected(($announcement['audience'] ?? '') === 'Assigned subject')>Assigned Subject Students</option>
                                        <option value="Department" @selected(($announcement['audience'] ?? '') === 'Department')>My Department ({{ session('teacher_dept', 'Arabic & Islamic Studies') }})</option>
                                        <option value="Other" @selected(($announcement['audience'] ?? '') === 'Other')>Other (e.g. Specific Group)</option>
                                    </select>
                                </label>
                                <label><span>Message</span><textarea name="body" required style="min-height:60px; padding: 5px 8px; font-size: 12px; border-radius: 6px;">{{ $announcement['body'] }}</textarea></label>
                                <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:8px;">
                                    <button type="button" onclick="toggleWorkspaceEditAnnouncement('{{ $dbAnnId }}')" class="teacher-outline-btn" style="padding: 3px 8px; font-size: 10px; min-height: 26px; border-radius: 6px;">Cancel</button>
                                    <button type="submit" class="teacher-primary-btn" style="padding: 3px 8px; font-size: 10px; min-height: 26px; background-color: #10b981; border-color: #10b981; color: white; border-radius: 6px;">Save</button>
                                </div>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="dash-empty"><i data-lucide="megaphone-off"></i><p>No announcements yet</p></div>
                @endforelse
            </div>
            <script>
                function toggleWorkspaceEditAnnouncement(id) {
                    const el = document.getElementById('ws-edit-form-' + id);
                    if (el) {
                        el.style.display = el.style.display === 'none' ? 'block' : 'none';
                    }
                }
            </script>
        </div>
    </section>
@endif

@if(in_array($tab, ['assignments', 'students', 'grades'], true))
    <section class="teacher-table-panel">
        <div class="teacher-panel-header">
            <div><h2>{{ Str::headline($tab) }}</h2><span>{{ $subject['name'] }}</span></div>
        </div>
        <div class="dash-empty">
            <i data-lucide="{{ $tab === 'students' ? 'users' : ($tab === 'grades' ? 'clipboard-list' : 'file-check-2') }}"></i>
            <p>{{ $tab === 'students' ? $subjectStudents->count().' enrolled students' : 'No records yet' }}</p>
        </div>
    </section>
@endif

@if($tab === 'attendance')
    <section class="teacher-panel" style="padding: 24px; width: 100%;">
        <div class="teacher-panel-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom: 24px;">
            <div>
                <h2 style="font-size:18px; font-weight:800;">Student Daily Attendance</h2>
                <span style="font-size:12px; color:var(--t-tertiary);">Mark and track attendance for {{ $subject['name'] }}</span>
            </div>
            
            <!-- Date Filter form -->
            <form method="GET" action="{{ route('teacher.subjects.workspace', $subject['id']) }}" style="margin:0; display:flex; gap:8px; align-items:center;">
                <input type="hidden" name="tab" value="attendance">
                <input type="date" name="attendance_date" value="{{ $attendanceDate }}" onchange="this.form.submit()" style="padding:6px 12px; border-radius:8px; border:1px solid var(--s-border); font-size:13px; background-color:var(--s-surface); color:var(--t-primary);">
            </form>
        </div>

        <form method="POST" action="{{ route('teacher.subjects.attendance.store', $subject['id']) }}">
            @csrf
            <input type="hidden" name="date" value="{{ $attendanceDate }}">

            <div class="teacher-table-scroll" style="margin-bottom: 20px;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--s-border); font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--t-tertiary);">
                            <th style="padding: 12px 16px;">Student ID</th>
                            <th style="padding: 12px 16px;">Student Name</th>
                            <th style="padding: 12px 16px; text-align: center; width: 420px;">Attendance Status</th>
                            <th style="padding: 12px 16px; width: 250px;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjectStudents as $index => $student)
                            @php
                                $existing = $subjectAttendances->firstWhere('student_id', $student['id']);
                                $status = $existing ? $existing->status : 'Present';
                                $remarks = $existing ? $existing->remarks : '';
                            @endphp
                            <tr style="border-bottom: 1px solid var(--s-border);">
                                <td style="padding: 12px 16px; font-weight: 600; font-family: monospace;">
                                    {{ $student['student_no'] }}
                                    <input type="hidden" name="attendance[{{ $index }}][student_id]" value="{{ $student['id'] }}">
                                </td>
                                <td style="padding: 12px 16px;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div style="width:32px; height:32px; border-radius:50%; background-color:#10b981; color:white; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:12px;">
                                            {{ substr($student['name'], 0, 1) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;">{{ $student['name'] }}</div>
                                            <div style="font-size: 10px; color: var(--t-tertiary);">{{ $student['grade'] }} · {{ $student['section'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <div style="display:inline-flex; border: 1px solid var(--s-border); border-radius: 8px; overflow: hidden; background-color: var(--s-surface-hover);">
                                        <label style="cursor: pointer; display: inline-flex; align-items: center; padding: 6px 12px; font-size: 11px; font-weight: 700; gap: 4px; transition: all 0.2s;" class="attendance-pill-label">
                                            <input type="radio" name="attendance[{{ $index }}][status]" value="Present" @checked($status === 'Present') style="margin: 0; accent-color: #10b981;">
                                            <span style="color: #10b981;">Present</span>
                                        </label>
                                        <label style="cursor: pointer; display: inline-flex; align-items: center; padding: 6px 12px; font-size: 11px; font-weight: 700; gap: 4px; transition: all 0.2s; border-left: 1px solid var(--s-border);" class="attendance-pill-label">
                                            <input type="radio" name="attendance[{{ $index }}][status]" value="Late" @checked($status === 'Late') style="margin: 0; accent-color: #f59e0b;">
                                            <span style="color: #f59e0b;">Late</span>
                                        </label>
                                        <label style="cursor: pointer; display: inline-flex; align-items: center; padding: 6px 12px; font-size: 11px; font-weight: 700; gap: 4px; transition: all 0.2s; border-left: 1px solid var(--s-border);" class="attendance-pill-label">
                                            <input type="radio" name="attendance[{{ $index }}][status]" value="Excused" @checked($status === 'Excused') style="margin: 0; accent-color: #3b82f6;">
                                            <span style="color: #3b82f6;">Excused</span>
                                        </label>
                                        <label style="cursor: pointer; display: inline-flex; align-items: center; padding: 6px 12px; font-size: 11px; font-weight: 700; gap: 4px; transition: all 0.2s; border-left: 1px solid var(--s-border);" class="attendance-pill-label">
                                            <input type="radio" name="attendance[{{ $index }}][status]" value="Absent" @checked($status === 'Absent') style="margin: 0; accent-color: #ef4444;">
                                            <span style="color: #ef4444;">Absent</span>
                                        </label>
                                    </div>
                                </td>
                                <td style="padding: 12px 16px;">
                                    <input type="text" name="attendance[{{ $index }}][remarks]" value="{{ $remarks }}" placeholder="E.g., Medical leave..." style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--s-border); font-size: 12px; background-color: var(--s-surface); color: var(--t-primary);">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding: 32px; text-align: center; color: var(--t-tertiary);">
                                    No students assigned to this section subject load.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subjectStudents->isNotEmpty())
                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="submit" class="teacher-primary-btn" style="background-color: #059669; border-color: #059669; color: white;">
                        <i data-lucide="check-circle-2"></i> Save Daily Attendance
                    </button>
                </div>
            @endif
        </form>
    </section>
@endif
@endsection
