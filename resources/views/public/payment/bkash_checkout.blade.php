<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Processing bKash Payment...</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden !important;
            background: #ffffff;
            font-family: 'Kalpurush', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
        }

        /* ── Simple, Plain & Centered Processing View (সাদামাটা) ── */
        .processing-wrap {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 10;
            width: 90%;
            max-width: 400px;
        }

        .simple-spinner {
            width: 38px;
            height: 38px;
            border: 3.5px solid #f1f5f9;
            border-top: 3.5px solid #e2136e;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 16px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .title-text {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 8px;
            letter-spacing: -0.2px;
        }

        .sub-text {
            font-size: 14px;
            color: #64748b;
            margin: 0 0 16px;
            line-height: 1.5;
        }

        .cancel-link {
            font-size: 13.5px;
            color: #94a3b8;
            text-decoration: underline;
            cursor: pointer;
            transition: color 0.2s;
            background: none;
            border: none;
            padding: 4px;
            font-family: inherit;
        }

        .cancel-link:hover {
            color: #ef4444;
        }

        .manual-btn {
            display: none;
            margin-top: 14px;
            background: #e2136e;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }

        .manual-btn:hover {
            background: #c20c5d;
        }

        .error-box {
            display: none;
            color: #b91c1c;
            font-size: 13.5px;
            margin-top: 14px;
            line-height: 1.5;
        }

        .error-box button {
            margin-top: 8px;
            background: #b91c1c;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 6px 14px;
            font-size: 12.5px;
            cursor: pointer;
        }

        /* ── Complete scrollbar elimination for bKash iframe & wrapper ── */
        #bKashFrameWrapper,
        #bKash_modal,
        .bKash_modal,
        iframe {
            overflow: hidden !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }

        #bKashFrameWrapper {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 99999 !important;
        }

        #bKashFrameWrapper::-webkit-scrollbar,
        iframe::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        #bKashFrameWrapper iframe {
            width: 100% !important;
            height: 100% !important;
            min-height: 100vh !important;
            border: none !important;
        }
    </style>
