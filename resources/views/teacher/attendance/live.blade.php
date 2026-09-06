@extends(session('teacher_email') ? 'teacher.layout' : 'teacher.public-layout', ['heading' => 'Live Attendance'])

@section('content')
<div class="space-y-6" style="font-family: var(--font-sans);">
    <!-- Coming Soon Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 text-white p-8 md:p-10 shadow-xl border border-slate-800">
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 via-teal-500/5 to-transparent pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-3 max-w-xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Under Development
                </span>
                <h2 class="text-2xl md:text-3xl font-black tracking-tight">Real-Time Biometric Sync</h2>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Say goodbye to manually downloading USB logs. We are developing an integrated Push SDK service that automatically streams swipes from school biometric machines straight to the AMIS Cloud in real-time.
                </p>
            </div>
            <div class="flex items-center gap-4">
                <div class="p-4 bg-slate-800/80 border border-slate-700/80 rounded-2xl flex flex-col items-center justify-center text-center shadow-lg w-28">
                    <span class="text-2xl font-black text-emerald-400 tracking-tight">Q3</span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Target</span>
                </div>
                <div class="p-4 bg-slate-800/80 border border-slate-700/80 rounded-2xl flex flex-col items-center justify-center text-center shadow-lg w-28">
                    <span class="text-2xl font-black text-emerald-400 tracking-tight">2026</span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Release</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Simulation & Terminal Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Feature Highlights -->
        <div class="lg:col-span-1 space-y-4">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Core Architecture</h3>
            
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                <!-- Card 1 -->
                <div class="flex gap-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <i data-lucide="zap" class="w-5 h-5"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-xs font-extrabold text-slate-800 dark:text-white">Instant Cloud Sync</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">Attendance records sync immediately as users swipe their cards or fingers on the device.</p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="flex gap-4 border-t border-slate-100 dark:border-slate-800/60 pt-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <i data-lucide="message-square" class="w-5 h-5"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-xs font-extrabold text-slate-800 dark:text-white">Insta-Alert Push</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">Instant SMS and mobile notifications sent to administrative channels for real-time compliance tracking.</p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="flex gap-4 border-t border-slate-100 dark:border-slate-800/60 pt-4">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/30 dark:text-violet-400 flex items-center justify-center shrink-0">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-xs font-extrabold text-slate-800 dark:text-white">No-Friction Fallbacks</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">If offline, the biometric reader stores swipes internally and uploads automatically when connection returns.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Simulated Device Terminal View -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Live Connection Console (Simulation)</h3>
                <button type="button" id="btnSimulateSwipe" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-3.5 py-1.5 text-[10px] font-black shadow-md transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="scan-face" class="w-3.5 h-3.5"></i> Simulate Machine Swipe
                </button>
            </div>

            <div class="relative bg-slate-950 rounded-3xl p-5 shadow-inner border border-slate-900 overflow-hidden">
                <!-- Terminal Header buttons -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-900 mb-3">
                    <div class="flex gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="text-[10px] font-bold text-slate-600 font-mono flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> dev-terminal.sh
                    </div>
                </div>

                <!-- Terminal Content Box -->
                <div id="terminalConsole" class="h-64 overflow-y-auto font-mono text-[10px] text-slate-350 space-y-1.5 scrollbar-thin select-none">
                    <p class="text-slate-500">// Welcome to the AMIS Biometric Live Sync Console Mock</p>
                    <p class="text-slate-500">// Press 'Simulate Machine Swipe' above to trigger events</p>
                    <p class="text-slate-400">[15:26:35] INITIALIZING ZKTECO CONNECTION DAEMON...</p>
                    <p class="text-slate-400">[15:26:36] ATTEMPTING HANDSHAKE WITH ZK_DEVICE_01 [192.168.1.201:4370]...</p>
                    <p class="text-emerald-400">[15:26:37] HANDSHAKE SUCCESSFUL: DeviceModel=F18, SerialNo=ZK958100140</p>
                    <p class="text-slate-400">[15:26:38] ACTIVE LISTENER SPAWNED: Awaiting push protocol event requests...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Swipes Today Table Card -->
    <div id="liveSwipesCard" class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm space-y-4 dark:border-slate-800 dark:bg-slate-900 transition-all duration-300">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white" id="liveSwipesTitle">AMIS REAL TIME DATA</h3>
                <p class="text-xs text-slate-500 mt-0.5" id="liveSwipesDesc">Real-time attendance swipe feed streaming from biometric terminals.</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Auto Stream Switch -->
                <label class="inline-flex items-center gap-2 cursor-pointer bg-slate-50 dark:bg-slate-800/50 px-3 py-1.5 rounded-xl border border-slate-100 dark:border-slate-800">
                    <input type="checkbox" id="chkAutoStream" checked class="sr-only peer">
                    <span class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Auto Swipes</span>
                    <div class="relative w-8 h-4 bg-slate-250 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-350 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>

                <!-- Full Screen Toggle Button -->
                <button type="button" onclick="toggleFullScreen()" class="rounded-xl border border-slate-250 bg-white px-3.5 py-2 text-[10px] font-black text-slate-700 hover:bg-slate-50 transition active:scale-95 flex items-center gap-1.5 cursor-pointer dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                    <i id="fullScreenIcon" data-lucide="maximize-2" class="w-3.5 h-3.5"></i> <span id="fullScreenText">Full Screen</span>
                </button>
                
                <span class="inline-flex items-center gap-1.5 px-2.5 py-2 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active Feed
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs align-middle">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-450 border-b border-slate-100 dark:bg-slate-950 dark:border-slate-800">
                        <th class="px-4 py-3">Full Name</th>
                        <th class="px-4 py-3">Time</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="liveSwipesBody" class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-4 py-3.5 font-extrabold text-slate-900 uppercase dark:text-white">SABTAL, FAIDHURRAHMAN</td>
                        <td class="px-4 py-3.5 font-semibold text-slate-500">03:20:12 PM</td>
                        <td class="px-4 py-3.5 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-400">TIME IN (FINGERPRINT)</span></td>
                    </tr>
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-4 py-3.5 font-extrabold text-slate-900 uppercase dark:text-white">FERNANDEZ, ROWENA</td>
                        <td class="px-4 py-3.5 font-semibold text-slate-500">03:15:34 PM</td>
                        <td class="px-4 py-3.5 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-950/30 dark:text-blue-400">TIME OUT (RFID CARD)</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Welcome Greeting Overlay -->
    <div id="greetingOverlay" class="fixed inset-0 z-200 flex flex-col items-center justify-center bg-white/98 backdrop-blur-md transition-all duration-500 opacity-0 pointer-events-none">
        <div class="scale-90 transform transition-all duration-500 ease-out bg-white border border-slate-100 rounded-3xl p-10 max-w-lg w-full text-center space-y-6 shadow-2xl relative" id="greetingCard">
            <!-- Glowing Circle with Status Icon -->
            <div class="mx-auto w-24 h-24 rounded-full flex items-center justify-center border-4 border-emerald-500/20 bg-emerald-50 text-emerald-600 relative" id="greetingIconBg">
                <div class="absolute inset-0 rounded-full border border-emerald-500 animate-ping opacity-15"></div>
                <i id="greetingIcon" data-lucide="check" class="w-12 h-12"></i>
            </div>
            
            <div class="space-y-2">
                <h4 class="text-xs font-black uppercase tracking-widest text-emerald-600" id="greetingStatusText">PRESENT</h4>
                <h2 class="text-3xl font-black tracking-tight text-slate-900 uppercase" id="greetingName">SABTAL, FAIDHURRAHMAN</h2>
                <p class="text-sm font-semibold text-slate-500" id="greetingSub">Biometric Verification Successful</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-6">
                <div>
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Swipe Time</span>
                    <strong class="text-xl font-black text-slate-900 mt-1 block" id="greetingTime">07:22:15 AM</strong>
                </div>
                <div>
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Method</span>
                    <strong class="text-xs font-black text-slate-600 mt-1.5 block uppercase" id="greetingMethod">Fingerprint</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleFullScreen() {
        const card = document.getElementById('liveSwipesCard');
        const isFullScreen = card.classList.contains('fixed');
        const title = document.getElementById('liveSwipesTitle');
        const desc = document.getElementById('liveSwipesDesc');
        
        if (!isFullScreen) {
            // Enter Full Screen
            card.classList.add('fixed', 'inset-0', 'z-150', 'bg-white', 'dark:bg-slate-900', 'p-8', 'overflow-y-auto', 'w-screen', 'h-screen');
            card.classList.remove('rounded-3xl', 'border');
            title.classList.replace('text-sm', 'text-2xl');
            desc.classList.replace('text-xs', 'text-sm');
            document.getElementById('fullScreenText').textContent = 'Exit Full Screen';
            
            // Set standard body overflow hidden to prevent double scrollbar
            document.body.style.overflow = 'hidden';
        } else {
            // Exit Full Screen
            card.classList.remove('fixed', 'inset-0', 'z-150', 'bg-white', 'dark:bg-slate-900', 'p-8', 'overflow-y-auto', 'w-screen', 'h-screen');
            card.classList.add('rounded-3xl', 'border', 'border-slate-200/80');
            title.classList.replace('text-2xl', 'text-sm');
            desc.classList.replace('text-sm', 'text-xs');
            document.getElementById('fullScreenText').textContent = 'Full Screen';
            
            // Restore body overflow
            document.body.style.overflow = '';
        }
        
        // Toggle icon using vanilla update or just wait for lucide re-render
        setTimeout(() => {
            const icon = document.getElementById('fullScreenIcon');
            if (icon) {
                icon.setAttribute('data-lucide', isFullScreen ? 'maximize-2' : 'minimize-2');
                window.lucide?.createIcons();
            }
        }, 50);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const consoleEl = document.getElementById('terminalConsole');
        const btnSimulate = document.getElementById('btnSimulateSwipe');
        const tableBody = document.getElementById('liveSwipesBody');
        const chkAutoStream = document.getElementById('chkAutoStream');
        
        const mockNames = [
            'SABTAL, FAIDHURRAHMAN',
            'DIBARATAN, SAIMONA',
            'SUMONDONG, JENNY JANE',
            'AMERKHAN, FHAIRUDZ',
            'AMPUL, JUNAISAH',
            'AMPUAN, ZHHORA',
            'ADSAMAN, HAINUR',
            'FERNANDEZ, ROWENA',
            'GECALE, ANGELENI',
            'JUSTINIANE, ETHEL',
            'LINGASA, MON ZHAIREL'
        ];

        const mockIds = [1102, 1103, 1104, 1105, 1106, 1110, 1107, 22084, 22085, 22086, 1108];

        function appendConsoleLine(text, colorClass = 'text-slate-400') {
            const timeStr = new Date().toLocaleTimeString('en-US', { hour12: false });
            const p = document.createElement('p');
            p.className = colorClass;
            p.textContent = `[${timeStr}] ${text}`;
            consoleEl.appendChild(p);
            consoleEl.scrollTop = consoleEl.scrollHeight;
        }

        // Auto append heartbeat lines
        setInterval(() => {
            const r = Math.random();
            if (r < 0.25) {
                appendConsoleLine('HEARTBEAT: Connection ZK_DEVICE_01 is alive. (Ping 14ms)', 'text-slate-600');
            }
        }, 12000);

        // Core swipe trigger logic
        function triggerSwipeSimulation() {
            const idx = Math.floor(Math.random() * mockNames.length);
            const name = mockNames[idx];
            const empId = mockIds[idx];

            const methods = ['FINGERPRINT', 'RFID CARD', 'FACE ID'];
            const randomMethod = methods[Math.floor(Math.random() * methods.length)];

            // Generate realistic swipe parameters based on check-in context
            const rSwipe = Math.random();
            let timeStr = "";
            let statusText = "";
            let statusBadgeClass = "";
            
            const minutesVal = Math.floor(Math.random() * 60);
            const secondsVal = Math.floor(Math.random() * 60);
            const minPad = String(minutesVal).padStart(2, '0');
            const secPad = String(secondsVal).padStart(2, '0');

            if (rSwipe < 0.60) {
                // TIME IN (either PRESENT or LATE)
                timeStr = `07:${minPad}:${secPad} AM`;
                if (minutesVal <= 30) {
                    statusText = "PRESENT";
                    statusBadgeClass = "bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-400";
                } else {
                    statusText = "LATE";
                    statusBadgeClass = "bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-950/30 dark:text-amber-400";
                }
            } else if (rSwipe < 0.75) {
                // ABSENT
                timeStr = `09:${minPad}:${secPad} AM`;
                statusText = "ABSENT";
                statusBadgeClass = "bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-950/30 dark:text-rose-450";
            } else {
                // TIME OUT
                const hr = Math.random() < 0.5 ? "04" : "05";
                timeStr = `${hr}:${minPad}:${secPad} PM`;
                statusText = "TIME OUT";
                statusBadgeClass = "bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-950/30 dark:text-blue-400";
            }

            appendConsoleLine(`SWIPE TRIGGERED ON TERMINAL ZK_DEVICE_01 (PIN #${empId})`, 'text-indigo-400');
            
            setTimeout(() => {
                appendConsoleLine(`RECORD PARSED: ${name} (PIN ${empId}) verified successfully via ${randomMethod}.`, 'text-slate-300');
            }, 400);

            setTimeout(() => {
                appendConsoleLine(`CLOUDSYNC: Broadcasting payload to AMIS API server...`, 'text-slate-500');
            }, 800);

            setTimeout(() => {
                appendConsoleLine(`SYNC SUCCESSFUL: Recorded swipe for user #${empId} in database.`, 'text-emerald-400');
                
                // Show Welcome Overlay Pop-up
                const overlay = document.getElementById('greetingOverlay');
                const gCard = document.getElementById('greetingCard');
                const gName = document.getElementById('greetingName');
                const gStatus = document.getElementById('greetingStatusText');
                const gTime = document.getElementById('greetingTime');
                const gMethod = document.getElementById('greetingMethod');
                const gIcon = document.getElementById('greetingIcon');
                const gIconBg = document.getElementById('greetingIconBg');

                const displayMethod = statusText === 'ABSENT' ? 'SYSTEM' : randomMethod;

                gName.textContent = name;
                gTime.textContent = timeStr;
                gMethod.textContent = displayMethod;
                gStatus.textContent = statusText;

                // Color code and icon swap based on status
                if (statusText === "PRESENT") {
                    gStatus.className = "text-xs font-black uppercase tracking-widest text-emerald-600";
                    gIconBg.className = "mx-auto w-24 h-24 rounded-full flex items-center justify-center border-4 border-emerald-500/20 bg-emerald-50 text-emerald-600 relative";
                    gIcon.setAttribute('data-lucide', 'check');
                } else if (statusText === "LATE") {
                    gStatus.className = "text-xs font-black uppercase tracking-widest text-amber-500";
                    gIconBg.className = "mx-auto w-24 h-24 rounded-full flex items-center justify-center border-4 border-amber-500/20 bg-amber-50 text-amber-600 relative";
                    gIcon.setAttribute('data-lucide', 'clock');
                } else if (statusText === "ABSENT") {
                    gStatus.className = "text-xs font-black uppercase tracking-widest text-rose-600";
                    gIconBg.className = "mx-auto w-24 h-24 rounded-full flex items-center justify-center border-4 border-rose-500/20 bg-rose-50 text-rose-600 relative";
                    gIcon.setAttribute('data-lucide', 'x-circle');
                } else {
                    gStatus.className = "text-xs font-black uppercase tracking-widest text-blue-600";
                    gIconBg.className = "mx-auto w-24 h-24 rounded-full flex items-center justify-center border-4 border-blue-500/20 bg-blue-50 text-blue-600 relative";
                    gIcon.setAttribute('data-lucide', 'log-out');
                }
                window.lucide?.createIcons();

                // Open overlay (animate in)
                overlay.classList.remove('opacity-0', 'pointer-events-none');
                gCard.classList.replace('scale-90', 'scale-100');

                // Prepend new row to table
                const newRow = document.createElement('tr');
                newRow.className = 'hover:bg-slate-50/50 transition bg-emerald-500/10 dark:bg-emerald-500/5';
                newRow.innerHTML = `
                    <td class="px-4 py-3.5 font-extrabold text-slate-900 uppercase dark:text-white">${name}</td>
                    <td class="px-4 py-3.5 font-semibold text-slate-500">${timeStr}</td>
                    <td class="px-4 py-3.5 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black ${statusBadgeClass} border">${statusText} (${displayMethod})</span></td>
                `;
                tableBody.insertBefore(newRow, tableBody.firstChild);

                // Auto-close overlay after 3.2 seconds
                setTimeout(() => {
                    overlay.classList.add('opacity-0', 'pointer-events-none');
                    gCard.classList.replace('scale-100', 'scale-90');
                }, 3200);

                // Fade highlight background after 3 seconds
                setTimeout(() => {
                    newRow.classList.remove('bg-emerald-500/10', 'dark:bg-emerald-500/5');
                }, 3000);
                
            }, 1200);
        }

        // Click handler to manually simulate a swipe
        if (btnSimulate) {
            btnSimulate.addEventListener('click', triggerSwipeSimulation);
        }

        // Auto Swipes simulation interval (every 8 seconds if checked)
        setInterval(() => {
            if (chkAutoStream && chkAutoStream.checked) {
                triggerSwipeSimulation();
            }
        }, 8000);
    });
</script>
@endsection
