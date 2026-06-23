<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — CineVault</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --bg:#0a0a0f; --bg2:#12121a; --bg3:#1a1a26; --border:#2a2a42; --gold:#c8a04a; --gold2:#e8c46a; --text:#f0ede8; --text2:#b8b4aa; --text3:#787068; --red:#e05252; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { background:var(--bg); color:var(--text); font-family:'Inter',system-ui,sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .form-input { background:var(--bg3); border:1px solid var(--border); border-radius:6px; padding:10px 14px; color:var(--text); font-size:13px; width:100%; outline:none; }
        .form-input:focus { border-color:var(--gold); box-shadow:0 0 0 2px rgba(200,160,74,.15); }
        .btn-primary { background:var(--gold); color:#000; border:none; border-radius:6px; padding:11px 20px; font-size:14px; font-weight:600; cursor:pointer; width:100%; transition:background .15s; }
        .btn-primary:hover { background:var(--gold2); }
        .alert-error { background:rgba(224,82,82,.1); border:1px solid rgba(224,82,82,.25); color:var(--red); padding:10px 14px; border-radius:6px; font-size:12px; margin-bottom:16px; }
    </style>
</head>
<body>
<div style="width:100%; max-width:380px; padding:20px;">
    {{-- Logo --}}
    <div style="text-align:center; margin-bottom:32px;">
        <svg width="52" height="52" viewBox="0 0 34 34" fill="none" style="display:inline-block; margin-bottom:12px;">
            <rect width="34" height="34" rx="7" fill="#1a1a26"/>
            <rect x="5" y="8" width="24" height="14" rx="2" fill="none" stroke="#c8a04a" stroke-width="1.4"/>
            <rect x="5" y="10" width="3" height="2.5" rx=".5" fill="#c8a04a"/>
            <rect x="5" y="14" width="3" height="2.5" rx=".5" fill="#c8a04a"/>
            <rect x="5" y="18" width="3" height="2.5" rx=".5" fill="#c8a04a"/>
            <rect x="26" y="10" width="3" height="2.5" rx=".5" fill="#c8a04a"/>
            <rect x="26" y="14" width="3" height="2.5" rx=".5" fill="#c8a04a"/>
            <rect x="26" y="18" width="3" height="2.5" rx=".5" fill="#c8a04a"/>
            <path d="M14 13l4.5 2.5L14 18V13z" fill="#c8a04a"/>
            <rect x="9" y="24" width="16" height="2" rx="1" fill="#c8a04a" opacity=".4"/>
        </svg>
        <div style="font-size:24px; font-weight:700; letter-spacing:-.5px;">Cine<span style="color:var(--gold)">Vault</span></div>
        <div style="font-size:11px; color:var(--text3); letter-spacing:2px; text-transform:uppercase; margin-top:4px;">Movie Rental System</div>
    </div>

    {{-- Card --}}
    <div style="background:var(--bg2); border:1px solid var(--border); border-radius:14px; padding:28px;">
        <h2 style="font-size:18px; font-weight:600; margin-bottom:6px;">Sign In</h2>
        <p style="font-size:12px; color:var(--text3); margin-bottom:22px;">Enter your credentials to access the system.</p>

        @if($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        @if(session('status'))
            <div style="background:rgba(82,192,122,.1); border:1px solid rgba(82,192,122,.25); color:#52c07a; padding:10px 14px; border-radius:6px; font-size:12px; margin-bottom:16px;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:12px; color:var(--text2); font-weight:500; margin-bottom:5px;">Email</label>
                <input class="form-input" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@cinevault.ph">
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:12px; color:var(--text2); font-weight:500; margin-bottom:5px;">Password</label>
                <input class="form-input" type="password" name="password" required placeholder="••••••••">
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                <label style="display:flex; align-items:center; gap:7px; cursor:pointer; font-size:12px; color:var(--text2);">
                    <input type="checkbox" name="remember" style="accent-color:var(--gold); width:14px; height:14px;">
                    Remember me
                </label>
            </div>
            <button type="submit" class="btn-primary">Sign In</button>
        </form>

        {{-- Demo accounts hint --}}
        <div style="margin-top:20px; padding-top:20px; border-top:1px solid var(--border);">
            <div style="font-size:11px; color:var(--text3); margin-bottom:10px; text-transform:uppercase; letter-spacing:.8px;">Demo Accounts</div>
            <div style="display:flex; flex-direction:column; gap:6px;">
                @foreach([['admin@cinevault.ph','Admin','#c8a04a'],['staff@cinevault.ph','Staff','#5294e0'],['user@cinevault.ph','User','#9452e0']] as [$email,$role,$color])
                <button onclick="document.querySelector('[name=email]').value='{{ $email }}'; document.querySelector('[name=password]').value='password';"
                        style="background:var(--bg3); border:1px solid var(--border); border-radius:6px; padding:8px 12px; cursor:pointer; display:flex; align-items:center; gap:8px; transition:background .15s;"
                        onmouseover="this.style.background='#22223a'" onmouseout="this.style.background='var(--bg3)'">
                    <span style="width:24px; height:24px; border-radius:50%; background:{{ $color }}22; border:1px solid {{ $color }}44; display:flex; align-items:center; justify-content:center; font-size:9px; font-weight:700; color:{{ $color }}; flex-shrink:0;">{{ substr($role,0,2) }}</span>
                    <div style="text-align:left;">
                        <div style="font-size:12px; color:var(--text); font-weight:500;">{{ $role }}</div>
                        <div style="font-size:10px; color:var(--text3);">{{ $email }}</div>
                    </div>
                    <span style="margin-left:auto; font-size:10px; color:{{ $color }}; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">{{ $role }}</span>
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <div style="text-align:center; margin-top:16px; font-size:11px; color:var(--text3);">
        CineVault © {{ date('Y') }} · Movie Rental Inventory System
    </div>
</div>
</body>
</html>
