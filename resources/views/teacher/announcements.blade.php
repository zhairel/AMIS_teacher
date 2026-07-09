@extends('teacher.layout', ['heading' => 'Announcements'])

@section('content')
<section class="dash-split">
    <!-- Left Column: Calendar UI -->
    <div class="teacher-panel" style="padding: 20px; align-self: flex-start;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 id="calendarMonthYear" style="margin:0; font-size:15px; font-weight:700; color:var(--t-primary);"></h3>
            <div style="display:flex; gap:6px;">
                <button type="button" onclick="prevMonth()" class="teacher-icon-btn" style="border: 1px solid var(--s-border); border-radius: 6px; padding:6px; display:inline-flex; align-items:center; justify-content:center;">
                    <i data-lucide="chevron-left" style="width:14px; height:14px;"></i>
                </button>
                <button type="button" onclick="nextMonth()" class="teacher-icon-btn" style="border: 1px solid var(--s-border); border-radius: 6px; padding:6px; display:inline-flex; align-items:center; justify-content:center;">
                    <i data-lucide="chevron-right" style="width:14px; height:14px;"></i>
                </button>
            </div>
        </div>
        
        <!-- Days of the week -->
        <div style="display:grid; grid-template-columns:repeat(7, 1fr); text-align:center; gap:6px; font-size:12px; font-weight:700; color:var(--t-tertiary); margin-bottom:10px;">
            <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
        </div>
        
        <!-- Days Grid -->
        <div id="calendarDaysGrid" style="display:grid; grid-template-columns:repeat(7, 1fr); gap:6px; text-align:center;">
            <!-- Rendered by Javascript -->
        </div>
        
        <!-- Filter Clear Actions -->
        <div id="calendarFilterStatus" style="display:none; margin-top:20px; padding-top:16px; border-top:1px solid var(--s-border); justify-content:space-between; align-items:center;">
            <span style="font-size:12px; color:var(--t-secondary); display:inline-flex; align-items:center; gap:4px;">
                <i data-lucide="filter" style="width:12px; height:12px; color:#10b981;"></i> Date filter active
            </span>
            <button type="button" onclick="clearDateFilter()" style="font-size:11px; color:#10b981; border:none; background:none; cursor:pointer; font-weight:700; padding:0; text-decoration: underline;">
                Clear filter
            </button>
        </div>
    </div>

    <!-- Right Column: Posted Announcements -->
    <div class="teacher-panel" style="display:flex; flex-direction:column; gap:24px;">
        <div class="teacher-panel-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h2>Posted Updates</h2>
                <span id="announcementsCount">{{ $announcements->count() }} total</span>
            </div>
            <button type="button" class="teacher-primary-btn" data-teacher-modal-open>
                <i data-lucide="plus"></i> Add Announcement
            </button>
        </div>

        <div class="teacher-announcement-list" id="announcementsList">
            @forelse($announcements as $announcement)
                @php 
                    $dbAnnId = str_replace('announcement-', '', $announcement['id']);
                @endphp
                <article data-announcement-date="{{ $announcement['date'] }}" style="border-bottom: 1px solid var(--s-border); padding: 16px 0;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; width: 100%;">
                        <div style="flex: 1; min-width: 0; padding-right: 16px;">
                            <span style="font-size:11px; color:var(--t-tertiary);">{{ $announcement['date'] }}</span>
                            <h3 style="margin: 4px 0 6px 0; font-size:14px; font-weight:700;">{{ $announcement['title'] }}</h3>
                            <p style="margin:0; font-size:13px; color:var(--t-secondary); white-space:pre-line; line-height: 1.5;">{{ $announcement['body'] }}</p>
                            <small style="display:block; margin-top:8px; font-size:11px; color:var(--t-tertiary);">
                                <span class="teacher-status-pill" style="font-size:9px; padding:2px 8px; font-weight:600;">{{ $announcement['audience'] ?? 'Assigned students' }}</span>
                            </small>
                        </div>
                        <div style="display:flex; gap:6px; align-items:center; flex-shrink:0;">
                            <!-- Edit Button (Toggles Form) -->
                            <button type="button" onclick="toggleEditAnnouncement('{{ $dbAnnId }}')" class="teacher-icon-btn" title="Edit" style="color: var(--t-secondary); border:none; background:none; cursor:pointer; padding:4px;">
                                <i data-lucide="edit-3" style="width:14px; height:14px;"></i>
                            </button>
                            
                            <!-- Delete Form -->
                            <form method="POST" action="{{ route('teacher.announcements.delete', $dbAnnId) }}" onsubmit="return confirm('Are you sure you want to delete this announcement?')" style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="teacher-icon-btn" title="Delete" style="color: #ef4444; border:none; background:none; cursor:pointer; padding:4px;">
                                    <i data-lucide="trash-2" style="width:14px; height:14px;"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Hidden Edit Form -->
                    <div id="edit-form-{{ $dbAnnId }}" style="display:none; margin-top:12px; padding:12px; border: 1px solid var(--s-border); border-radius: 8px; background-color: var(--s-surface-hover);">
                        <form method="POST" action="{{ route('teacher.announcements.update', $dbAnnId) }}" class="teacher-form" style="margin: 0; gap: 8px;">
                            @csrf
                            <label><span>Title</span><input name="title" value="{{ $announcement['title'] }}" required style="padding: 6px 10px; font-size: 13px; border-radius: 6px;"></label>
                            <label>
                                <span>Audience</span>
                                <select name="audience" required style="padding: 6px 10px; font-size: 13px; border-radius: 6px; width: 100%;">
                                    <option value="Assigned subject" @selected($announcement['audience'] === 'Assigned subject')>Assigned Subject Students</option>
                                    <option value="Department" @selected($announcement['audience'] === 'Department')>My Department ({{ session('teacher_dept', 'Arabic & Islamic Studies') }})</option>
                                    <option value="Other" @selected($announcement['audience'] === 'Other')>Other (e.g. Specific Group)</option>
                                </select>
                            </label>
                            <label><span>Message</span><textarea name="body" required style="min-height:70px; padding: 6px 10px; font-size: 13px; border-radius: 6px;">{{ $announcement['body'] }}</textarea></label>
                            <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:8px;">
                                <button type="button" onclick="toggleEditAnnouncement('{{ $dbAnnId }}')" class="teacher-outline-btn" style="padding: 4px 10px; font-size: 11px; min-height: 28px; border-radius: 6px;">Cancel</button>
                                <button type="submit" class="teacher-primary-btn" style="padding: 4px 10px; font-size: 11px; min-height: 28px; background-color: #10b981; border-color: #10b981; color: white; border-radius: 6px;">Save</button>
                            </div>
                        </form>
                    </div>
                </article>
            @empty
                <div class="dash-empty" style="padding: 48px 24px;">
                    <i data-lucide="megaphone-off" style="color: #10b981;"></i>
                    <p>No announcements posted yet</p>
                </div>
            @endforelse
        </div>

        <!-- Empty State for Date Filter -->
        <div id="filterEmptyState" class="dash-empty" style="padding: 48px 24px; display:none;">
            <i data-lucide="calendar-x" style="color: var(--t-text-muted);"></i>
            <p>No announcements posted on this day</p>
        </div>
    </div>