</head>
<body>

    {{-- Hidden button for bKash SDK binding must be directly under body so bKash modal attaches to body, NOT inside processing-wrap --}}
    <button type="button" id="bKash_button" style="display:none !important;"></button>

    <div class="processing-wrap" id="processingBox">
        <div class="simple-spinner"></div>
        <div class="title-text" id="statusHeading">Processing...</div>
        <div class="sub-text" id="statusSubtext">বিকাশ পেমেন্ট গেটওয়ে লোড হচ্ছে, অনুগ্রহ করে অপেক্ষা করুন...</div>

        {{-- Fallback button if popup blocked --}}
        <button type="button" id="manualBtn" class="manual-btn" onclick="window.location.reload()">
            পুনরায় লোড করুন
        </button>

        {{-- Adblock / slow network notice --}}
        <div id="adblockNotice" style="display:none;margin-top:14px;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:13px;color:#991b1b;line-height:1.5;">
            <i class="fa-solid fa-triangle-exclamation"></i> বিকাশ পেমেন্ট উইন্ডো লোড হতে দেরি হচ্ছে। আপনার ব্রাউজারে <strong>AdBlocker</strong> বা কোনো Extension চালু থাকলে তা বন্ধ করুন অথবা <strong>Incognito (প্রাইভেট) উইন্ডোতে</strong> চেষ্টা করুন।
        </div>

        <div style="margin-top: 12px;">
            <a href="{{ $cancelUrl }}" class="cancel-link" onclick="onUserCancel(event)">পেমেন্ট বাতিল করুন</a>
        </div>

        <div id="errorBox" class="error-box">
            <div id="errorText"></div>
            <div>
                <button type="button" onclick="window.location.reload()">পুনরায় চেষ্টা করুন</button>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ $scriptUrl }}"></script>

    <script>
        var initialPaymentData = @json($paymentData ?? []);
        var tranId = @json($transaction->tran_id);
        var executeUrl = @json(route('payment.bkash.execute.ajax', $transaction->tran_id));
        var createUrl = @json(route('payment.bkash.create.ajax', $transaction->tran_id));
        var cancelUrl = @json($cancelUrl);
        var cancelEndpoint = @json(route('payment.bkash.cancel', $transaction->tran_id));
        var amountStr = "{{ number_format($transaction->amount, 2, '.', '') }}";
        var activePaymentID = (initialPaymentData && initialPaymentData.paymentID) ? initialPaymentData.paymentID : null;
        var bKashOpened = false;

        function setStatus(title, sub) {
            if (title) $('#statusHeading').text(title);
            if (sub) $('#statusSubtext').text(sub);
        }

        function showError(msg) {
            $('#errorText').text(msg || 'বিকাশ পেমেন্ট লোড করা যায়নি।');
            $('#errorBox').show();
            setStatus('Error', 'পেমেন্ট গেটওয়েতে সমস্যা হয়েছে।');
        }

        function onUserCancel(e) {
            if (e) e.preventDefault();
            setStatus('Processing...', 'পেমেন্ট বাতিল হচ্ছে...');
            $.ajax({
                url: cancelEndpoint,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                complete: function() {
                    window.location.href = cancelUrl;
                }
            });
        }

        function triggerBkashOpen() {
            var btn = document.getElementById('bKash_button');
            if (btn && typeof bKash !== 'undefined') {
                btn.click();
            }
        }

        $(document).ready(function() {
            if (typeof bKash === 'undefined') {
                showError('বিকাশ পেমেন্ট স্ক্রিপ্ট লোড হতে পারেনি। অনুগ্রহ করে পেজ রিফ্রেশ করুন।');
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
                        setStatus('Processing...', 'বিকাশ পেমেন্ট উইন্ডো তৈরি হচ্ছে...');

                        if (initialPaymentData && initialPaymentData.paymentID) {
                            activePaymentID = initialPaymentData.paymentID;
                            var currentData = initialPaymentData;
                            initialPaymentData = null;
                            bKashOpened = true;
                            bKash.create().onSuccess(currentData);
                            return;
                        }

                        $.ajax({
                            url: createUrl,
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                if (res.success && res.data && res.data.paymentID) {
                                    activePaymentID = res.data.paymentID;
                                    bKashOpened = true;
                                    bKash.create().onSuccess(res.data);
                                } else {
                                    bKash.create().onError();
                                    showError(res.message || 'বিকাশ পেমেন্ট সেশন শুরু করা যায়নি।');
                                }
                            },
                            error: function(xhr) {
                                bKash.create().onError();
                                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'বিকাশ সার্ভার সংযোগে সমস্যা হয়েছে।';
                                showError(msg);
                            }
                        });
                    },
                    executeRequestOnAuthorization: function() {
                        setStatus('Processing...', 'আপনার বিকাশ পেমেন্ট সম্পন্ন ও যাচাই করা হচ্ছে...');

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
                                    setStatus('Success!', 'আলহামদুলিল্লাহ! পেমেন্ট সফল হয়েছে। পেজে নিয়ে যাওয়া হচ্ছে...');
                                    setTimeout(function() {
                                        window.location.href = res.redirect_url;
                                    }, 400);
                                } else {
                                    if (typeof bKash !== 'undefined' && bKash.execute) bKash.execute().onError();
                                    showError(res.message || 'বিকাশ পেমেন্ট সফল হয়নি।');
                                    if (res.redirect_url) {
                                        setTimeout(function() { window.location.href = res.redirect_url; }, 2000);
                                    }
                                }
                            },
                            error: function(xhr) {
                                if (typeof bKash !== 'undefined' && bKash.execute) bKash.execute().onError();
                                var res = xhr.responseJSON || {};
                                showError(res.message || 'পেমেন্ট সম্পন্ন হতে ত্রুটি হয়েছে।');
                                setTimeout(function() {
                                    if (res.redirect_url) {
                                        window.location.href = res.redirect_url;
                                    } else {
                                        window.location.href = cancelUrl;
                                    }
                                }, 2500);
                            }
                        });
                    },
                    onClose: function() {
                        setStatus('Processing...', 'পেমেন্ট বাতিল হচ্ছে...');
                        $.ajax({
                            url: cancelEndpoint,
                            type: 'POST',
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            complete: function() {
                                window.location.href = cancelUrl;
                            }
                        });
                    }
                });

                // Auto-trigger bKash modal
                setTimeout(function() {
                    triggerBkashOpen();
                }, 300);

                // If modal not open after 4 seconds (e.g. adblocker blocking or slow CDN), display retry & hint
                setTimeout(function() {
                    var wrapper = document.getElementById('bKashFrameWrapper');
                    var iframe = wrapper ? wrapper.querySelector('iframe') : null;
                    if (!iframe || iframe.style.visibility === 'hidden' || !wrapper) {
                        $('#adblockNotice').fadeIn();
                        $('#manualBtn').show();
                    }
                }, 4000);

            } catch (err) {
                console.error('bKash init error:', err);
                showError('বিকাশ গেটওয়ে লোড হতে ত্রুটি: ' + (err.message || err));
            }
        });
    </script>
</body>
</html>
