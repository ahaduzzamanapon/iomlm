<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — IOM ERP</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            min-height: 100vh;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        .login-bg {
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse at 15% 20%, rgba(59,130,246,.08) 0%, transparent 50%),
                radial-gradient(ellipse at 85% 80%, rgba(99,102,241,.06) 0%, transparent 50%),
                #f8fafc;
            z-index: 0;
        }

        .login-card {
            position: relative; z-index: 1;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 36px;
            width: 100%; max-width: 450px;
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.08), 0 0 1px rgba(15, 23, 42, 0.1);
        }

        .login-logo {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 20px;
        }
        .login-logo-icon {
            width: 48px; height: 48px;
            display: flex; align-items: center; justify-content: center;
        }
        .login-logo-text { line-height: 1.2; }
        .login-logo-name { font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; }
        .login-logo-sub  { font-size: 12px; color: #64748b; font-weight: 500; }

        .login-title { font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 4px; letter-spacing: -0.5px; }
        .login-subtitle { font-size: 13px; color: #64748b; margin-bottom: 20px; }

        .lf-group { margin-bottom: 16px; }
        .lf-label {
            display: block; font-size: 13px; font-weight: 600;
            color: #334155; margin-bottom: 6px;
        }
        .lf-input {
            width: 100%; padding: 11px 14px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            color: #0f172a; font-size: 14px;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: all .2s;
            box-sizing: border-box;
        }
        .lf-input:focus {
            border-color: #2563eb;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(37,99,235,.15);
        }
        .lf-input::placeholder { color: #94a3b8; }

        .lf-check {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: #475569; cursor: pointer; margin-top: 6px; font-weight: 500;
        }
        .lf-check input { accent-color: #2563eb; width: 15px; height: 15px; }

        .btn-login {
            width: 100%; padding: 12px;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: #fff; font-size: 15px; font-weight: 700;
            border: none; border-radius: 10px; cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: all .2s;
            margin-top: 14px;
            box-shadow: 0 4px 12px rgba(37,99,235,.25);
        }
        .btn-login:hover { opacity: .95; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,.35); }

        .btn-google {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; padding: 11px;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            color: #0f172a; font-size: 14px; font-weight: 600;
            font-family: 'Inter', sans-serif;
            transition: all .2s;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .btn-google:hover {
            border-color: #4285F4;
            background: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.15);
        }

        .error-msg {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 8px; padding: 10px 14px;
            font-size: 13px; margin-bottom: 16px; font-weight: 500;
        }
    </style>
</head>
<body>
<div class="login-bg"></div>

<div class="login-card">
    <!-- Logo -->
    <div class="login-logo">
        <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" style="width:48px;height:48px;object-fit:contain">
        <div class="login-logo-text">
            <div class="login-logo-name">IOM ERP</div>
            <div class="login-logo-sub">Institute Management System</div>
        </div>
    </div>

    <div class="login-title">Sign In</div>
    <div class="login-subtitle">Enter your credentials to access the admin panel</div>

    @if($errors->any())
    <div class="error-msg">
        {{ $errors->first() }}
    </div>
    @endif

    @if(session('error'))
    <div class="error-msg">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <div class="lf-group">
            <label class="lf-label" for="email">Email Address or Student ID</label>
            <input
                id="email"
                type="text"
                name="email"
                class="lf-input"
                value="{{ old('email') }}"
                placeholder="e.g. 26-15-01-1-0001 or email@domain.com"
                autocomplete="username"
                required
            >
        </div>

        <div class="lf-group">
            <label class="lf-label" for="password">Password</label>
            <div style="position:relative;display:flex;align-items:center">
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="lf-input"
                    style="padding-right:44px"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                >
                <button type="button" onclick="togglePasswordVisibility('password', this)" title="Show/Hide Password" style="position:absolute;right:8px;background:transparent;border:none;padding:6px;cursor:pointer;color:#64748b;display:flex;align-items:center;justify-content:center;border-radius:6px;transition:all 0.2s">
                    <i class="fa-solid fa-eye eye-show" style="font-size:16px"></i>
                    <i class="fa-solid fa-eye-slash eye-hide" style="display:none;font-size:16px"></i>
                </button>
            </div>
        </div>

        <label class="lf-check">
            <input type="checkbox" name="remember" checked> Remember me
        </label>

        <button type="submit" class="btn-login">Sign In →</button>
    </form>

    @if(\App\Models\Setting::get('google_auth_enabled', '0') == '1')
    <div style="display:flex;align-items:center;margin:20px 0 16px;gap:12px">
        <div style="flex:1;height:1px;background:#e2e8f0"></div>
        <span style="font-size:12px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:0.5px">Or continue with</span>
        <div style="flex:1;height:1px;background:#e2e8f0"></div>
    </div>

    <a href="{{ route('auth.google.redirect') }}" class="btn-google">
        <span>Sign in with Google</span>
    </a>
    @endif
</div>
<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const eyeShow = btn.querySelector('.eye-show');
    const eyeHide = btn.querySelector('.eye-hide');
    if (input.type === 'password') {
        input.type = 'text';
        if (eyeShow) eyeShow.style.display = 'none';
        if (eyeHide) eyeHide.style.display = 'inline-block';
    } else {
        input.type = 'password';
        if (eyeShow) eyeShow.style.display = 'inline-block';
        if (eyeHide) eyeHide.style.display = 'none';
    }
}
</script>
</body>
</html>
