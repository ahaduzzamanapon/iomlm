<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>বিকাশ পেমেন্ট — ইসলামিক অনলাইন মাদ্রাসা (IOM)</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @font-face {
            font-family: 'Kalpurush';
            src: url('{{ asset("fonts/kalpurush.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        :root {
            --primary: #047857;
            --primary-dark: #064e3b;
            --bkash: #e2136e;
            --bkash-dark: #c20c5d;
            --bg: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Kalpurush', 'Hind Siliguri', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #fdf2f8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            color: var(--text-main);
        }

        .checkout-card {
            background: #ffffff;
            width: 100%;
            max-width: 480px;
            border-radius: 20px;
            box-shadow: 0 20px 40px -15px rgba(226, 19, 110, 0.18), 0 0 0 1px rgba(226, 19, 110, 0.08);
            overflow: hidden;
            animation: fadeIn 0.35s ease-out;
            position: relative;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-header {
            background: linear-gradient(135deg, #e2136e 0%, #c20c5d 100%);
            color: #ffffff;
            padding: 24px 20px 20px;
            text-align: center;
            position: relative;
        }

        .logo-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .logo-iom {
            width: 44px;
            height: 44px;
            background: #ffffff;
            border-radius: 12px;
            padding: 6px;
            object-fit: contain;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .logo-bkash-badge {
            background: #ffffff;
            padding: 6px 14px;
            border-radius: 10px;
            font-weight: 700;
            color: var(--bkash);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .header-title {
            font-size: 19px;
            font-weight: 700;
            letter-spacing: -0.2px;
        }

        .header-sub {
            font-size: 13px;
            opacity: 0.92;
            margin-top: 3px;
        }

        .card-body {
            padding: 24px 20px;
        }

        .amount-box {
            background: #fff1f6;
            border: 1.5px dashed #fbcfe8;
            border-radius: 14px;
            padding: 16px;
            text-align: center;
            margin-bottom: 20px;
        }

        .amount-label {
            font-size: 13px;
            color: #9d174d;
            font-weight: 600;
        }

        .amount-val {
            font-size: 32px;
            font-weight: 700;
            color: var(--bkash);
            margin-top: 2px;
            font-family: 'Hind Siliguri', sans-serif;
        }

        .info-list {
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13.5px;
            border: 1px solid #e2e8f0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
            gap: 12px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-muted);
            font-size: 13px;
            flex-shrink: 0;
        }

        .info-value {
            font-weight: 700;
            color: var(--text-main);
            text-align: right;
            word-break: break-all;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-bkash {
            background: linear-gradient(135deg, #e2136e 0%, #c20c5d 100%);
            color: #ffffff;
            border: none;
            width: 100%;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 20px -5px rgba(226, 19, 110, 0.4);
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .btn-bkash:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 24px -5px rgba(226, 19, 110, 0.5);
        }

        .btn-bkash:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid #cbd5e1;
            width: 100%;
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: #f1f5f9;
            color: var(--text-main);
        }

        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 18px;
            font-size: 12px;
            color: #64748b;
        }

        .error-alert {
            display: none;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13.5px;
            margin-bottom: 16px;
            line-height: 1.5;
        }

        /* ── LOADING OVERLAY ── */
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(4px);
            z-index: 999999;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.25);
            border-top-color: #e2136e;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-bottom: 16px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .overlay-close-btn {
            margin-top: 20px;
            background: rgba(255,255,255,0.2);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.4);
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
        }

        .overlay-close-btn:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>

    <div class="checkout-card">
        <div class="card-header">
            <div class="logo-wrap">
                <img src="{{ asset('images/logo.png') }}" alt="IOM" class="logo-iom" onerror="this.style.display='none'">
                <div class="logo-bkash-badge">
                    <i class="fa-solid fa-wallet"></i>
                    <span>bKash</span>
                </div>
            </div>
            <div class="header-title">ইসলামিক অনলাইন মাদ্রাসা</div>
            <div class="header-sub">নিরাপদ অনলাইন পেমেন্ট গেটওয়ে</div>
        </div>

        <div class="card-body">
            <div id="errorAlertBox" class="error-alert">
                <i class="fa-solid fa-triangle-exclamation"></i> <span id="errorAlertText"></span>
            </div>

            <div class="amount-box">
                <div class="amount-label">পরিশোধযোগ্য সর্বমোট ফি</div>
                <div class="amount-val">৳ {{ number_format($transaction->amount, 2) }}</div>
            </div>

            <div class="info-list">
                <div class="info-row">
                    <span class="info-label">ট্রানজেকশন আইডি</span>
                    <span class="info-value" style="font-family:monospace;font-size:12px">{{ $transaction->tran_id }}</span>
                </div>
                @if($transaction->admissionForm)
                <div class="info-row">
                    <span class="info-label">আবেদন নম্বর</span>
                    <span class="info-value">{{ $transaction->admissionForm->application_no }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">আবেদনকারীর নাম</span>
                    <span class="info-value">{{ $transaction->customer_name ?: ($transaction->admissionForm->applicant_name ?: 'সম্মানিত শিক্ষার্থী') }}</span>
                </div>
                @elseif($transaction->invoice)
                <div class="info-row">
                    <span class="info-label">ইনভয়েস নম্বর</span>
                    <span class="info-value">{{ $transaction->invoice->invoice_no }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">শিক্ষার্থীর নাম</span>
                    <span class="info-value">{{ $transaction->student?->name ?: ($transaction->customer_name ?: 'সম্মানিত শিক্ষার্থী') }}</span>
                </div>
                @else
                <div class="info-row">
                    <span class="info-label">গ্রাহকের নাম</span>
                    <span class="info-value">{{ $transaction->customer_name ?: 'সম্মানিত শিক্ষার্থী' }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">পেমেন্ট মাধ্যম</span>
                    <span class="info-value" style="color:var(--bkash)"><i class="fa-solid fa-shield-check"></i> বিকাশ পেমেন্ট (bKash)</span>
                </div>
            </div>

            <div class="actions">
                <button type="button" id="bKash_button" class="btn-bkash">
                    <i class="fa-solid fa-lock"></i>
                    <span>বিকাশ দিয়ে পেমেন্ট করুন</span>
                </button>

                <a href="{{ route('payment.status', $transaction->tran_id) }}" class="btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> অন্য মাধ্যমে পেমেন্ট বা স্ট্যাটাস দেখুন
                </a>
            </div>

            <div class="security-badge">
                <i class="fa-solid fa-lock" style="color:#10b981"></i>
                <span>১২৮-বিট এনক্রিপশনযুক্ত নিরাপদ ও সরাসরি বিকাশ গেটওয়ে</span>
            </div>
        </div>
    </div>

    <!-- Processing Loader Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <div style="font-size:17px;font-weight:700" id="loadingText">বিকাশ গেটওয়ে সংযোগ করা হচ্ছে...</div>
        <div style="font-size:13px;opacity:0.85;margin-top:6px">অনুগ্রহ করে অপেক্ষা করুন, পেজ রিফ্রেশ করবেন না।</div>
        <button type="button" class="overlay-close-btn" onclick="hideLoader()">বাতিল করুন / বন্ধ করুন</button>
    </div>

    <!-- JQuery and bKash Checkout SDK -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ $scriptUrl }}"></script>

    <script>
        function showLoader(text) {
            if (text) $('#loadingText').text(text);
            $('#loadingOverlay').css('display', 'flex');
        }

        function hideLoader() {
            $('#loadingOverlay').hide();
        }

        function showError(msg) {
            hideLoader();
            $('#errorAlertText').text(msg);
            $('#errorAlertBox').slideDown();
        }

        $(document).ready(function() {
            var initialPaymentData = @json($paymentData ?? []);
            var tranId = @json($transaction->tran_id);
            var executeUrl = @json(route('payment.bkash.execute.ajax', $transaction->tran_id));
            var createUrl = @json(route('payment.bkash.create.ajax', $transaction->tran_id));
            var statusUrl = @json(route('payment.status', $transaction->tran_id));
            var amountStr = "{{ number_format($transaction->amount, 2, '.', '') }}";
            var activePaymentID = (initialPaymentData && initialPaymentData.paymentID) ? initialPaymentData.paymentID : null;

            // Check if bKash SDK loaded
            if (typeof bKash === 'undefined') {
                showError('বিকাশ পেমেন্ট গেটওয়ে স্ক্রিপ্ট লোড করা যায়নি। অনুগ্রহ করে ইন্টারনেট সংযোগ চেক করে পেজ রিফ্রেশ করুন অথবা অন্য মাধ্যমে পেমেন্ট করুন।');
                return;
            }

            try {
                bKash.init({
                    paymentMode: 'checkout',
                    paymentRequest: {
                        amount: amountStr,
                        intent: 'sale'
                    },
                    createRequest: function(request) {
                        $('#errorAlertBox').hide();
                        showLoader('বিকাশ পেমেন্ট উইন্ডো খোলা হচ্ছে...');

                        // If valid fresh paymentID exists from initial load
                        if (initialPaymentData && initialPaymentData.paymentID) {
                            activePaymentID = initialPaymentData.paymentID;
                            var currentData = initialPaymentData;
                            initialPaymentData = null; // Clear so subsequent attempts generate fresh
                            hideLoader();
                            bKash.create().onSuccess(currentData);
                            return;
                        }

                        // Otherwise request a fresh one via AJAX
                        $.ajax({
                            url: createUrl,
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                hideLoader();
                                if (res.success && res.data && res.data.paymentID) {
                                    activePaymentID = res.data.paymentID;
                                    bKash.create().onSuccess(res.data);
                                } else {
                                    bKash.create().onError();
                                    showError(res.message || 'বিকাশ পেমেন্ট শুরু করা যায়নি। অনুগ্রহ করে পুনরায় চেষ্টা করুন।');
                                }
                            },
                            error: function(xhr) {
                                hideLoader();
                                bKash.create().onError();
                                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'বিকাশ সার্ভার সংযোগে ত্রুটি হয়েছে।';
                                showError(msg);
                            }
                        });
                    },
                    executeRequestOnAuthorization: function() {
                        showLoader('আপনার বিকাশ পেমেন্ট যাচাই করা হচ্ছে...');

                        $.ajax({
                            url: executeUrl,
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                paymentID: activePaymentID || (typeof bKash !== 'undefined' ? bKash.paymentID : '')
                            },
                            success: function(res) {
                                if (res.success && res.redirect_url) {
                                    window.location.href = res.redirect_url;
                                } else {
                                    if (typeof bKash !== 'undefined' && bKash.execute) bKash.execute().onError();
                                    hideLoader();
                                    showError(res.message || 'বিকাশ পেমেন্ট সফল হয়নি।');
                                    if (res.redirect_url) {
                                        setTimeout(function() { window.location.href = res.redirect_url; }, 1500);
                                    }
                                }
                            },
                            error: function(xhr) {
                                if (typeof bKash !== 'undefined' && bKash.execute) bKash.execute().onError();
                                hideLoader();
                                var res = xhr.responseJSON || {};
                                showError(res.message || 'পেমেন্ট সম্পন্ন হতে ত্রুটি হয়েছে।');
                                setTimeout(function() {
                                    if (res.redirect_url) {
                                        window.location.href = res.redirect_url;
                                    } else {
                                        window.location.href = statusUrl;
                                    }
                                }, 2000);
                            }
                        });
                    },
                    onClose: function() {
                        hideLoader();
                    }
                });
            } catch (err) {
                console.error('bKash initialization error:', err);
                showError('বিকাশ গেটওয়ে শুরু হতে সমস্যা হয়েছে: ' + (err.message || err));
            }

            // Automatically attempt to open bKash modal after page render
            setTimeout(function() {
                var btn = document.getElementById('bKash_button');
                if (btn && typeof bKash !== 'undefined') {
                    btn.click();
                }
            }, 600);
        });
    </script>
</body>
</html>
