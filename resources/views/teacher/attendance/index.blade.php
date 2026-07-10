@extends(session('teacher_email') ? 'teacher.layout' : 'teacher.public-layout', ['heading' => 'Attendance Logs'])

@section('content')
<div style="display:flex; flex-direction:column; gap:24px; width: 100%;">

    <!-- PROFILE CARD (Only visible if logged in OR a profile has been selected via public inquiry) -->
    @if(session('teacher_email') || $myBiometricId)
        <div class="teacher-panel" style="padding: 24px; display:flex; flex-direction:column; gap:20px;">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; border-bottom: 1px solid var(--s-border); padding-bottom: 20px;">
                <div>
                    <span style="font-size:11px; font-weight:800; color:var(--t-tertiary); text-transform:uppercase; letter-spacing:0.05em; display:block;">Full Name</span>
                    <strong style="font-size:18px; font-weight:800; color:var(--t-primary); margin-top:4px; display:block;">{{ $displayName }}</strong>
                </div>
                <div>
                    <span style="font-size:11px; font-weight:800; color:var(--t-tertiary); text-transform:uppercase; letter-spacing:0.05em; display:block;">Saved Info? (Biometric)</span>
                    <div style="margin-top:6px; display:flex; align-items:center; gap:8px;">
                        @if($myBiometricId)
                            <span style="font-size:11.5px; font-weight:800; text-transform:uppercase; padding:4px 10px; border-radius:6px; background-color:rgba(16, 185, 129, 0.1); color:#047857; border:1px solid rgba(16, 185, 129, 0.3); display:inline-flex; align-items:center; gap:6px;">
                                <span style="width:6px; height:6px; border-radius:50%; background-color:#10b981;"></span>
                                Linked (ID: {{ $myBiometricId }})
                            </span>
                        @else
                            <span style="font-size:11.5px; font-weight:800; text-transform:uppercase; padding:4px 10px; border-radius:6px; background-color:rgba(239, 68, 68, 0.1); color:#b91c1c; border:1px solid rgba(239, 68, 68, 0.3); display:inline-flex; align-items:center; gap:6px;">
                                <span style="width:6px; height:6px; border-radius:50%; background-color:#ef4444;"></span>
                                Not Saved / Not Linked
                            </span>
                        @endif
                    </div>
                </div>
            </div>
    @endif

    @if(!$myBiometricId)
        @if(session('teacher_email'))
            {{-- Logged in link profile form --}}
            <div class="teacher-panel" style="padding: 24px; display:flex; flex-direction:column; gap:20px;">
                <div style="background-color: var(--s-surface-hover); border: 1px dashed var(--s-border); padding: 20px; border-radius: 12px;">
                    <h3 style="font-size: 14px; font-weight: 700; color: var(--t-primary); margin: 0 0 8px 0; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="link" style="width: 16px; height: 16px; color:#10b981;"></i> Link Biometric Account
                    </h3>
                    <p style="font-size: 12px; color: var(--t-secondary); margin: 0 0 16px 0;">Please select your name from the biometric directory below to save your link and display your attendance logs.</p>
                    <form method="POST" action="{{ route('teacher.attendance.link') }}" class="teacher-form" style="display:flex; gap:12px; align-items:flex-end; margin:0;">
                        @csrf
                        <label style="flex:1; margin:0;">
                            <span>Select Your Biometric Profile</span>
                            <select name="biometric_id" required style="padding: 6px 10px; font-size:13px; border-radius:8px; width:100%;">
                                <option value="" disabled selected>Choose profile...</option>
                                @foreach($users as $u)
                                    <option value="{{ $u['employee_id'] }}">{{ $u['name'] }} (ID: {{ $u['employee_id'] }})</option>
                                @endforeach
                            </select>
                        </label>
                        <button type="submit" class="teacher-primary-btn" style="background-color: #059669; border-color: #059669; color: white;">
                            Link Profile
                        </button>
                    </form>
                </div>
            </div>
        @else
            {{-- Public lookup (Faculty Verification) --}}
            <div style="background-color: var(--s-surface); border: 1px solid var(--s-border); padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); max-width: 500px; margin: 40px auto; width: 100%; box-sizing: border-box;">
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="width: 54px; height: 54px; border-radius: 50%; background-color: rgba(16, 185, 129, 0.1); display: inline-flex; align-items: center; justify-content: center; color: #10b981; margin-bottom: 12px;">
                        <i data-lucide="shield-check" style="width: 28px; height: 28px;"></i>
                    </div>
                    <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--t-primary); margin: 0;">Faculty Verification</h2>
                    <p style="font-size: 12px; color: var(--t-tertiary); margin: 6px 0 0 0;">Verify your attendance info by Employee ID or Full Name</p>
                </div>

                <form method="GET" action="{{ route('teacher.attendance') }}" class="teacher-form" style="display: flex; flex-direction: column; gap: 16px; margin: 0;" onsubmit="return validateSearchForm()">
                    <label style="margin: 0;">
                        <span style="font-size: 11px; font-weight: 700; color: var(--t-secondary); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Enter Full Name</span>
                        <input type="text" name="search_name" id="searchNameInput" placeholder="e.g. Mon Zhairel Lingasa" style="padding: 10px 14px; font-size: 13px; border-radius: 10px; width: 100%; border: 1px solid var(--s-border); background-color: var(--s-surface); color: var(--t-primary); font-weight: 600;" onfocus="document.getElementById('searchIdInput').value = ''">
                    </label>

                    <div style="display: flex; align-items: center; justify-content: center; margin: 8px 0; position: relative;">
                        <span style="height: 1px; background-color: var(--s-border); flex: 1;"></span>
                        <span style="font-size: 10px; font-weight: 800; color: var(--t-tertiary); text-transform: uppercase; padding: 0 12px; background-color: var(--s-surface); position: relative; z-index: 2;">OR</span>
                        <span style="height: 1px; background-color: var(--s-border); flex: 1;"></span>
                    </div>

                    <label style="margin: 0;">
                        <span style="font-size: 11px; font-weight: 700; color: var(--t-secondary); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Enter Employee ID (PIN)</span>
                        <input type="text" name="search_id" id="searchIdInput" pattern="[0-9]*" inputmode="numeric" placeholder="e.g. 22078" style="padding: 10px 14px; font-size: 13px; border-radius: 10px; width: 100%; border: 1px solid var(--s-border); background-color: var(--s-surface); color: var(--t-primary); font-weight: 600;" onfocus="document.getElementById('searchNameInput').value = ''">
                    </label>

                    <button type="submit" class="teacher-primary-btn" style="background-color: #059669; border-color: #059669; color: white; padding: 12px 20px; font-size: 13px; font-weight: 800; border-radius: 10px; cursor: pointer; width: 100%; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 10px;">
                        Verify & View Logs
                    </button>
                </form>
            </div>

            <script>
                function validateSearchForm() {
                    const nameVal = document.getElementById('searchNameInput').value.trim();
                    const idVal = document.getElementById('searchIdInput').value.trim();
                    if (!nameVal && !idVal) {
                        alert('Please enter either your Full Name or Employee ID.');
                        return false;
                    }
                    return true;
                }
            </script>
        @endif
    @else
            <!-- Title & cutoff paginator -->
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; border-bottom:1px solid var(--s-border); padding-bottom:16px;">
                <div>
                    <h2 style="font-size:16px; font-weight:800; color:var(--t-primary); display:flex; align-items:center; gap:8px; margin:0;">
                        <i data-lucide="calendar" style="color:#10b981;"></i> My Attendance Report
                    </h2>
                    <span style="font-size:11px; color:var(--t-tertiary);">Showing logs for <strong>{{ $myStartDate }}</strong> to <strong>{{ $myEndDate }}</strong></span>
                </div>
                
                <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                    <!-- Cut-off Pagination Navigation -->
                    <div style="display:inline-flex; align-items:center; gap:8px; border: 1px solid var(--s-border); border-radius: 8px; padding: 4px 12px; background-color: var(--s-surface-hover);">
                        <!-- Previous Period Button -->
                        <a href="{{ route('teacher.attendance', ['my_month' => $prevMonth, 'my_year' => $prevYear, 'my_cutoff' => $prevCutoff]) }}" 
                           style="color: var(--t-primary); display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; transition: all 0.2s;"
                           title="Previous Pay Period">
                            <i data-lucide="chevrons-left" style="width: 16px; height: 16px;"></i>
                        </a>

                        <!-- Current Period Display Label -->
                        <span style="font-size: 13px; font-weight: 700; color: var(--t-primary); min-width: 150px; text-align: center; user-select: none;">
                            @php
                                $monthName = date('F', mktime(0, 0, 0, $myMonth, 1));
                                $cutoffLabel = $myCutoff === '1-15' ? '1 - 15' : '16 - ' . date('t', strtotime("{$myYear}-" . str_pad($myMonth, 2, '0', STR_PAD_LEFT) . "-01"));
                            @endphp
                            {{ $monthName }} {{ $cutoffLabel }}, {{ $myYear }}
                        </span>

                        <!-- Next Period Button (Disabled if in future) -->
                        @if($isNextDisabled)
                            <span style="color: var(--t-tertiary); cursor: not-allowed; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; opacity: 0.35;"
                                  title="Next period is active or not completed yet">
                                <i data-lucide="chevrons-right" style="width: 16px; height: 16px;"></i>
                            </span>
                        @else
                            <a href="{{ route('teacher.attendance', ['my_month' => $nextMonth, 'my_year' => $nextYear, 'my_cutoff' => $nextCutoff]) }}" 
                               style="color: var(--t-primary); display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; transition: all 0.2s;"
                               title="Next Pay Period">
                                <i data-lucide="chevrons-right" style="width: 16px; height: 16px;"></i>
                            </a>
                        @endif
                    </div>
                    
                    <!-- Change Link/Profile Option -->
                    <!-- Change Link/Profile Option -->
                    @if(session('teacher_email'))
                        <button type="button" onclick="const div = document.getElementById('changeBiometricDiv'); div.style.display = div.style.display === 'none' ? 'block' : 'none';" class="teacher-outline-btn" style="padding: 6px 12px; font-size:11.5px; min-height:34px; border-radius: 8px;">
                            Change Link
                        </button>
                    @else
                        <a href="{{ route('teacher.attendance') }}" class="teacher-outline-btn" style="padding: 6px 12px; font-size:11.5px; min-height:34px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                            Change Profile
                        </a>
                    @endif
                </div>
            </div>

            <!-- Change Link Div (hidden by default) -->
            <div id="changeBiometricDiv" style="display:none; padding:16px; border:1px solid var(--s-border); border-radius:8px; background-color:var(--s-surface-hover); margin-bottom:12px;">
                @if(session('teacher_email'))
                    <form method="POST" action="{{ route('teacher.attendance.link') }}" class="teacher-form" style="display:flex; gap:12px; align-items:flex-end; margin:0;">
                        @csrf
                        <label style="flex:1; margin:0;">
                            <span>Select Profile</span>
                            <select name="biometric_id" required style="padding: 6px 10px; font-size:12px; width:100%;">
                                @foreach($users as $u)
                                    <option value="{{ $u['employee_id'] }}" @selected($myBiometricId == $u['employee_id'])>{{ $u['name'] }} (ID: {{ $u['employee_id'] }})</option>
                                @endforeach
                            </select>
                        </label>
                        <button type="submit" class="teacher-primary-btn" style="padding:4px 12px; font-size:11px; min-height:28px; background-color: #059669; border-color: #059669; color:white;">Update</button>
                        <button type="button" onclick="document.getElementById('changeBiometricDiv').style.display='none'" class="teacher-outline-btn" style="padding:4px 12px; font-size:11px; min-height:28px;">Cancel</button>
                    </form>
                @else
                    <form method="GET" action="{{ route('teacher.attendance') }}" class="teacher-form" style="display:flex; gap:12px; align-items:flex-end; margin:0;">
                        <label style="flex:1; margin:0;">
                            <span>Select Profile</span>
                            <select name="biometric_id" required style="padding: 6px 10px; font-size:12px; width:100%;">
                                @foreach($users as $u)
                                    <option value="{{ $u['employee_id'] }}" @selected($myBiometricId == $u['employee_id'])>{{ $u['name'] }} (ID: {{ $u['employee_id'] }})</option>
                                @endforeach
                            </select>
                        </label>
                        <button type="submit" class="teacher-primary-btn" style="padding:4px 12px; font-size:11px; min-height:28px; background-color: #059669; border-color: #059669; color:white;">Inquire</button>
                        <button type="button" onclick="document.getElementById('changeBiometricDiv').style.display='none'" class="teacher-outline-btn" style="padding:4px 12px; font-size:11px; min-height:28px;">Cancel</button>
                    </form>
                @endif
            </div>

            <!-- 15-Day Personal Summary Row -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:16px;">
                <div style="padding:16px; border:1px solid var(--s-border); border-radius:12px; text-align:center; background-color:var(--s-surface-hover); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <span style="font-size:11px; font-weight:800; color:var(--t-tertiary); text-transform:uppercase; tracking: 0.05em;">Days Present</span>
                    <div style="font-size:24px; font-weight:800; color:var(--t-primary); margin-top:6px;">{{ $mySummary['present'] }}</div>
                </div>
                <div style="padding:16px; border:1px solid var(--s-border); border-radius:12px; text-align:center; background-color:var(--s-surface-hover); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <span style="font-size:11px; font-weight:800; color:var(--t-tertiary); text-transform:uppercase; tracking: 0.05em;">Late Minutes</span>
                    <div style="font-size:24px; font-weight:800; color:#b45309; margin-top:6px;">{{ $mySummary['late_minutes'] }} m</div>
                </div>
                <div style="padding:16px; border:1px solid var(--s-border); border-radius:12px; text-align:center; background-color:var(--s-surface-hover); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <span style="font-size:11px; font-weight:800; color:var(--t-tertiary); text-transform:uppercase; tracking: 0.05em;">Overtime</span>
                    <div style="font-size:24px; font-weight:800; color:#047857; margin-top:6px;">{{ number_format($mySummary['overtime_minutes'] / 60, 1) }} h</div>
                </div>
                <div style="padding:16px; border:1px solid var(--s-border); border-radius:12px; text-align:center; background-color:var(--s-surface-hover); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <span style="font-size:11px; font-weight:800; color:var(--t-tertiary); text-transform:uppercase; tracking: 0.05em;">Hours Worked</span>
                    <div style="font-size:24px; font-weight:800; color:var(--t-primary); margin-top:6px;">{{ number_format($mySummary['hours_worked'], 1) }} h</div>
                </div>
            </div>

            <!-- View Toggle & List/Calendar Views -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; margin-bottom:16px;">
                <div style="font-size:14px; color:var(--t-secondary); font-weight:600;">
                    Report Period: {{ date('M d, Y', strtotime($myStartDate)) }} to {{ date('M d, Y', strtotime($myEndDate)) }}
                </div>
                <div style="display:flex; background-color:var(--s-surface-hover); border:1px solid var(--s-border); border-radius:8px; padding:2px;">
                    <button type="button" id="btnListView" onclick="switchView('list')" style="padding:6px 14px; font-size:12px; font-weight:800; border-radius:6px; border:none; cursor:pointer; background-color:var(--s-surface); color:var(--t-primary); box-shadow:0 1px 2px rgba(0,0,0,0.05); display:flex; align-items:center; gap:6px;">
                        <i data-lucide="list" style="width:14px; height:14px;"></i> List
                    </button>
                    <button type="button" id="btnCalendarView" onclick="switchView('calendar')" style="padding:6px 14px; font-size:12px; font-weight:800; border-radius:6px; border:none; cursor:pointer; background-color:transparent; color:var(--t-tertiary); display:flex; align-items:center; gap:6px;">
                        <i data-lucide="calendar" style="width:14px; height:14px;"></i> Calendar
                    </button>
                </div>
            </div>

            <!-- List View Container -->
            <div id="attendanceListView">
                <div class="teacher-table-scroll">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--s-border); font-weight:800; color:var(--t-primary); font-size:13.5px;">
                            <th style="padding:16px 20px;">Date</th>
                            <th style="padding:16px 20px; text-align:center;">Time In</th>
                            <th style="padding:16px 20px; text-align:center;">Time Out</th>
                            <th style="padding:16px 20px; text-align:center;">Late</th>
                            <th style="padding:16px 20px; text-align:center;">Overtime</th>
                            <th style="padding:16px 20px; text-align:center;">Total Hours</th>
                            <th style="padding:16px 20px; text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody style="color:#334155; font-size:14.5px;">
                        @forelse($myLogs as $log)
                            @php
                                $statusColors = [
                                    'Present' => 'background-color:rgba(16, 185, 129, 0.1); color:#047857; border:1px solid rgba(16, 185, 129, 0.35);',
                                    'Late' => 'background-color:rgba(245, 158, 11, 0.1); color:#b45309; border:1px solid rgba(245, 158, 11, 0.35);',
                                    'Half Day' => 'background-color:rgba(99, 102, 241, 0.1); color:#4f46e5; border:1px solid rgba(99, 102, 241, 0.35);',
                                    'Missing Time Out' => 'background-color:rgba(239, 68, 68, 0.1); color:#b91c1c; border:1px solid rgba(239, 68, 68, 0.35);',
                                    'Absent' => 'background-color:rgba(107, 114, 128, 0.1); color:#475569; border:1px solid rgba(107, 114, 128, 0.35);',
                                    'Rest Day' => 'background-color:rgba(59, 130, 246, 0.1); color:#1d4ed8; border:1px solid rgba(59, 130, 246, 0.35);'
                                ];
                                $colorStyle = $statusColors[$log['status']] ?? 'background-color:rgba(107, 114, 128, 0.1); color:#475569;';
                            @endphp
                            <tr style="border-bottom:1px solid var(--s-border); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--s-surface-hover)'" onmouseout="this.style.backgroundColor='transparent'">
                                <td style="padding:16px 20px; font-weight:700; color:#0f172a;">{{ date('D, M d, Y', strtotime($log['date'])) }}</td>
                                <td style="padding:16px 20px; text-align:center; font-weight:700; color:#0f172a;">{{ $log['time_in'] && $log['time_in'] !== '—' ? date('h:i A', strtotime($log['time_in'])) : '' }}</td>
                                <td style="padding:16px 20px; text-align:center; font-weight:700; color:#0f172a;">{{ $log['time_out'] && $log['time_out'] !== '—' ? date('h:i A', strtotime($log['time_out'])) : '' }}</td>
                                <td style="padding:16px 20px; text-align:center; font-weight:700; color:#b45309;">{{ $log['late'] !== '0m' ? $log['late'] : '' }}</td>
                                <td style="padding:16px 20px; text-align:center; font-weight:700; color:#047857;">{{ $log['overtime'] !== '0m' ? $log['overtime'] : '' }}</td>
                                <td style="padding:16px 20px; text-align:center; font-weight:700; color:#0f172a;">{{ $log['total_hours'] > 0 ? $log['total_hours'] . 'h' : '' }}</td>
                                <td style="padding:16px 20px; text-align:center;">
                                    @if($log['status'])
                                        <span style="font-size:11px; padding:4px 10px; font-weight:800; border-radius:6px; text-transform:uppercase; display:inline-block; letter-spacing:0.02em; {{ $colorStyle }}">
                                            {{ $log['status'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding:32px; text-align:center; color:var(--t-tertiary); font-size:14.5px;">
                                    No logs registered in this cutoff range.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Closing for attendanceListView -->
            </div>

            <!-- Calendar View Container -->
            @php
                $firstDay = new \DateTime("{$myYear}-" . str_pad($myMonth, 2, '0', STR_PAD_LEFT) . "-01");
                $daysInMonth = (int)$firstDay->format('t');
                $startOfWeek = (int)$firstDay->format('w'); // 0=Sun, 1=Mon, ..., 6=Sat
                $logsByDate = [];
                foreach ($myLogs as $log) {
                    $logsByDate[$log['date']] = $log;
                }
            @endphp
            <div id="attendanceCalendarView" style="display:none; margin-top:16px;">
                <!-- Day Names Header -->
                <div style="display:grid; grid-template-columns:repeat(7, 1fr); gap:8px; margin-bottom:12px; text-align:center;">
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                        <div style="font-size:12px; font-weight:800; color:var(--t-tertiary); text-transform:uppercase; letter-spacing:0.05em;">{{ $dayName }}</div>
                    @endforeach
                </div>

                <!-- Calendar Days Grid -->
                <div style="display:grid; grid-template-columns:repeat(7, 1fr); gap:8px;">
                    <!-- Trailing blank squares for start of week -->
                    @for($i = 0; $i < $startOfWeek; $i++)
                        <div style="background-color:rgba(241, 245, 249, 0.2); border:1px dashed var(--s-border); border-radius:8px; min-height:105px;"></div>
                    @endfor

                    <!-- Days of the Month -->
                    @for($dayNum = 1; $dayNum <= $daysInMonth; $dayNum++)
                        @php
                            $dateStr = sprintf('%04d-%02d-%02d', $myYear, $myMonth, $dayNum);
                            $isDateActive = ($dateStr >= $myStartDate && $dateStr <= $myEndDate);
                            $cDayOfWeek = (int)(new \DateTime($dateStr))->format('N'); // 1=Mon, 5=Fri, 6=Sat, 7=Sun
                            $log = $logsByDate[$dateStr] ?? null;
                        @endphp
                        
                        <div style="border:1px solid var(--s-border); border-radius:10px; min-height:105px; padding:10px; display:flex; flex-direction:column; justify-content:space-between; transition: transform 0.2s, box-shadow 0.2s;
                            @if($isDateActive)
                                background-color:var(--s-surface);
                                box-shadow: 0 1px 3px rgba(0,0,0,0.02);
                            @else
                                background-color:rgba(241, 245, 249, 0.45);
                                opacity: 0.65;
                            @endif
                        " onmouseover="if({{ $isDateActive ? 'true' : 'false' }}) { this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.05)'; }" onmouseout="this.style.transform='none'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.02)';">
                            <!-- Top Row: Date Number and Day Label -->
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:11px; font-weight:700; color:var(--t-tertiary);">{{ (new \DateTime($dateStr))->format('D') }}</span>
                                <span style="font-size:14px; font-weight:800; @if($isDateActive) color:#0f172a; @else color:var(--t-tertiary); @endif">{{ $dayNum }}</span>
                            </div>

                            <!-- Center Area: Time In / Time Out -->
                            <div style="margin:8px 0; display:flex; flex-direction:column; gap:2px; text-align:center;">
                                @if($isDateActive && $log)
                                    @if($log['time_in'] && $log['time_in'] !== '—')
                                        <div style="font-size:11px; font-weight:700; color:#059669; background-color:rgba(16, 185, 129, 0.08); border-radius:4px; padding:2px 4px; font-family:monospace;">
                                            In: {{ date('h:i A', strtotime($log['time_in'])) }}
                                        </div>
                                    @endif
                                    @if($log['time_out'] && $log['time_out'] !== '—')
                                        <div style="font-size:11px; font-weight:700; color:#4f46e5; background-color:rgba(99, 102, 241, 0.08); border-radius:4px; padding:2px 4px; font-family:monospace;">
                                            Out: {{ date('h:i A', strtotime($log['time_out'])) }}
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <!-- Bottom Row: Status Badge -->
                            <div style="text-align:center;">
                                @if($isDateActive && $log && $log['status'])
                                    @php
                                        $statusColors = [
                                            'Present' => 'background-color:rgba(16, 185, 129, 0.1); color:#047857; border:1px solid rgba(16, 185, 129, 0.3);',
                                            'Late' => 'background-color:rgba(245, 158, 11, 0.1); color:#b45309; border:1px solid rgba(245, 158, 11, 0.3);',
                                            'Half Day' => 'background-color:rgba(99, 102, 241, 0.1); color:#4f46e5; border:1px solid rgba(99, 102, 241, 0.3);',
                                            'Missing Time Out' => 'background-color:rgba(239, 68, 68, 0.1); color:#b91c1c; border:1px solid rgba(239, 68, 68, 0.3);',
                                            'Absent' => 'background-color:rgba(107, 114, 128, 0.1); color:#475569; border:1px solid rgba(107, 114, 128, 0.3);',
                                            'Rest Day' => 'background-color:rgba(59, 130, 246, 0.1); color:#1d4ed8; border:1px solid rgba(59, 130, 246, 0.3);'
                                        ];
                                        $colorStyle = $statusColors[$log['status']] ?? 'background-color:rgba(107, 114, 128, 0.1); color:#475569;';
                                    @endphp
                                    <span style="font-size:9px; padding:2px 6px; font-weight:800; border-radius:4px; text-transform:uppercase; letter-spacing:0.01em; display:inline-block; {{ $colorStyle }}">
                                        {{ $log['status'] }}
                                    </span>
                                @elseif($isDateActive && !$log)
                                    @if($cDayOfWeek === 5 && (new \DateTime($dateStr)) < (new \DateTime()))
                                        <span style="font-size:9px; padding:2px 6px; font-weight:800; border-radius:4px; text-transform:uppercase; background-color:rgba(59, 130, 246, 0.1); color:#1d4ed8; border:1px solid rgba(59, 130, 246, 0.3); display:inline-block;">
                                            REST DAY
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endfor

                    <!-- Leading blank squares for end of week -->
                    @php
                        $totalCells = $startOfWeek + $daysInMonth;
                        $remaining = (7 - ($totalCells % 7)) % 7;
                    @endphp
                    @for($i = 0; $i < $remaining; $i++)
                        <div style="background-color:rgba(241, 245, 249, 0.2); border:1px dashed var(--s-border); border-radius:8px; min-height:105px;"></div>
                    @endfor
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user() && auth()->user()->role === 'admin')
    <!-- STATS CARDS -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:20px;">
        <div class="teacher-panel" style="display:flex; align-items:center; gap:16px; padding:20px;">
            <div style="padding:12px; background-color:rgba(16, 185, 129, 0.1); color:#10b981; border-radius:12px;">
                <i data-lucide="check-square" style="width:24px; height:24px;"></i>
            </div>
            <div>
                <strong style="display:block; font-size:24px; font-weight:800; color:var(--t-primary);">{{ number_format($totalLogs) }}</strong>
                <span style="font-size:12px; font-weight:600; color:var(--t-tertiary); text-transform:uppercase; tracking:0.05em;">Attendance Logs</span>
            </div>
        </div>

        <div class="teacher-panel" style="display:flex; align-items:center; gap:16px; padding:20px;">
            <div style="padding:12px; background-color:rgba(59, 130, 246, 0.1); color:#3b82f6; border-radius:12px;">
                <i data-lucide="users" style="width:24px; height:24px;"></i>
            </div>
            <div>
                <strong style="display:block; font-size:24px; font-weight:800; color:var(--t-primary);">{{ number_format($totalUsers) }}</strong>
                <span style="font-size:12px; font-weight:600; color:var(--t-tertiary); text-transform:uppercase; tracking:0.05em;">Biometric Users</span>
            </div>
        </div>

        <div class="teacher-panel" style="display:flex; align-items:center; gap:16px; padding:20px;">
            <div style="padding:12px; background-color:rgba(139, 92, 246, 0.1); color:#8b5cf6; border-radius:12px;">
                <i data-lucide="briefcase" style="width:24px; height:24px;"></i>
            </div>
            <div>
                <strong style="display:block; font-size:24px; font-weight:800; color:var(--t-primary);">{{ number_format($totalDepts) }}</strong>
                <span style="font-size:12px; font-weight:600; color:var(--t-tertiary); text-transform:uppercase; tracking:0.05em;">Departments</span>
            </div>
        </div>
    </div>

    <!-- MAIN GRID: UPLOAD & SCHEDULER -->
    <div class="dash-split">
        <!-- ZKTeco DAT File Uploader -->
        <div class="teacher-panel" style="padding: 20px;">
            <div style="margin-bottom: 20px;">
                <h2 style="font-size:15px; font-weight:700; color:var(--t-primary); display:flex; align-items:center; gap:8px;">
                    <i data-lucide="upload-cloud" style="color:#10b981;"></i> Import ZKTeco Data Files
                </h2>
                <p style="font-size:11px; color:var(--t-tertiary); margin: 4px 0 0 0;">Upload dat files directly exported from USB devices</p>
            </div>

            <form action="{{ route('teacher.attendance.import') }}" method="POST" enctype="multipart/form-data" class="teacher-form" style="gap:16px;">
                @csrf
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:12px;">
                    <!-- Attendance Logs -->
                    <div style="border: 2px dashed var(--s-border); border-radius:12px; padding:16px; text-align:center; position:relative; cursor:pointer; background-color:var(--s-surface-hover);">
                        <input type="file" name="attlog_file" accept=".dat" style="position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%;">
                        <i data-lucide="file-spreadsheet" style="width:24px; height:24px; color:var(--t-tertiary); margin-bottom:8px;"></i>
                        <div style="font-size:11px; font-weight:700; color:var(--t-secondary);">Attendance Log</div>
                        <div style="font-size:9px; color:var(--t-tertiary); margin-top:2px;">attlog.dat</div>
                    </div>

                    <!-- User Profiles -->
                    <div style="border: 2px dashed var(--s-border); border-radius:12px; padding:16px; text-align:center; position:relative; cursor:pointer; background-color:var(--s-surface-hover);">
                        <input type="file" name="user_file" accept=".dat" style="position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%;">
                        <i data-lucide="users-round" style="width:24px; height:24px; color:var(--t-tertiary); margin-bottom:8px;"></i>
                        <div style="font-size:11px; font-weight:700; color:var(--t-secondary);">User Data</div>
                        <div style="font-size:9px; color:var(--t-tertiary); margin-top:2px;">user.dat</div>
                    </div>

                    <!-- Departments -->
                    <div style="border: 2px dashed var(--s-border); border-radius:12px; padding:16px; text-align:center; position:relative; cursor:pointer; background-color:var(--s-surface-hover);">
                        <input type="file" name="department_file" accept=".dat" style="position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%;">
                        <i data-lucide="network" style="width:24px; height:24px; color:var(--t-tertiary); margin-bottom:8px;"></i>
                        <div style="font-size:11px; font-weight:700; color:var(--t-secondary);">Departments</div>
                        <div style="font-size:9px; color:var(--t-tertiary); margin-top:2px;">department.dat</div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end;">
                    <button type="submit" class="teacher-primary-btn" style="background-color: #059669; border-color: #059669; color: white;">
                        Start Upload & Parse
                    </button>
                </div>
            </form>
        </div>

        <!-- Schedule Configurator -->
        <div class="teacher-panel" style="padding: 20px;">
            <div style="margin-bottom: 20px;">
                <h2 style="font-size:15px; font-weight:700; color:var(--t-primary); display:flex; align-items:center; gap:8px;">
                    <i data-lucide="calendar-range" style="color:#10b981;"></i> Schedule Policy
                </h2>
                <p style="font-size:11px; color:var(--t-tertiary); margin: 4px 0 0 0;">Set standard office hours to calculate lates & undertimes</p>
            </div>

            <form action="{{ route('teacher.attendance') }}" method="GET" class="teacher-form" style="gap:12px;">
                @if($search)
                    <input type="hidden" name="search" value="{{ $search }}">
                @endif
                <label>
                    <span>Expected Time In</span>
                    <input type="time" name="time_in" value="{{ $timeIn }}" style="padding: 6px 10px; font-size: 13px;">
                </label>
                <label>
                    <span>Expected Time Out</span>
                    <input type="time" name="time_out" value="{{ $timeOut }}" style="padding: 6px 10px; font-size: 13px;">
                </label>

                <button type="submit" class="teacher-outline-btn" style="width:100%; min-height:36px;">
                    Apply Schedule Policy
                </button>
            </form>
        </div>
    </div>

    <!-- Alpine tabs state -->
    <div x-data="{ activeTab: 'logs', showForm: false, employee_id: '', name: '', department_id: 0, card_number: '', privilege: 0, password: '' }" style="display:flex; flex-direction:column; gap:20px; width: 100%;">
        
        <!-- TABS NAVIGATION -->
        <div style="display:flex; gap:16px; border-bottom: 1px solid var(--s-border); padding-bottom: 1px;">
            <button @click="activeTab = 'logs'" 
                    :style="activeTab === 'logs' ? 'color:var(--t-primary); border-bottom: 2px solid #10b981; font-weight: 700;' : 'color:var(--t-tertiary);'" 
                    style="background:none; border:none; padding:8px 12px; font-size:13px; cursor:pointer; display:flex; align-items:center; gap:8px;">
                <i data-lucide="file-clock" style="width:14px; height:14px;"></i> Attendance Log Report
            </button>
            <button @click="activeTab = 'users'" 
                    :style="activeTab === 'users' ? 'color:var(--t-primary); border-bottom: 2px solid #10b981; font-weight: 700;' : 'color:var(--t-tertiary);'" 
                    style="background:none; border:none; padding:8px 12px; font-size:13px; cursor:pointer; display:flex; align-items:center; gap:8px;">
                <i data-lucide="users" style="width:14px; height:14px;"></i> Biometric Users Directory
            </button>
        </div>

        <!-- TAB 1: ATTENDANCE LOG REPORT -->
        <div x-show="activeTab === 'logs'" class="teacher-panel" style="display:flex; flex-direction:column; gap:20px; padding: 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div>
                    <h3 style="margin:0; font-size:15px; font-weight:700; color:var(--t-primary);">Daily Attendance Report</h3>
                    <p style="margin:4px 0 0 0; font-size:11px; color:var(--t-tertiary);">Calculated logs sorted by most recent date</p>
                </div>
                
                <form action="{{ route('teacher.attendance') }}" method="GET" style="margin:0; display:flex; align-items:center; relative;">
                    <input type="hidden" name="time_in" value="{{ $timeIn }}">
                    <input type="hidden" name="time_out" value="{{ $timeOut }}">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search ID, Name..." style="padding: 6px 12px 6px 32px; border-radius: 8px; border:1px solid var(--s-border); font-size: 13px; background-color: var(--s-surface); color: var(--t-primary); width: 240px;">
                    <i data-lucide="search" style="width:14px; height:14px; color:var(--t-tertiary); position:absolute; margin-left: 10px;"></i>
                </form>
            </div>

            <div class="teacher-table-scroll">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--s-border); font-weight:800; color:var(--t-primary); font-size:13.5px;">
                            <th style="padding:16px 20px;">Employee</th>
                            <th style="padding:16px 20px;">Department</th>
                            <th style="padding:16px 20px;">Date</th>
                            <th style="padding:16px 20px; text-align:center;">Time In</th>
                            <th style="padding:16px 20px; text-align:center;">Time Out</th>
                            <th style="padding:16px 20px; text-align:center;">Late</th>
                            <th style="padding:16px 20px; text-align:center;">Overtime</th>
                            <th style="padding:16px 20px; text-align:center;">Total</th>
                            <th style="padding:16px 20px; text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody style="color:#334155; font-size:14.5px;">
                        @forelse($report as $row)
                            @php
                                $statusColors = [
                                    'Present' => 'background-color:rgba(16, 185, 129, 0.1); color:#047857; border:1px solid rgba(16, 185, 129, 0.35);',
                                    'Late' => 'background-color:rgba(245, 158, 11, 0.1); color:#b45309; border:1px solid rgba(245, 158, 11, 0.35);',
                                    'Half Day' => 'background-color:rgba(99, 102, 241, 0.1); color:#4f46e5; border:1px solid rgba(99, 102, 241, 0.35);',
                                    'Missing Time Out' => 'background-color:rgba(239, 68, 68, 0.1); color:#b91c1c; border:1px solid rgba(239, 68, 68, 0.35);',
                                    'Absent' => 'background-color:rgba(107, 114, 128, 0.1); color:#475569; border:1px solid rgba(107, 114, 128, 0.35);',
                                    'Rest Day' => 'background-color:rgba(59, 130, 246, 0.1); color:#1d4ed8; border:1px solid rgba(59, 130, 246, 0.35);'
                                ];
                                $colorStyle = $statusColors[$row['status']] ?? 'background-color:rgba(107, 114, 128, 0.1); color:#475569;';
                            @endphp
                            <tr style="border-bottom:1px solid var(--s-border); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--s-surface-hover)'" onmouseout="this.style.backgroundColor='transparent'">
                                <td style="padding:16px 20px;">
                                    <div style="font-weight:700; color:#0f172a;">{{ $row['name'] }}</div>
                                    <div style="font-size:11px; color:var(--t-tertiary); font-family:monospace; font-weight:600;">ID: {{ $row['employee_id'] }}</div>
                                </td>
                                <td style="padding:16px 20px;">{{ $row['department'] }}</td>
                                <td style="padding:16px 20px; font-weight:600;">{{ date('M d, Y', strtotime($row['date'])) }}</td>
                                <td style="padding:16px 20px; text-align:center; font-weight:700; color:#0f172a;">{{ $row['time_in'] && $row['time_in'] !== '—' ? date('h:i A', strtotime($row['time_in'])) : '' }}</td>
                                <td style="padding:16px 20px; text-align:center; font-weight:700; color:#0f172a;">{{ $row['time_out'] && $row['time_out'] !== '—' ? date('h:i A', strtotime($row['time_out'])) : '' }}</td>
                                <td style="padding:16px 20px; text-align:center; font-weight:700; color:#b45309;">{{ $row['late'] !== '0m' ? $row['late'] : '' }}</td>
                                <td style="padding:16px 20px; text-align:center; font-weight:700; color:#047857;">{{ $row['overtime'] !== '0m' ? $row['overtime'] : '' }}</td>
                                <td style="padding:16px 20px; text-align:center; font-weight:700; color:#0f172a;">{{ $row['total_hours'] > 0 ? $row['total_hours'] . 'h' : '' }}</td>
                                <td style="padding:16px 20px; text-align:center;">
                                    @if($row['status'])
                                        <span style="font-size:11px; padding:4px 10px; font-weight:800; border-radius:6px; text-transform:uppercase; display:inline-block; letter-spacing:0.02em; {{ $colorStyle }}">
                                            {{ $row['status'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="padding: 32px; text-align: center; color: var(--t-tertiary); font-size:14.5px;">
                                    <i data-lucide="clock-alert" style="width:24px; height:24px; margin-bottom:8px; color:var(--t-tertiary); display:inline-block; vertical-align:middle;"></i>
                                    No attendance records found. Try importing DAT files.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION CONTROLLER -->
            @if($totalPages > 1)
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px;">
                    <span style="font-size:11px; color:var(--t-tertiary);">
                        Showing page {{ $page }} of {{ $totalPages }} (Total {{ number_format($totalItems) }} entries)
                    </span>
                    <div style="display:flex; gap:8px;">
                        <a href="{{ $page > 1 ? route('teacher.attendance', array_merge(request()->query(), ['page' => $page - 1])) : '#' }}" 
                           class="p-2 border border-slate-800 rounded-xl hover:bg-slate-900 transition {{ $page <= 1 ? 'opacity-50 pointer-events-none' : '' }}" style="border: 1px solid var(--s-border); border-radius: 8px; padding: 6px; display:inline-flex; align-items:center;" title="Previous Page">
                            <i data-lucide="chevron-left" style="width:14px; height:14px;"></i>
                        </a>
                        <a href="{{ $page < $totalPages ? route('teacher.attendance', array_merge(request()->query(), ['page' => $page + 1])) : '#' }}" 
                           class="p-2 border border-slate-800 rounded-xl hover:bg-slate-900 transition {{ $page >= $totalPages ? 'opacity-50 pointer-events-none' : '' }}" style="border: 1px solid var(--s-border); border-radius: 8px; padding: 6px; display:inline-flex; align-items:center;" title="Next Page">
                            <i data-lucide="chevron-right" style="width:14px; height:14px;"></i>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- TAB 2: BIOMETRIC USERS DIRECTORY -->
        <div x-show="activeTab === 'users'" class="teacher-panel" style="display:flex; flex-direction:column; gap:20px; padding: 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                <div>
                    <h3 style="margin:0; font-size:15px; font-weight:700; color:var(--t-primary);">Biometric User Management</h3>
                    <p style="margin:4px 0 0 0; font-size:11px; color:var(--t-tertiary);">Add staff members and download compiled user.dat file</p>
                </div>
                <div style="display:flex; gap:8px;">
                    <button @click="showForm = !showForm" class="teacher-primary-btn" style="background-color: #059669; border-color: #059669; color: white;">
                        <i data-lucide="user-plus"></i> Add Employee
                    </button>
                    <a href="{{ route('teacher.attendance.users.download') }}" class="teacher-outline-btn" style="display:inline-flex; align-items:center; gap:6px;">
                        <i data-lucide="download" style="width:14px; height:14px;"></i> user.dat
                    </a>
                </div>
            </div>

            <!-- Collapsible Edit/Add Form -->
            <div x-show="showForm" style="padding:16px; border:1px solid var(--s-border); border-radius:12px; background-color:var(--s-surface-hover);">
                <h4 style="margin:0 0 16px 0; font-size:13px; font-weight:700; color:var(--t-primary); display:flex; align-items:center; gap:6px;">
                    <i data-lucide="user-cog" style="color:#10b981;"></i> Enter Staff Details
                </h4>

                <form action="{{ route('teacher.attendance.users.store') }}" method="POST" class="teacher-form" style="gap:16px;">
                    @csrf
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
                        <label>
                            <span>Employee ID (PIN)</span>
                            <input type="number" name="employee_id" x-model="employee_id" required placeholder="e.g. 22078" style="padding:6px 10px; font-size:13px;">
                        </label>
                        <label>
                            <span>Full Name</span>
                            <input type="text" name="name" x-model="name" required placeholder="e.g. Sophia Macarimbang" style="padding:6px 10px; font-size:13px;">
                        </label>
                        <label>
                            <span>Card Number</span>
                            <input type="text" name="card_number" x-model="card_number" placeholder="e.g. 0012847590" style="padding:6px 10px; font-size:13px;">
                        </label>
                        <label>
                            <span>Privilege</span>
                            <select name="privilege" x-model="privilege" style="padding:6px 10px; font-size:13px;">
                                <option value="0">Normal User</option>
                                <option value="3">Administrator</option>
                            </select>
                        </label>
                        <label>
                            <span>Department</span>
                            <select name="department_id" x-model="department_id" style="padding:6px 10px; font-size:13px;">
                                <option value="0">Main (0)</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept['id'] }}">{{ $dept['name'] }} ({{ $dept['id'] }})</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Password</span>
                            <input type="text" name="password" x-model="password" placeholder="Optional" style="padding:6px 10px; font-size:13px;">
                        </label>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                        <button type="button" @click="employee_id = ''; name = ''; department_id = 0; card_number = ''; privilege = 0; password = ''; showForm = false;" class="teacher-outline-btn">Cancel</button>
                        <button type="submit" class="teacher-primary-btn" style="background-color: #059669; border-color: #059669; color: white;">Save Employee</button>
                    </div>
                </form>
            </div>

            <!-- Table of users -->
            <div class="teacher-table-scroll">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--s-border); font-size:11px; text-transform:uppercase; font-weight:700; color:var(--t-tertiary);">
                            <th style="padding:12px 16px;">ID (PIN)</th>
                            <th style="padding:12px 16px;">Name</th>
                            <th style="padding:12px 16px;">Card Number</th>
                            <th style="padding:12px 16px;">Privilege</th>
                            <th style="padding:12px 16px;">Dept/Group</th>
                            <th style="padding:12px 16px; text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody style="font-size:13px; color:var(--t-secondary);">
                        @forelse($users as $u)
                            <tr style="border-bottom: 1px solid var(--s-border);">
                                <td style="padding:12px 16px; font-family:monospace; font-weight:700; color:var(--t-primary);">{{ $u['employee_id'] }}</td>
                                <td style="padding:12px 16px; font-weight:600; color:var(--t-primary);">{{ $u['name'] }}</td>
                                <td style="padding:12px 16px; font-family:monospace;">{{ $u['card_number'] ?: '—' }}</td>
                                <td style="padding:12px 16px;">
                                    <span style="font-size:10px; padding:2px 8px; border-radius:6px; background-color:var(--s-surface-hover); border:1px solid var(--s-border);">
                                        {{ in_array($u['privilege'], [3, 14]) ? 'Admin' : 'Normal' }}
                                    </span>
                                </td>
                                <td style="padding:12px 16px;">
                                    @php
                                        $deptName = 'Main';
                                        foreach($departments as $d) {
                                            if($d['id'] == $u['department_id']) {
                                                $deptName = $d['name'];
                                                break;
                                            }
                                        }
                                    @endphp
                                    {{ $deptName }} <span style="font-size:10px; color:var(--t-tertiary); font-family:monospace;">({{ $u['department_id'] }})</span>
                                </td>
                                <td style="padding:12px 16px; text-align:center;">
                                    <div style="display:flex; justify-content:center; gap:16px;">
                                        <button @click="employee_id = {{ $u['employee_id'] }}; name = '{{ addslashes($u['name']) }}'; department_id = {{ $u['department_id'] }}; card_number = '{{ $u['card_number'] }}'; privilege = {{ $u['privilege'] }}; password = '{{ $u['password'] }}'; showForm = true;" 
                                                style="border:none; background:none; color:#2563eb; cursor:pointer; font-weight:700; display:flex; align-items:center; gap:4px; font-size:12px; padding:0;">
                                            <i data-lucide="edit" style="width:13px; height:13px;"></i> Edit
                                        </button>
                                        
                                        <form action="{{ route('teacher.attendance.users.delete', $u['employee_id']) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="margin:0;">
                                            @csrf
                                            <button type="submit" style="border:none; background:none; color:#dc2626; cursor:pointer; font-weight:700; display:flex; align-items:center; gap:4px; font-size:12px; padding:0;">
                                                <i data-lucide="trash-2" style="width:13px; height:13px;"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding:32px; text-align:center; color:var(--t-tertiary);">No users registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
        </div>
    </div>
    @endif
</div>

<script>
    const refreshIcons = () => window.lucide?.createIcons();
    refreshIcons();
    document.addEventListener('DOMContentLoaded', refreshIcons);

    function switchView(view) {
        const listDiv = document.getElementById('attendanceListView');
        const calDiv = document.getElementById('attendanceCalendarView');
        const btnList = document.getElementById('btnListView');
        const btnCal = document.getElementById('btnCalendarView');

        if (!listDiv || !calDiv || !btnList || !btnCal) return;

        if (view === 'list') {
            listDiv.style.display = 'block';
            calDiv.style.display = 'none';
            btnList.style.backgroundColor = 'var(--s-surface)';
            btnList.style.color = 'var(--t-primary)';
            btnList.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
            btnCal.style.backgroundColor = 'transparent';
            btnCal.style.color = 'var(--t-tertiary)';
            btnCal.style.boxShadow = 'none';
            localStorage.setItem('att_preferred_view', 'list');
        } else {
            listDiv.style.display = 'none';
            calDiv.style.display = 'block';
            btnList.style.backgroundColor = 'transparent';
            btnList.style.color = 'var(--t-tertiary)';
            btnList.style.boxShadow = 'none';
            btnCal.style.backgroundColor = 'var(--s-surface)';
            btnCal.style.color = 'var(--t-primary)';
            btnCal.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
            localStorage.setItem('att_preferred_view', 'calendar');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const pref = localStorage.getItem('att_preferred_view') || 'list';
        switchView(pref);
    });
</script>
@endsection
