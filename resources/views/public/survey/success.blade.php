<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Response Submitted — {{ $survey->title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', Roboto, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .card { background: #ffffff; border-radius: 14px; padding: 40px 30px; text-align: center; max-width: 480px; width: 100%; box-shadow: 0 10px 25px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; border-top: 6px solid #10b981; }
    </style>
</head>
<body>
    <div class="card">
        <div style="font-size:48px; margin-bottom:12px; color:#10b981">✓</div>
        <h2 style="margin:0 0 10px; color:#0f172a; font-size:22px; font-weight:800">Response Submitted!</h2>
        <p style="color:#475569; font-size:14px; line-height:1.5; margin-bottom:24px">
            {{ session('success_message', 'Thank you! Your response to "' . $survey->title . '" has been recorded successfully.') }}
        </p>

        @if($survey->allow_multiple_responses)
            <div style="margin-bottom:16px">
                <a href="{{ route('public.survey.show', $survey->slug) }}" style="font-size:13px; color:#2563eb; text-decoration:none; font-weight:600">
                    Submit another response →
                </a>
            </div>
        @endif

        <a href="{{ url('/') }}" class="btn btn-outline" style="font-size:13px">Return to Homepage</a>
    </div>
</body>
</html>
