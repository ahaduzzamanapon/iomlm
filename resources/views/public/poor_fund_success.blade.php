<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted — Poor Fund IOM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
    @font-face {
        font-family: 'Kalpurush';
        src: local('Kalpurush'), url('/fonts/Kalpurush.ttf') format('truetype');
        font-display: swap;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Kalpurush', 'Inter', sans-serif;
        background: #f8fafc;
        color: #0f172a;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 16px;
    }
    .card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #d1fae5;
        border-top: 4px solid #047857;
        box-shadow: 0 10px 25px -5px rgba(6,78,59,.08);
        max-width: 540px;
        width: 100%;
        text-align: center;
        padding: 36px 28px;
    }
    .icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #047857, #064e3b);
        color: #fff;
        font-size: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
        box-shadow: 0 4px 12px rgba(4,120,87,.25);
    }
    h1 {
        font-size: 23px;
        font-weight: 700;
        color: #064e3b;
        margin-bottom: 8px;
        letter-spacing: -.01em;
    }
    .subtext {
        font-size: 14.5px;
        color: #475569;
        margin-bottom: 20px;
        line-height: 1.6;
    }
    .app-box {
        background: #f0fdf4;
        border: 1.5px dashed #86efac;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 18px;
    }
    .app-title {
        font-size: 12px;
        font-weight: 600;
        color: #047857;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 8px;
    }
    .app-no-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .app-no {
        font-size: 22px;
        font-weight: 700;
        color: #047857;
        font-family: 'Consolas', 'Courier New', monospace;
        letter-spacing: 1.5px;
        background: #ffffff;
        padding: 6px 14px;
        border-radius: 8px;
        border: 1px solid #bbf7d0;
        user-select: all;
    }
    .btn-copy {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ffffff;
        border: 1.5px solid #a7f3d0;
        color: #047857;
        padding: 7px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s ease;
        font-family: 'Kalpurush', sans-serif;
    }
    .btn-copy:hover {
        background: #047857;
        color: #ffffff;
        border-color: #047857;
        box-shadow: 0 2px 6px rgba(4,120,87,.2);
    }
    .btn-copy.copied {
        background: #10b981;
        color: #ffffff;
        border-color: #10b981;
    }
    .alert-box {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 12px 14px;
        border-radius: 8px;
        font-size: 13.5px;
        color: #92400e;
        text-align: left;
        margin-bottom: 22px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        line-height: 1.55;
    }
    .alert-box i {
        color: #f59e0b;
        font-size: 16px;
        margin-top: 2px;
        flex-shrink: 0;
    }
    .btn-group {
        display: flex;
        gap: 12px;
        justify-content: center;
    }
    .btn {
        padding: 10px 22px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all .15s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-primary {
        background: #047857;
        color: #fff;
    }
    .btn-primary:hover {
        background: #064e3b;
        box-shadow: 0 4px 12px rgba(4,120,87,.2);
    }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon"><i class="fa-solid fa-check"></i></div>
        <h1>পুওর ফান্ড আবেদন জমা হয়েছে!</h1>
        <p class="subtext">আপনার আবেদনটি সফলভাবে সিস্টেমে রেকর্ড করা হয়েছে। অ্যাডমিন ও কমিটি রিভিউ শেষে আপনার ইমেইল বা মোবাইলে যোগাযোগ করা হবে, ইনশাআল্লাহ।</p>

        <div class="app-box">
            <div class="app-title">Application Reference No</div>
            <div class="app-no-wrap">
                <span class="app-no" id="appRefNo">{{ $app->application_no }}</span>
                <button type="button" class="btn-copy" id="copyBtn" onclick="copyRefNumber('{{ $app->application_no }}')" title="রেফারেন্স নম্বর কপি করুন">
                    <i class="fa-regular fa-copy" id="copyIcon"></i> <span id="copyText">কপি করুন</span>
                </button>
            </div>
        </div>

        {{-- সংরক্ষণ নির্দেশনা নোটিশ বক্স --}}
        <div class="alert-box">
            <i class="fa-solid fa-circle-info"></i>
            <div>
                <strong>গুরুত্বপূর্ণ তথ্য:</strong> ভর্তি ফরম পূরণ ও পরবর্তীতে ওয়েভার সুবিধা গ্রহণ করার জন্য <strong>উপরের রেফারেন্স নম্বরটি যত্নসহকারে সংরক্ষণ করুন</strong>।
            </div>
        </div>

        <div class="btn-group">
            <a href="/apply" class="btn btn-primary">
                <span>ভর্তি ফর্মে ফিরে যান</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <script>
    function copyRefNumber(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(showCopied).catch(function() {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            showCopied();
        } catch (err) {
            console.error('Fallback copy error:', err);
        }
        textArea.remove();
    }

    function showCopied() {
        const btn = document.getElementById('copyBtn');
        const icon = document.getElementById('copyIcon');
        const txt = document.getElementById('copyText');
        if (btn && icon && txt) {
            btn.classList.add('copied');
            icon.className = 'fa-solid fa-check';
            txt.textContent = 'কপি হয়েছে!';
            setTimeout(function() {
                btn.classList.remove('copied');
                icon.className = 'fa-regular fa-copy';
                txt.textContent = 'কপি করুন';
            }, 2500);
        }
    }
    </script>
</body>
</html>
