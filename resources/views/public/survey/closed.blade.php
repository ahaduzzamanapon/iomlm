<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Closed — {{ $survey->title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', Roboto, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .card { background: #ffffff; border-radius: 14px; padding: 40px 30px; text-align: center; max-width: 480px; width: 100%; box-shadow: 0 10px 25px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="card">
        <div style="font-size:48px; margin-bottom:12px">🔒</div>
        <h2 style="margin:0 0 10px; color:#0f172a; font-size:22px; font-weight:800">{{ $survey->title }}</h2>
        <p style="color:#64748b; font-size:14px; line-height:1.5; margin-bottom:24px">
            This survey form is no longer accepting responses. Thank you for your interest!
        </p>
        <a href="{{ url('/') }}" class="btn btn-primary">Return to Homepage</a>
    </div>
</body>
</html>
