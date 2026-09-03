<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted — Poor Fund IOM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Noto+Sans+Bengali:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', 'Noto Sans Bengali', sans-serif; background: #f8fafc; color: #0f172a; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(0,0,0,.05); max-width: 540px; width: 100%; text-align: center; padding: 40px 32px; }
    .icon { width: 64px; height: 64px; border-radius: 50%; background: #dcfce7; color: #166534; font-size: 28px; display: flex; align-items: center; justify-content: justify; margin: 0 auto 20px; align-items: center; justify-content: center; }
    h1 { font-size: 22px; font-weight: 700; color: #1e3a5f; margin-bottom: 8px; }
    p { font-size: 14px; color: #64748b; margin-bottom: 20px; line-height: 1.6; }
    .app-box { background: #f1f5f9; border: 1.5px dashed #cbd5e1; border-radius: 10px; padding: 14px; margin-bottom: 24px; }
    .app-no { font-size: 20px; font-weight: 700; color: #1a56db; font-family: monospace; letter-spacing: 1px; }
    .btn-group { display: flex; gap: 12px; justify-content: center; }
    .btn { padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all .15s; }
    .btn-primary { background: #1a56db; color: #fff; }
    .btn-primary:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon"><i class="fa-solid fa-check"></i></div>
        <h1>পুওর ফান্ড আবেদন জমা হয়েছে!</h1>
        <p>আপনার আবেদনটি সফলভাবে সিস্টেমে রেকর্ড করা হয়েছে। অ্যাডমিন ও কমিটি রিভিউ শেষে আপনার ইমেইল বা মোবাইলে যোগাযোগ করা হবে, ইনশাআল্লাহ।</p>

        <div class="app-box">
            <div style="font-size:12px;color:#64748b;margin-bottom:4px">Application Reference No</div>
            <div class="app-no">{{ $app->application_no }}</div>
        </div>

        <div class="btn-group">
            <a href="/apply" class="btn btn-primary">ভর্তি ফর্মে ফিরে যান →</a>
        </div>
    </div>
</body>
</html>
