<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted — IOM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: #f1f5f9; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; }
    .card { background: #fff; border-radius: 16px; padding: 40px 48px; max-width: 520px; width: 100%; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
    .icon { width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #059669, #10b981); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
    .icon i { font-size: 32px; color: #fff; }
    h1 { font-size: 22px; font-weight: 700; color: #111827; margin-bottom: 8px; }
    p { font-size: 14px; color: #6b7280; line-height: 1.6; }
    .app-no { display: inline-block; background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 8px; padding: 10px 24px; margin: 20px 0; font-size: 22px; font-weight: 700; color: #1d4ed8; letter-spacing: .06em; }
    .info-table { width: 100%; border-collapse: collapse; text-align: left; margin: 16px 0; font-size: 13px; }
    .info-table td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; }
    .info-table td:first-child { color: #6b7280; width: 40%; }
    .info-table td:last-child { font-weight: 500; }
    .actions { display: flex; gap: 10px; justify-content: center; margin-top: 24px; flex-wrap: wrap; }
    .btn { padding: 10px 22px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .btn-primary { background: #1a56db; color: #fff; }
    .btn-secondary { background: #f3f4f6; color: #374151; }
    .note { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 14px; font-size: 12px; color: #92400e; margin-top: 20px; text-align: left; line-height: 1.7; }
    @media print { .actions { display: none; } body { background: #fff; } .card { box-shadow: none; } }
    </style>
</head>
<body>
<div class="card" id="printArea">
    <div class="icon">
        <i class="fa-solid fa-check"></i>
    </div>

    <h1>Application Submitted!</h1>
    <p>আপনার ভর্তি আবেদন সফলভাবে জমা হয়েছে।<br>নিচের Application Number টি সংরক্ষণ করুন।</p>

    <div class="app-no">{{ $form->application_no }}</div>

    <table class="info-table">
        <tr><td>Applicant Name</td><td>{{ $form->student->name ?? '—' }}</td></tr>
        <tr><td>Phone</td><td>{{ $form->student->phone ?? '—' }}</td></tr>
        <tr><td>Course</td><td>{{ $form->interestedCourse->name ?? '—' }}</td></tr>
        <tr><td>Session</td><td>{{ $form->session->name ?? '—' }}</td></tr>
        <tr><td>Status</td><td><span style="background:#fef3c7;color:#92400e;padding:2px 10px;border-radius:4px;font-size:12px;font-weight:600">PENDING REVIEW</span></td></tr>
        <tr><td>Submitted At</td><td>{{ $form->created_at->format('d M Y, h:i A') }}</td></tr>
    </table>

    <div class="note">
        <i class="fa-solid fa-thumbtack" style="margin-right:4px"></i> আপনার Application Number <strong>{{ $form->application_no }}</strong> টি সংরক্ষণ করুন। আবেদনের অগ্রগতি সম্পর্কে আপনার সাথে যোগাযোগ করা হবে।
    </div>

    <div class="actions">
        <button class="btn btn-primary" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Application</button>
        <a href="{{ route('apply.show') }}" class="btn btn-secondary"><i class="fa-solid fa-plus"></i> New Application</a>
        <a href="/" class="btn btn-secondary"><i class="fa-solid fa-house"></i> Home</a>
    </div>
</div>
</body>
</html>
