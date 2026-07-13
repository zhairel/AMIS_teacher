@extends(session('teacher_email') ? 'teacher.layout' : 'teacher.public-layout', ['heading' => 'Faculty Digital ID'])

@section('content')
<div style="display:flex; flex-direction:column; align-items:center; gap:24px; width: 100%; max-width: 600px; margin: 0 auto; padding: 20px 0;">

    @if($myBiometricId)
        {{-- Premium Faculty ID Card --}}
        <div style="background: var(--s-surface); border: 1px solid var(--s-border); border-radius: var(--r-2xl); box-shadow: var(--shadow-xl); overflow: hidden; width: 100%; transition: transform 0.3s var(--ease); position: relative;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
            
            {{-- Emerald Top Accent Stripe --}}
            <div style="height: 12px; background: linear-gradient(90deg, #059669 0%, #10b981 100%);"></div>

            {{-- Card Inner Layout --}}
            <div style="padding: 32px; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 24px;">
                
                {{-- Logo and Header --}}
                <div style="display: flex; align-items: center; gap: 10px; width: 100%; justify-content: center; padding-bottom: 16px; border-bottom: 1px solid var(--s-border);">
                    <img src="{{ asset('images/AMIS_Logo.png') }}" alt="AMIS Logo" style="width: 32px; height: 32px; object-fit: contain;">
                    <div>
                        <span style="font-size: 14px; font-weight: 850; color: var(--t-primary); display: block; letter-spacing: -0.3px; line-height: 1.2;">AL MUNAWWARA</span>
                        <span style="font-size: 10px; font-weight: 700; color: var(--t-tertiary); display: block; text-transform: uppercase; letter-spacing: 0.5px;">ISLAMIC SCHOOL</span>
                    </div>
                </div>

                {{-- Teacher Avatar / Initials --}}
                <div style="width: 90px; height: 90px; border-radius: var(--r-full); background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 900; border: 4px solid var(--s-bg); box-shadow: var(--shadow-sm);">
                    {{ strtoupper(substr($displayName, 0, 2)) }}
                </div>

                {{-- Teacher Info --}}
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <h2 style="font-size: 22px; font-weight: 850; color: var(--t-primary); margin: 0; letter-spacing: -0.4px;">{{ $displayName }}</h2>
                    <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; padding: 4px 12px; border-radius: 999px; background: rgba(16, 185, 129, 0.12); color: #047857; display: inline-block; align-self: center; letter-spacing: 0.8px;">
                        FACULTY MEMBER
                    </span>
                    <p style="font-size: 13px; font-weight: 600; color: var(--t-secondary); margin: 8px 0 0 0;">{{ $departmentName }}</p>
                </div>

                {{-- QR and Barcode Tabs for Scanner Compatibility --}}
                <div x-data="{ activeCode: 'qr' }" style="width: 100%; border-top: 1px dashed var(--s-border); padding-top: 24px; display: flex; flex-direction: column; align-items: center; gap: 16px;">
                    
                    {{-- Code Type Switcher --}}
                    <div style="display: flex; background: var(--s-surface-hover); border: 1px solid var(--s-border); border-radius: 10px; padding: 3px; gap: 4px;">
                        <button type="button" @click="activeCode = 'qr'" :class="activeCode === 'qr' ? 'bg-white text-emerald-700 shadow-3xs font-extrabold' : 'text-slate-500 hover:text-slate-800 font-bold'" style="padding: 6px 14px; font-size: 11.5px; border-radius: 8px; border: none; cursor: pointer; transition: all 0.2s; outline: none;">
                            QR Code Scanner
                        </button>
                        <button type="button" @click="activeCode = 'barcode'" :class="activeCode === 'barcode' ? 'bg-white text-emerald-700 shadow-3xs font-extrabold' : 'text-slate-500 hover:text-slate-800 font-bold'" style="padding: 6px 14px; font-size: 11.5px; border-radius: 8px; border: none; cursor: pointer; transition: all 0.2s; outline: none;">
                            Barcode Scanner
                        </button>
                    </div>

                    {{-- QR Code Image Container --}}
                    <div x-show="activeCode === 'qr'" style="background: white; border: 1px solid var(--s-border); padding: 16px; border-radius: 16px; box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: center; width: 200px; height: 200px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                        <img src="{{ $qrCodeUrl }}" alt="Faculty Attendance QR Code" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>

                    {{-- Barcode Image Container --}}
                    <div x-show="activeCode === 'barcode'" style="background: white; border: 1px solid var(--s-border); padding: 24px 16px; border-radius: 16px; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; min-height: 120px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                        <img src="{{ $barcodeUrl }}" alt="Faculty Attendance Barcode" style="max-width: 100%; height: 65px; object-fit: contain;">
                    </div>

                    {{-- PIN Number Display --}}
                    <div style="font-size: 14px; font-family: monospace; font-weight: 800; color: var(--t-primary); background: var(--s-surface-hover); padding: 6px 14px; border-radius: 8px; border: 1px solid var(--s-border);">
                        EMPLOYEE ID: {{ $myBiometricId }}
                    </div>
                </div>

                {{-- Usage Instructions --}}
                <div style="background: rgba(59, 130, 246, 0.04); border: 1px solid rgba(59, 130, 246, 0.1); border-radius: 12px; padding: 12px 16px; display: flex; gap: 10px; align-items: flex-start; text-align: left;">
                    <i data-lucide="info" style="color: #3b82f6; width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px;"></i>
                    <p style="margin: 0; font-size: 11.5px; color: var(--t-secondary); line-height: 1.45;">
                        <strong style="color: var(--t-primary); display: block; margin-bottom: 2px;">Attendance Logging Instructions</strong>
                        Align this code in front of the lobby scanner/camera to log your attendance. The scanner will automatically register your Employee ID.
                    </p>
                </div>

                @if(!$isAuthenticated)
                    <div style="border-top: 1px solid var(--s-border); width: 100%; padding-top: 16px; display: flex; justify-content: center;">
                        <a href="{{ route('teacher.id') }}" style="font-size: 12.5px; font-weight: 700; color: #059669; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i> Back to Inquiry Verification
                        </a>
                    </div>
                @endif

            </div>
        </div>

    @else
        {{-- Inquiry / Verification Form --}}
        @if(session('teacher_email'))
            {{-- Logged in link profile form --}}
            <div style="background: var(--s-surface); border: 1px solid var(--s-border); border-radius: var(--r-xl); padding: 30px; box-shadow: var(--shadow-md); width: 100%;">
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(59, 130, 246, 0.1); display: inline-flex; align-items: center; justify-content: center; color: #3b82f6; margin-bottom: 12px;">
                        <i data-lucide="link" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h3 style="font-size: 18px; font-weight: 850; color: var(--t-primary); margin: 0;">Link Biometric Account</h3>
                    <p style="font-size: 12px; color: var(--t-tertiary); margin: 6px 0 0 0;">Please select your name from the biometric directory to activate and view your Digital ID card.</p>
                </div>

                <form method="POST" action="{{ route('teacher.attendance.link') }}" class="teacher-form" style="margin:0;">
                    @csrf
                    <label style="margin-bottom: 16px;">
                        <span style="font-size: 11px; font-weight: 700; color: var(--t-secondary); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Choose Your Biometric Profile</span>
                        <select name="biometric_id" required style="padding: 10px 14px; font-size:13.5px; border-radius:10px; width:100%; border: 1px solid var(--s-border); background-color: var(--s-surface); color: var(--t-primary); font-weight: 600;">
                            <option value="" disabled selected>Choose profile...</option>
                            @foreach($zkUsers as $u)
                                <option value="{{ $u['employee_id'] }}">{{ $u['name'] }} (ID: {{ $u['employee_id'] }})</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="teacher-primary-btn" style="background-color: #059669; border-color: #059669; color: white; padding: 12px 20px; font-size: 13.5px; font-weight: 800; border-radius: 10px; cursor: pointer; width: 100%; text-transform: uppercase; letter-spacing: 0.05em;">
                        Link & Display ID Card
                    </button>
                </form>
            </div>
        @else
            {{-- Public lookup (Faculty Verification) --}}
            <div style="background: var(--s-surface); border: 1px solid var(--s-border); padding: 32px; border-radius: var(--r-2xl); box-shadow: var(--shadow-xl); width: 100%;">
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="width: 54px; height: 54px; border-radius: 50%; background-color: rgba(16, 185, 129, 0.1); display: inline-flex; align-items: center; justify-content: center; color: #10b981; margin-bottom: 12px;">
                        <i data-lucide="shield-check" style="width: 28px; height: 28px;"></i>
                    </div>
                    <h2 style="font-size: 20px; font-weight: 850; color: var(--t-primary); margin: 0; letter-spacing: -0.3px;">Faculty ID Card lookup</h2>
                    <p style="font-size: 12.5px; color: var(--t-tertiary); margin: 6px 0 0 0;">Verify your identity to retrieve and display your digital keycard for logging attendance.</p>
                </div>

                <form method="GET" action="{{ route('teacher.id') }}" class="teacher-form" style="display: flex; flex-direction: column; gap: 16px; margin: 0;" onsubmit="return validateSearchForm()">
                    <label style="margin: 0;">
                        <span style="font-size: 11px; font-weight: 700; color: var(--t-secondary); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Enter Full Name</span>
                        <input type="text" name="search_name" id="searchNameInput" placeholder="e.g. ZHAIREL LINGASA" style="padding: 10px 14px; font-size: 13.5px; border-radius: 10px; width: 100%; border: 1px solid var(--s-border); background-color: var(--s-surface); color: var(--t-primary); font-weight: 600; text-transform: uppercase;" onfocus="document.getElementById('searchIdInput').value = ''" oninput="this.value = this.value.toUpperCase()">
                    </label>

                    <div style="display: flex; align-items: center; justify-content: center; margin: 8px 0; position: relative;">
                        <span style="height: 1px; background-color: var(--s-border); flex: 1;"></span>
                        <span style="font-size: 10px; font-weight: 800; color: var(--t-tertiary); text-transform: uppercase; padding: 0 12px; background-color: var(--s-surface); position: relative; z-index: 2;">OR</span>
                        <span style="height: 1px; background-color: var(--s-border); flex: 1;"></span>
                    </div>

                    <label style="margin: 0;">
                        <span style="font-size: 11px; font-weight: 700; color: var(--t-secondary); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Enter Employee ID / Biometric PIN</span>
                        <input type="text" name="biometric_id" id="searchIdInput" pattern="[0-9]*" inputmode="numeric" placeholder="e.g. 22078" style="padding: 10px 14px; font-size: 13.5px; border-radius: 10px; width: 100%; border: 1px solid var(--s-border); background-color: var(--s-surface); color: var(--t-primary); font-weight: 600;" onfocus="document.getElementById('searchNameInput').value = ''">
                    </label>

                    <button type="submit" class="teacher-primary-btn" style="background-color: #059669; border-color: #059669; color: white; padding: 12px 20px; font-size: 13.5px; font-weight: 800; border-radius: 10px; cursor: pointer; width: 100%; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 10px;">
                        Verify & View ID Card
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
    @endif

</div>
@endsection