</section>

<!-- Add Announcement Large Modal -->
<div id="announcementCreateModal" class="teacher-modal-backdrop" data-teacher-modal hidden>
    <div class="teacher-modal-card" role="dialog" aria-modal="true" aria-labelledby="announcementCreateTitle" style="max-width: 680px; width: 100%;">
        <div class="teacher-panel-header">
            <h2 id="announcementCreateTitle">Create Announcement</h2>
            <button type="button" class="teacher-icon-btn" data-teacher-modal-close aria-label="Close">
                <i data-lucide="x"></i>
            </button>
        </div>

        @if($errors->any())
            <div class="teacher-error">
                <i data-lucide="alert-circle"></i>
                <span>Please check the announcement details and try again.</span>
            </div>
        @endif

        <form method="POST" action="{{ route('teacher.announcements.store') }}" class="teacher-form">
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
            <label><span>Title</span><input name="title" required placeholder="Class reminder..." value="{{ old('title') }}"></label>
            <label>
                <span>Audience</span>
                <select name="audience" required>
                    <option value="Assigned subject" @selected(old('audience') === 'Assigned subject')>Assigned Subject Students</option>
                    <option value="Department" @selected(old('audience') === 'Department')>My Department ({{ session('teacher_dept', 'Arabic & Islamic Studies') }})</option>
                    <option value="Other" @selected(old('audience') === 'Other')>Other (e.g. Specific Group)</option>
                </select>
            </label>
            <label><span>Date</span><input name="date" type="date" value="{{ old('date', now()->toDateString()) }}" required></label>
            <label><span>Message</span><textarea name="body" required placeholder="Write your announcement..." style="min-height: 120px;">{{ old('body') }}</textarea></label>
            <div class="teacher-modal-actions" style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="teacher-outline-btn" data-teacher-modal-close>Cancel</button>
                <button type="submit" class="teacher-primary-btn" style="background-color: #059669; border-color: #059669; color: white;"><i data-lucide="send"></i> Post Announcement</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleEditAnnouncement(id) {
        const el = document.getElementById('edit-form-' + id);
        if (el) {
            el.style.display = el.style.display === 'none' ? 'block' : 'none';
        }
    }

    // Calendar & Filter Logic
    const announcementDates = @json($announcements->pluck('date')->toArray());
    let currentYear = {{ now()->year }};
    let currentMonth = {{ now()->month - 1 }}; // 0-indexed (0 = Jan, 11 = Dec)
    const today = new Date();
    let selectedDateStr = null;

    const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    function renderCalendar() {
        const firstDayIndex = new Date(currentYear, currentMonth, 1).getDay();
        const lastDay = new Date(currentYear, currentMonth + 1, 0).getDate();
        const prevLastDay = new Date(currentYear, currentMonth, 0).getDate();

        document.getElementById("calendarMonthYear").innerText = monthNames[currentMonth] + " " + currentYear;

        let daysHtml = "";

        // Prev month offset days
        for (let x = firstDayIndex; x > 0; x--) {
            daysHtml += `<div style="padding:8px 0; color:var(--t-tertiary); font-size:12px; opacity:0.3; pointer-events:none;">${prevLastDay - x + 1}</div>`;
        }

        // Current month days
        for (let i = 1; i <= lastDay; i++) {
            const dayStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            const isToday = today.getDate() === i && today.getMonth() === currentMonth && today.getFullYear() === currentYear;
            const hasAnnouncement = announcementDates.includes(dayStr);
            const isSelected = selectedDateStr === dayStr;

            let cellStyle = "padding:6px 0; border-radius:6px; cursor:pointer; font-size:12px; font-weight:600; position:relative; display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:36px; transition: all 0.2s;";
            
            if (isSelected) {
                cellStyle += "background-color:#10b981; color:white; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);";
            } else if (isToday) {
                cellStyle += "background-color:var(--s-surface-hover); border:1.5px solid #10b981; color:var(--t-primary);";
            } else {
                cellStyle += "color:var(--t-primary);";
            }

            daysHtml += `
                <div style="${cellStyle}" onclick="selectCalendarDate('${dayStr}')" class="calendar-day-cell">
                    <span>${i}</span>
                    ${hasAnnouncement ? `<span style="width:5px; height:5px; border-radius:50%; background-color:${isSelected ? 'white' : '#10b981'}; position:absolute; bottom:4px;"></span>` : ''}
                </div>
            `;
        }

        document.getElementById("calendarDaysGrid").innerHTML = daysHtml;
        
        // Update filter status visibility
        const statusDiv = document.getElementById("calendarFilterStatus");
        if (statusDiv) {
            statusDiv.style.display = selectedDateStr ? "flex" : "none";
        }
        window.lucide?.createIcons();
    }

    function prevMonth() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar();
    }

    function nextMonth() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar();
    }

    function selectCalendarDate(dateStr) {
        selectedDateStr = selectedDateStr === dateStr ? null : dateStr;
        renderCalendar();
        applyAnnouncementsFilter();
    }

    function clearDateFilter() {
        selectedDateStr = null;
        renderCalendar();
        applyAnnouncementsFilter();
    }

    function applyAnnouncementsFilter() {
        const articles = document.querySelectorAll('#announcementsList article');
        let visibleCount = 0;
        
        articles.forEach(article => {
            const artDate = article.getAttribute('data-announcement-date');
            if (!selectedDateStr || artDate === selectedDateStr) {
                article.style.display = 'block';
                visibleCount++;
            } else {
                article.style.display = 'none';
            }
        });

        const emptyState = document.getElementById('filterEmptyState');
        const listContainer = document.getElementById('announcementsList');

        if (visibleCount === 0 && articles.length > 0) {
            emptyState.style.display = 'flex';
            listContainer.style.display = 'none';
        } else {
            emptyState.style.display = 'none';
            listContainer.style.display = 'block';
        }

        // Update header count text
        const countText = selectedDateStr ? `${visibleCount} match(es) for ${selectedDateStr}` : `${articles.length} total`;
        document.getElementById('announcementsCount').innerText = countText;
        window.lucide?.createIcons();
    }

    // Modal Initializer
    (() => {
        const modal = document.getElementById('announcementCreateModal');
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

        // Initial calendar render
        renderCalendar();
    })();
</script>
@endsection
