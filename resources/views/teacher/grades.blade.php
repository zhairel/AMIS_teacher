@extends('teacher.layout', ['heading' => 'Gradebook'])

@section('content')
@if($subjects->isEmpty())
    <div class="teacher-panel">
        <div class="dash-empty" style="padding:48px 24px;">
            <i data-lucide="book-open"></i>
            <p>No section-linked subjects have been assigned to your account yet.</p>
        </div>
    </div>
@else
<div class="teacher-panel" style="margin-bottom:16px; border-left:4px solid {{ ($submission?->status ?? 'draft') === 'draft' ? '#f59e0b' : '#2563eb' }};">
    <strong style="display:block;">Status: {{ ucfirst($submission?->status ?? 'draft') }}</strong>
    <span style="font-size:13px; color:#475569;">Drafts remain editable. Submitted grades wait for Academic review and cannot be changed.</span>
</div>
{{-- Toolbar --}}
<div class="teacher-toolbar-panel">
    <form method="GET" action="{{ route('teacher.grades') }}" class="teacher-form teacher-inline-form">
        <label style="margin-bottom:0;">
            <span>Subject</span>
            <select name="subject" onchange="this.form.submit()" style="min-width:240px;">
                @foreach($subjects as $subject)
                    <option value="{{ $subject['id'] }}" @selected($selectedSubjectId === $subject['id'])>
                        {{ $subject['name'] }} · {{ $subject['section'] }}
                    </option>
                @endforeach
            </select>
        </label>
    </form>

    <form method="POST" action="{{ route('teacher.assessments.store') }}" class="teacher-form teacher-assessment-form">
        @csrf
        <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
        <label style="margin-bottom:0;"><span>Assessment</span><input name="title" required placeholder="Quiz title"></label>
        <label style="margin-bottom:0;"><span>Max</span><input name="max_score" type="number" value="50" min="1" required></label>
        <label style="margin-bottom:0;"><span>Date</span><input name="date" type="date" value="{{ now()->toDateString() }}" required></label>
        <button type="submit" class="teacher-primary-btn"><i data-lucide="plus"></i> Add</button>
    </form>
</div>

{{-- Grade Table --}}
<form method="POST" action="{{ route('teacher.grades.scores.store') }}" class="teacher-panel teacher-table-panel">
    @csrf
    <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
    <div class="teacher-panel-header">
        <h2>{{ $selectedSubject['name'] ?? 'Gradebook' }}</h2>
        <span>{{ $students->count() }} learners</span>
    </div>
    <div class="teacher-table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    @foreach($assessments as $assessment)
                        <th>{{ $assessment['title'] }}<small>/{{ $assessment['max_score'] }}</small></th>
                    @endforeach
                    <th>Average</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    @php
                        $percentages = [];
                        foreach ($assessments as $assessment) {
                            $value = $scores[$student['id'].':'.$assessment['id']] ?? null;
                            if ($value !== null && $value !== '') {
                                $percentages[] = ((int) $value / (int) $assessment['max_score']) * 100;
                            }
                        }
                        $average = count($percentages) ? round(array_sum($percentages) / count($percentages)) : null;
                    @endphp
                    <tr>
                        <td><strong>{{ $student['name'] }}</strong><span>{{ $student['student_no'] }}</span></td>
                        @foreach($assessments as $assessment)
                            @php $key = $student['id'].':'.$assessment['id']; @endphp
                            <td>
                                <input class="teacher-score-input" type="number" min="0" max="{{ $assessment['max_score'] }}"
                                    name="scores[{{ $student['id'] }}][{{ $assessment['id'] }}]"
                                    value="{{ $scores[$key] ?? '' }}">
                            </td>
                        @endforeach
                        <td><span class="teacher-average">{{ $average === null ? 'N/A' : $average.'%' }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($assessments) + 2 }}" style="padding:48px;text-align:center;color:var(--t-text-muted);">
                            <i data-lucide="clipboard-list" style="width:40px;height:40px;margin-bottom:12px;display:block;margin-inline:auto;"></i>
                            <span style="font-size:14px;font-weight:600;">No students enrolled in this subject</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="teacher-table-footer">
        <a href="{{ route('teacher.grades.export', $selectedSubjectId) }}" class="teacher-outline-btn"><i data-lucide="download"></i> Export CSV</a>
        @if(($submission?->status ?? 'draft') === 'draft' || ($submission?->status ?? '') === 'returned')
            <button type="submit" class="teacher-primary-btn"><i data-lucide="save"></i> Save Scores</button>
        @endif
    </div>
</form>
@if(($submission?->status ?? 'draft') === 'draft' || ($submission?->status ?? '') === 'returned')
    <form method="POST" action="{{ route('teacher.grades.submit', $selectedSubjectId) }}" style="margin-top:16px; text-align:right;" onsubmit="return confirm('Submit grades for Academic review? Editing will be locked until returned.')">
        @csrf
        <button class="teacher-primary-btn"><i data-lucide="send"></i> Submit for Review</button>
    </form>
@endif
@endif
@endsection
