<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Learning Plus ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            min-height: 100vh;
            background: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-bg {
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(59,130,246,.15) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(139,92,246,.12) 0%, transparent 50%),
                radial-gradient(ellipse at 60% 80%, rgba(16,185,129,.08) 0%, transparent 50%),
                #0f172a;
            z-index: 0;
        }

        .login-card {
            position: relative; z-index: 1;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 20px;
            padding: 36px;
            width: 100%; max-width: 460px;
            backdrop-filter: blur(20px);
            box-shadow: 0 24px 80px rgba(0,0,0,.4);
        }

        .login-logo {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 20px;
        }
        .login-logo-icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 800; color: #fff;
        }
        .login-logo-text { line-height: 1.2; }
        .login-logo-name { font-size: 18px; font-weight: 700; color: #f1f5f9; }
        .login-logo-sub  { font-size: 12px; color: #64748b; }

        .login-title { font-size: 20px; font-weight: 700; color: #f1f5f9; margin-bottom: 4px; }
        .login-subtitle { font-size: 13px; color: #64748b; margin-bottom: 20px; }

        .quick-login-box {
            background: rgba(255,255,255,.03);
            border: 1px dashed rgba(255,255,255,.12);
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 20px;
        }
        .quick-login-title {
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
            color: #94a3b8; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;
        }

        .quick-section-label {
            font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 8px 0 4px 0;
        }

        .quick-btn-grid {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px;
        }
        .quick-btn {
            padding: 7px 10px;
            border-radius: 6px;
            font-size: 11px; font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer; text-align: left;
            transition: all .2s;
            font-family: 'Inter', sans-serif;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .quick-btn.admin {
            background: rgba(59,130,246,.15); color: #60a5fa; border-color: rgba(59,130,246,.3);
            grid-column: 1 / -1; text-align: center;
        }
        .quick-btn.admin:hover { background: rgba(59,130,246,.3); transform: translateY(-1px); }

        .quick-btn.teacher {
            background: rgba(16,185,129,.15); color: #34d399; border-color: rgba(16,185,129,.3);
        }
        .quick-btn.teacher:hover { background: rgba(16,185,129,.3); transform: translateY(-1px); }

        .quick-btn.student {
            background: rgba(139,92,246,.15); color: #a78bfa; border-color: rgba(139,92,246,.3);
        }
        .quick-btn.student:hover { background: rgba(139,92,246,.3); transform: translateY(-1px); }

        .lf-group { margin-bottom: 14px; }
        .lf-label {
            display: block; font-size: 12px; font-weight: 600;
            color: #94a3b8; margin-bottom: 6px;
        }
        .lf-input {
            width: 100%; padding: 10px 14px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 8px;
            color: #f1f5f9; font-size: 14px;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: all .2s;
        }
        .lf-input:focus {
            border-color: #3b82f6;
            background: rgba(59,130,246,.08);
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }
        .lf-input::placeholder { color: #475569; }

        .lf-check {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: #64748b; cursor: pointer; margin-top: 4px;
        }
        .lf-check input { accent-color: #3b82f6; width: 14px; height: 14px; }

        .btn-login {
            width: 100%; padding: 11px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: #fff; font-size: 14px; font-weight: 600;
            border: none; border-radius: 8px; cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: all .2s;
            margin-top: 10px;
        }
        .btn-login:hover { opacity: .9; transform: translateY(-1px); box-shadow: 0 8px 20px rgba(59,130,246,.3); }

        .error-msg {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.2);
            color: #fca5a5;
            border-radius: 8px; padding: 10px 14px;
            font-size: 13px; margin-bottom: 16px;
        }
    </style>
</head>
<body>
<div class="login-bg"></div>

<div class="login-card">
    <!-- Logo -->
    <div class="login-logo">
        <div class="login-logo-icon">LP</div>
        <div class="login-logo-text">
            <div class="login-logo-name">Learning Plus</div>
            <div class="login-logo-sub">University Management System</div>
        </div>
    </div>

    <div class="login-title">Welcome back</div>
    <div class="login-subtitle">Select a demo role or sign in below</div>

    <!-- Quick Demo Login Buttons (Multiple Teachers & Multiple Students) -->
    <div class="quick-login-box">
        <div class="quick-login-title">
            <span>⚡ 1-Click Demo Accounts</span>
            <span style="font-size:10px;color:#64748b">Password: password</span>
        </div>

        <button type="button" class="quick-btn admin" onclick="quickLogin('admin@learningplus.com', 'password')">👑 System Administrator</button>

        <div class="quick-section-label">👨‍🏫 Teachers (Multiple Accounts)</div>
        <div class="quick-btn-grid">
            <button type="button" class="quick-btn teacher" onclick="quickLogin('teacher@learningplus.com', 'password')">Dr. Alan Turing</button>
            <button type="button" class="quick-btn teacher" onclick="quickLogin('ada@learningplus.com', 'password')">Prof. Ada Lovelace</button>
            <button type="button" class="quick-btn teacher" onclick="quickLogin('shannon@learningplus.com', 'password')">Prof. C. Shannon</button>
        </div>

        <div class="quick-section-label">🎓 Students (Multiple Accounts)</div>
        <div class="quick-btn-grid">
            <button type="button" class="quick-btn student" onclick="quickLogin('student@learningplus.com', 'password')">John Doe (Active)</button>
            <button type="button" class="quick-btn student" onclick="quickLogin('sarah@learningplus.com', 'password')">Sarah Ahmed (Active)</button>
            <button type="button" class="quick-btn student" onclick="quickLogin('tanvir@gmail.com', 'password')">Tanvir H. (Pending)</button>
        </div>
    </div>

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
            <label class="lf-label" for="email">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                class="lf-input"
                value="{{ old('email') }}"
                placeholder="admin@learningplus.com"
                autocomplete="email"
                required
            >
        </div>

        <div class="lf-group">
            <label class="lf-label" for="password">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                class="lf-input"
                placeholder="••••••••"
                autocomplete="current-password"
                required
            >
        </div>

        <label class="lf-check">
            <input type="checkbox" name="remember" checked> Remember me
        </label>

        <button type="submit" class="btn-login">Sign In →</button>
    </form>
</div>

<script>
function quickLogin(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
    document.getElementById('loginForm').submit();
}
</script>

</body>
</html>
