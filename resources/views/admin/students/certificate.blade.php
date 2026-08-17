<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Completion Certificate — {{ $student->name }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@600;800&family=Great+Vibes&family=Montserrat:wght@400;600;700&display=swap');
        
        body { font-family: 'Montserrat', sans-serif; background: #f8fafc; margin: 0; padding: 20px; color: #0f172a; }
        .cert-outer { max-width: 900px; margin: 0 auto; background: #fff; border: 12px solid #1e293b; padding: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border-radius: 8px; }
        .cert-inner { border: 3px double #d97706; padding: 40px; text-align: center; background: radial-gradient(circle, #ffffff 0%, #fffbeb 100%); position: relative; }

        .cert-badge { font-size: 54px; margin-bottom: 10px; }
        .institute-name { font-family: 'Cinzel', serif; font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: 2px; text-transform: uppercase; margin: 0; }
        .cert-title { font-family: 'Great Vibes', cursive; font-size: 48px; color: #d97706; margin: 10px 0 20px; }

        .cert-body { font-size: 15px; color: #334155; line-height: 1.8; max-width: 700px; margin: 0 auto 30px; }
        .student-name { font-family: 'Cinzel', serif; font-size: 28px; font-weight: 800; color: #4f46e5; border-bottom: 2px solid #4f46e5; display: inline-block; padding: 0 20px 4px; margin: 10px 0; }
        .course-name { font-size: 18px; font-weight: 700; color: #0f172a; }

        .signatures { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 60px; padding: 0 40px; }
        .sign-box { text-align: center; }
        .sign-line { border-top: 2px solid #0f172a; width: 180px; font-size: 12px; font-weight: 700; color: #0f172a; padding-top: 6px; }

        @media print {
            body { background: #fff; padding: 0; }
            .cert-outer { border: 10px solid #1e293b; box-shadow: none; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <div style="text-align:center;margin-bottom:20px" class="btn-print">
        <button onclick="window.print()" style="background:#d97706;color:#fff;border:none;padding:12px 28px;border-radius:8px;font-weight:700;font-size:15px;cursor:pointer">
            🎓 Print Official Certificate of Completion
        </button>
    </div>

    <div class="cert-outer">
        <div class="cert-inner">
            <div class="cert-badge">🏅</div>
            <h1 class="institute-name">IOM Institute</h1>
            <div class="cert-title">Certificate of Completion</div>

            <div class="cert-body">
                This is to proudly certify that
                <br>
                <div class="student-name">{{ strtoupper($student->name) }}</div>
                <br>
                Student Code: <strong>{{ $student->student_code ?? 'N/A' }}</strong>
                <br><br>
                has successfully completed all required academic modules, examinations, and coursework for the program of
                <br>
                <span class="course-name">{{ $student->enrollments->first()?->course?->name ?? 'Diploma Course' }}</span>
                <br>
                with outstanding dedication and academic performance.
            </div>

            <div class="signatures">
                <div class="sign-box">
                    <div style="font-family:'Great Vibes', cursive;font-size:24px;color:#334155;margin-bottom:2px">Academic Dean</div>
                    <div class="sign-line">Academic Coordinator</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#64748b;margin-bottom:6px">Date of Issuance</div>
                    <strong style="font-size:13px">{{ date('d F Y') }}</strong>
                </div>
                <div class="sign-box">
                    <div style="font-family:'Great Vibes', cursive;font-size:24px;color:#334155;margin-bottom:2px">Principal Director</div>
                    <div class="sign-line">Principal / Institute Head</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
