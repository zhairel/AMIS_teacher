@extends('teacher.layout', ['heading' => 'Class Meetings'])

@section('content')
<section class="teacher-table-panel teacher-meetings-panel">
    <div class="teacher-panel-header">
        <div>
            <h2>Meeting Board</h2>
            <span>{{ $meetings->count() }} records</span>
        </div>
        @if($subjects->isNotEmpty())
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <form method="POST" action="{{ route('teacher.meetings.store') }}" style="margin:0;">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $subjects->first()['id'] }}">
                    <input type="hidden" name="title" value="Instant Meeting">
                    <input type="hidden" name="description" value="Live class meeting started from the Faculty Portal.">
                    <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                    <input type="hidden" name="time" value="{{ now()->format('H:i') }}">
                    <input type="hidden" name="duration" value="60">
                    <input type="hidden" name="status" value="Live">
                    <button class="teacher-primary-btn"><i data-lucide="play"></i> Start Now</button>
                </form>
                <button type="button" class="teacher-primary-btn" data-teacher-modal-open aria-controls="meetingCreateModal">
                    <i data-lucide="plus"></i> Schedule
                </button>
            </div>
        @endif
    </div>


    <div class="teacher-table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Meeting</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Description</th>
                    <th>Teams Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($meetings as $meeting)
                    @php 
                        $subject = $subjects->firstWhere('id', $meeting['subject_id']); 
                        $dbMeetingId = str_replace('meeting-', '', $meeting['id']);
                        $statusLower = strtolower($meeting['status']);
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-start;">
                                <span class="teacher-status-pill">{{ $meeting['status'] }}</span>
                                @if($statusLower === 'scheduled' || $statusLower === 'draft')
                                    <form method="POST" action="{{ route('teacher.meetings.status', $dbMeetingId) }}" style="margin:0;">
                                        @csrf
                                        <input type="hidden" name="status" value="live">
                                        <button type="submit" class="teacher-primary-btn" style="padding: 2px 6px; font-size: 9px; border-radius: 4px; background-color: #10b981; border-color: #10b981; color: white; display: inline-flex; align-items: center; gap: 2px;">
                                            <i data-lucide="play" style="width:10px; height:10px;"></i> Go Live
                                        </button>
                                    </form>
                                @elseif($statusLower === 'live')
                                    <form method="POST" action="{{ route('teacher.meetings.status', $dbMeetingId) }}" style="margin:0;">
                                        @csrf
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="teacher-outline-btn" style="padding: 2px 6px; font-size: 9px; border-radius: 4px; color: #ef4444; border-color: #fca5a5; background-color: #fef2f2; display: inline-flex; align-items: center; gap: 2px;">
                                            <i data-lucide="square" style="width:10px; height:10px;"></i> End Class
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                        <td><strong>{{ $meeting['title'] }}</strong></td>
                        <td>
                            {{ $subject['name'] ?? 'No subject' }}
                            <span>{{ $subject['section'] ?? 'Unassigned' }}</span>
                        </td>
                        <td>{{ $meeting['date'] }}</td>
                        <td>{{ $meeting['time'] }}</td>
                        <td class="teacher-table-muted">{{ $meeting['agenda'] ?: 'No description added' }}</td>
                        <td>
                            <div style="display:flex; flex-direction:column; gap:6px; min-width: 140px;">
                                @if($meeting['link'])
                                    <a href="{{ $meeting['link'] }}" target="_blank" class="teacher-outline-btn" style="display:inline-flex; align-items:center; gap:4px; padding: 4px 8px; font-size: 11px; border-radius: 6px; width: fit-content; text-decoration: none;">
                                        <i data-lucide="external-link" style="width:12px; height:12px;"></i> Join Meeting
                                    </a>
                                @endif
                                
                                <form method="POST" action="{{ route('teacher.meetings.link', $dbMeetingId) }}" style="margin: 0; display: flex; gap: 4px; align-items: center;">
                                    @csrf
                                    <input type="url" name="link" value="{{ $meeting['link'] }}" placeholder="Teams Link" required style="padding: 4px 6px; font-size: 10px; border-radius: 6px; border: 1px solid #cbd5e1; width: 100px;">
                                    <button type="submit" class="teacher-icon-btn" title="Save Link" style="padding: 2px;"><i data-lucide="check" style="width:12px; height:12px; color: #10b981;"></i></button>
                                </form>
                            </div>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('teacher.meetings.delete', $dbMeetingId) }}" onsubmit="return confirm('Are you sure you want to delete this meeting?')" style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="teacher-icon-btn" title="Delete" style="color: #ef4444; border:none; background:none; cursor:pointer; padding:4px;"><i data-lucide="trash-2" style="width:15px; height:15px;"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            @if($subjects->isEmpty())
                                <div style="padding: 48px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;">
                                    <div style="background-color: rgba(16, 185, 129, 0.1); border-radius: 50%; padding: 16px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i data-lucide="video" style="width: 36px; height: 36px; color: #10b981; stroke-width: 1.5;"></i>
                                    </div>
                                    <h3 style="font-size: 1.15rem; font-weight: 600; color: #e2e8f0; margin: 0;">Preparing Academic Load</h3>
                                    <p style="font-size: 0.875rem; color: #94a3b8; max-width: 400px; margin: 0; line-height: 1.5;">Please wait for the administrator in the Admin Portal to assign subjects to your account first.</p>
                                </div>
                            @else
                                <div class="dash-empty">
                                    <i data-lucide="video-off"></i>
                                    <p>No meetings created yet</p>
                                    <button type="button" class="teacher-primary-btn" data-teacher-modal-open aria-controls="meetingCreateModal">
                                        <i data-lucide="plus"></i> Add Meeting
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@if($subjects->isNotEmpty())
<div id="meetingCreateModal" class="teacher-modal-backdrop" data-teacher-modal hidden>
    <div class="teacher-modal-card" role="dialog" aria-modal="true" aria-labelledby="meetingCreateTitle">
        <div class="teacher-panel-header">
            <h2 id="meetingCreateTitle">Create Meeting</h2>
            <button type="button" class="teacher-icon-btn" data-teacher-modal-close aria-label="Close">
                <i data-lucide="x"></i>
            </button>
        </div>

        @if($errors->any())
            <div class="teacher-error">
                <i data-lucide="alert-circle"></i>
                <span>Please check the meeting details and try again.</span>
            </div>
        @endif

        <form method="POST" action="{{ route('teacher.meetings.store') }}" class="teacher-form">
            @csrf
            <label>
                <span>Subject</span>
                <select name="subject_id" required>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject['id'] }}" @selected(old('subject_id') === $subject['id'])>
                            {{ $subject['name'] }} · {{ $subject['section'] }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label><span>Meeting Title</span><input name="title" value="{{ old('title') }}" required placeholder="Weekly review"></label>
            <div class="teacher-form-grid">
                <label><span>Date</span><input name="date" type="date" value="{{ old('date', now()->toDateString()) }}" required></label>
                <label><span>Time</span><input name="time" type="time" value="{{ old('time', '08:00') }}" required></label>
            </div>
            <label><span>Duration</span><input name="duration" type="number" min="5" max="480" value="{{ old('duration', 60) }}" required></label>
            <label><span>Meeting Link</span><input name="link" type="url" value="{{ old('link') }}" placeholder="https://teams.microsoft.com/..."></label>
            <label><span>Description</span><textarea name="description" placeholder="Discussion points">{{ old('description', old('agenda')) }}</textarea></label>
            <label>
                <span>Status</span>
                <select name="status">
                    @foreach(['Scheduled','Live','Draft','Completed'] as $status)
                        <option @selected(old('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </label>
            <div class="teacher-modal-actions">
                <button type="button" class="teacher-outline-btn" data-teacher-modal-close>Cancel</button>
                <button type="submit" class="teacher-primary-btn"><i data-lucide="save"></i> Save Meeting</button>
            </div>
        </form>
    </div>
</div>

<script>
    (() => {
        const modal = document.getElementById('meetingCreateModal');
        if (!modal) return;

        const openModal = () => {
            modal.hidden = false;
            document.body.classList.add('teacher-modal-open');
            window.lucide?.createIcons();
            modal.querySelector('select[name="subject_id"]')?.focus();
        };

        const closeModal = () => {
            modal.hidden = true;
            document.body.classList.remove('teacher-modal-open');
        };

        document.querySelectorAll('[data-teacher-modal-open]').forEach((button) => {
            button.addEventListener('click', openModal);
        });

        modal.querySelectorAll('[data-teacher-modal-close]').forEach((button) => {
            button.addEventListener('click', closeModal);
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });

        if (@json($errors->any())) {
            openModal();
        }
    })();
</script>
@endif
@endsection
