<x-admin-layout>
    <x-slot name="title">পেমেন্ট গেটওয়ে সেটিংস (Payment Gateway Settings)</x-slot>

    <style>
    .pgw-container {
        font-family: 'Kalpurush', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        max-width: 1040px;
    }
    .nav-tabs-custom {
        display: flex;
        gap: 12px;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 24px;
    }
    .nav-tab-item {
        padding: 14px 24px;
        font-weight: 700;
        font-size: 15px;
        color: #64748b;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all .2s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        border-radius: 6px 6px 0 0;
    }
    .nav-tab-item:hover {
        color: #1e293b;
        background: #f8fafc;
    }
    .nav-tab-item.active {
        color: #0f766e;
        border-bottom-color: #0f766e;
        background: #f0fdf4;
    }
    .tab-content-panel {
        display: none;
    }
    .tab-content-panel.active {
        display: block;
        animation: fadeIn .25s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .badge-mode {
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 999px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .badge-mode-sandbox {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .badge-mode-live {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .mode-toggle-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 22px;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 22px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: border-color .2s;
    }
    .section-card.is-active-env {
        border-color: #0f766e;
        box-shadow: 0 0 0 1px #0f766e, 0 4px 6px -1px rgba(0,0,0,0.06);
    }
    .section-header {
        padding: 16px 22px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .section-header.sandbox-header {
        background: #fffbeb;
        border-bottom-color: #fef3c7;
    }
    .section-header.live-header {
        background: #f0fdf4;
        border-bottom-color: #dcfce7;
    }
    .section-body {
        padding: 22px;
    }
    .password-input-group {
        position: relative;
    }
    .password-toggle-btn {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        padding: 4px 8px;
    }
    .password-toggle-btn:hover {
        color: #1e293b;
    }
    .callback-box {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-family: monospace;
        font-size: 13px;
        color: #334155;
    }
    .btn-copy {
        background: #0f766e;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 12px;
        cursor: pointer;
        font-family: 'Kalpurush', sans-serif;
    }
    .btn-copy:hover {
        background: #115e59;
    }
    .switch-wrapper {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 26px;
    }
    .switch-wrapper input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .switch-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 34px;
    }
    .switch-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    input:checked + .switch-slider {
        background-color: #0f766e;
    }
    input:checked + .switch-slider:before {
        transform: translateX(22px);
    }
    </style>

    <div class="pgw-container">
        {{-- Page Header --}}
        <div class="page-header" style="margin-bottom:24px">
            <div class="page-header-left">
                <h1 style="font-size:24px;color:#0f172a;display:flex;align-items:center;gap:10px">
                    <i class="fa-solid fa-credit-card text-teal-600"></i>
                    পেমেন্ট গেটওয়ে সেটিংস (Payment Gateway Settings)
                </h1>
                <p style="color:#64748b;font-size:14px;margin-top:4px">
                    SSLCommerz এবং bKash গেটওয়ের স্যান্ডবক্স (টেস্ট) ও লাইভ ক্রেডেনশিয়াল ও URL পরিচালনা করুন।
                </p>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-weight:600;display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-weight:600;display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-triangle-exclamation"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- Gateway Tabs --}}
        @php
            $activeTab = request('tab', 'sslcommerz');
        @endphp
        <div class="nav-tabs-custom">
            <div class="nav-tab-item {{ $activeTab === 'sslcommerz' ? 'active' : '' }}" onclick="switchGatewayTab('sslcommerz', this)">
                <i class="fa-solid fa-building-columns text-blue-600"></i>
                <span>SSLCommerz (এসএসএলকমার্স)</span>
                @if(($settings['sslcommerz_enabled'] ?? '0') === '1')
                    <span style="font-size:11px;background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:12px">সক্রিয়</span>
                @else
                    <span style="font-size:11px;background:#f1f5f9;color:#64748b;padding:2px 8px;border-radius:12px">নিষ্ক্রিয়</span>
                @endif
            </div>
            <div class="nav-tab-item {{ $activeTab === 'bkash' ? 'active' : '' }}" onclick="switchGatewayTab('bkash', this)">
                <i class="fa-solid fa-mobile-screen-button" style="color:#d82a6b"></i>
                <span>bKash (বিকাশ ডিরেক্ট পিজিডব্লিউ)</span>
                @if(($settings['bkash_enabled'] ?? '0') === '1')
                    <span style="font-size:11px;background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:12px">সক্রিয়</span>
                @else
                    <span style="font-size:11px;background:#f1f5f9;color:#64748b;padding:2px 8px;border-radius:12px">নিষ্ক্রিয়</span>
                @endif
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- TAB 1: SSLCOMMERZ SETTINGS                                --}}
        {{-- ========================================================= --}}
        <div id="tab-sslcommerz" class="tab-content-panel {{ $activeTab === 'sslcommerz' ? 'active' : '' }}">
            <form method="POST" action="{{ route('admin.settings.payment-gateways.sslcommerz') }}">
                @csrf

                {{-- Status & Sandbox Toggle Card --}}
                <div class="mode-toggle-card">
                    <div>
                        <h3 style="margin:0 0 4px 0;font-size:16px;font-weight:700;color:#0f172a">
                            SSLCommerz পেমেন্ট গেটওয়ে স্ট্যাটাস
                        </h3>
                        <p style="margin:0;font-size:13px;color:#64748b">
                            সিস্টেমে কার্ড, নেট ব্যাংকিং ও মোবাইল ওয়ালেটের মাধ্যমে পেমেন্ট গ্রহণের জন্য এটি অন রাখুন।
                        </p>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px">
                        <span style="font-weight:600;font-size:14px;color:#334155">গেটওয়ে অন/অফ:</span>
                        <label class="switch-wrapper">
                            <input type="checkbox" name="sslcommerz_enabled" value="1" {{ ($settings['sslcommerz_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>

                {{-- Sandbox Mode Controller Card --}}
                <div class="mode-toggle-card" style="background:#f8fafc;border-left:5px solid #0284c7">
                    <div>
                        <div style="display:flex;align-items:center;gap:10px">
                            <h4 style="margin:0;font-size:15px;font-weight:700;color:#0f172a">
                                স্যান্ডবক্স (Sandbox) মোড নির্বাচন
                            </h4>
                            <span id="sslcommerz-mode-badge" class="badge-mode {{ ($settings['sslcommerz_mode'] ?? 'sandbox') === 'sandbox' ? 'badge-mode-sandbox' : 'badge-mode-live' }}">
                                @if(($settings['sslcommerz_mode'] ?? 'sandbox') === 'sandbox')
                                    <i class="fa-solid fa-flask"></i> স্যান্ডবক্স মোড একটিভ
                                @else
                                    <i class="fa-solid fa-circle-check"></i> লাইভ মোড একটিভ
                                @endif
                            </span>
                        </div>
                        <p style="margin:4px 0 0 0;font-size:13px;color:#64748b">
                            স্যান্ডবক্স অন থাকলে পরীক্ষামূলক ক্রেডেনশিয়াল ও টেস্ট কার্ড ব্যবহার হবে। লাইভ অন থাকলে আসল টাকা প্রসেস হবে।
                        </p>
                    </div>

                    <div style="display:flex;align-items:center;gap:16px">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:600;font-size:14px;color:#92400e">
                            <input type="radio" name="sslcommerz_mode" value="sandbox"
                                   {{ ($settings['sslcommerz_mode'] ?? 'sandbox') === 'sandbox' ? 'checked' : '' }}
                                   onchange="toggleEnvHighlight('sslcommerz', 'sandbox')">
                            স্যান্ডবক্স (Sandbox / Test)
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:600;font-size:14px;color:#166534">
                            <input type="radio" name="sslcommerz_mode" value="live"
                                   {{ ($settings['sslcommerz_mode'] ?? 'sandbox') === 'live' ? 'checked' : '' }}
                                   onchange="toggleEnvHighlight('sslcommerz', 'live')">
                            লাইভ (Live Production)
                        </label>
                    </div>
                </div>

                {{-- General Currency Setting --}}
                <div style="margin-bottom:18px;display:flex;gap:16px;align-items:center">
                    <label style="font-weight:600;font-size:14px;color:#334155">ডিফল্ট কারেন্সি:</label>
                    <select name="sslcommerz_currency" class="form-control" style="width:140px">
                        <option value="BDT" {{ ($settings['sslcommerz_currency'] ?? 'BDT') === 'BDT' ? 'selected' : '' }}>BDT (৳)</option>
                        <option value="USD" {{ ($settings['sslcommerz_currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD ($)</option>
                    </select>
                </div>

                {{-- 1.1 SSLCommerz SANDBOX Credentials Section --}}
                <div id="sslcommerz-sandbox-box" class="section-card {{ ($settings['sslcommerz_mode'] ?? 'sandbox') === 'sandbox' ? 'is-active-env' : '' }}">
                    <div class="section-header sandbox-header">
                        <div style="display:flex;align-items:center;gap:10px">
                            <i class="fa-solid fa-flask" style="color:#d97706"></i>
                            <strong style="color:#92400e;font-size:15px">স্যান্ডবক্স সেটিংস (Sandbox / Test Environment)</strong>
                        </div>
                        <span class="badge-mode badge-mode-sandbox">টেস্ট ক্রেডেনশিয়াল</span>
                    </div>
                    <div class="section-body">
                        <div class="form-row" style="display:flex;gap:16px;margin-bottom:16px">
                            <div class="form-group" style="flex:1">
                                <label style="font-weight:600;font-size:13px;color:#475569">
                                    Sandbox Store ID <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="sslcommerz_sandbox_store_id" class="form-control"
                                       placeholder="e.g. testbox or your_sandbox_store_id"
                                       value="{{ $settings['sslcommerz_sandbox_store_id'] ?? '' }}">
                                <small style="color:#64748b">SSLCommerz স্যান্ডবক্স স্টোর আইডি</small>
                            </div>
                            <div class="form-group" style="flex:1">
                                <label style="font-weight:600;font-size:13px;color:#475569">
                                    Sandbox Store Password <span class="text-danger">*</span>
                                </label>
                                <div class="password-input-group">
                                    <input type="password" id="ssl_sb_pwd" name="sslcommerz_sandbox_store_passwd" class="form-control"
                                           placeholder="{{ !empty($settings['sslcommerz_sandbox_store_passwd']) ? '••••••••••••' : 'Enter Sandbox Store Password' }}"
                                           value="{{ $settings['sslcommerz_sandbox_store_passwd'] ?? '' }}">
                                    <button type="button" class="password-toggle-btn" onclick="togglePasswordView('ssl_sb_pwd')">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <small style="color:#64748b">নতুন পাসওয়ার্ড না দিতে চাইলে খালি রাখুন</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="font-weight:600;font-size:13px;color:#475569">
                                Sandbox Base URL
                            </label>
                            <input type="url" name="sslcommerz_sandbox_url" class="form-control"
                                   placeholder="https://sandbox.sslcommerz.com"
                                   value="{{ $settings['sslcommerz_sandbox_url'] ?? 'https://sandbox.sslcommerz.com' }}">
                            <small style="color:#64748b">ডিফল্ট: https://sandbox.sslcommerz.com</small>
                        </div>
                    </div>
                </div>

                {{-- 1.2 SSLCommerz LIVE Credentials Section --}}
                <div id="sslcommerz-live-box" class="section-card {{ ($settings['sslcommerz_mode'] ?? 'sandbox') === 'live' ? 'is-active-env' : '' }}">
                    <div class="section-header live-header">
                        <div style="display:flex;align-items:center;gap:10px">
                            <i class="fa-solid fa-shield-halved" style="color:#16a34a"></i>
                            <strong style="color:#166534;font-size:15px">লাইভ সেটিংস (Live / Production Environment)</strong>
                        </div>
                        <span class="badge-mode badge-mode-live">বাস্তব লেনদেন (Production)</span>
                    </div>
                    <div class="section-body">
                        <div class="form-row" style="display:flex;gap:16px;margin-bottom:16px">
                            <div class="form-group" style="flex:1">
                                <label style="font-weight:600;font-size:13px;color:#475569">
                                    Live Store ID <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="sslcommerz_live_store_id" class="form-control"
                                       placeholder="e.g. iomlive001"
                                       value="{{ $settings['sslcommerz_live_store_id'] ?? '' }}">
                                <small style="color:#64748b">SSLCommerz এর প্রদত্ত লাইভ মার্চেন্ট স্টোর আইডি</small>
                            </div>
                            <div class="form-group" style="flex:1">
                                <label style="font-weight:600;font-size:13px;color:#475569">
                                    Live Store Password <span class="text-danger">*</span>
                                </label>
                                <div class="password-input-group">
                                    <input type="password" id="ssl_live_pwd" name="sslcommerz_live_store_passwd" class="form-control"
                                           placeholder="{{ !empty($settings['sslcommerz_live_store_passwd']) ? '••••••••••••' : 'Enter Live Store Password' }}"
                                           value="{{ $settings['sslcommerz_live_store_passwd'] ?? '' }}">
                                    <button type="button" class="password-toggle-btn" onclick="togglePasswordView('ssl_live_pwd')">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <small style="color:#64748b">নতুন পাসওয়ার্ড না দিতে চাইলে খালি রাখুন</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="font-weight:600;font-size:13px;color:#475569">
                                Live Base URL
                            </label>
                            <input type="url" name="sslcommerz_live_url" class="form-control"
                                   placeholder="https://securepay.sslcommerz.com"
                                   value="{{ $settings['sslcommerz_live_url'] ?? 'https://securepay.sslcommerz.com' }}">
                            <small style="color:#64748b">ডিফল্ট: https://securepay.sslcommerz.com</small>
                        </div>
                    </div>
                </div>

                {{-- SSLCommerz IPN & Callback URLs Reference --}}
                <div class="section-card" style="background:#fcfcfc">
                    <div class="section-header">
                        <strong style="color:#334155;font-size:14px">
                            <i class="fa-solid fa-link"></i> SSLCommerz IPN / Webhook Callback URL
                        </strong>
                    </div>
                    <div class="section-body" style="padding:16px 22px">
                        <p style="font-size:13px;color:#64748b;margin-bottom:8px">
                            SSLCommerz Merchant প্যানেলে ইনস্ট্যান্ট পেমেন্ট নোটিফিকেশনের (IPN) জন্য নিচের URL টি ব্যবহার করুন:
                        </p>
                        <div class="callback-box">
                            <span id="ssl-ipn-url">{{ url('/api/payment/sslcommerz/ipn') }}</span>
                            <button type="button" class="btn-copy" onclick="copyToClipboard('ssl-ipn-url')">কপি করুন</button>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div style="margin-top:24px;display:flex;justify-content:flex-end">
                    <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-size:15px;font-weight:700;background:#0f766e;border-color:#0f766e">
                        <i class="fa-solid fa-floppy-disk"></i> SSLCommerz সেটিংস সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>

        {{-- ========================================================= --}}
        {{-- TAB 2: bKash SETTINGS                              --}}
        {{-- ========================================================= --}}
        <div id="tab-bkash" class="tab-content-panel {{ $activeTab === 'bkash' ? 'active' : '' }}">
            <form method="POST" action="{{ route('admin.settings.payment-gateways.bkash') }}">
                @csrf

                {{-- Status & Mode Toggle Card --}}
                <div class="mode-toggle-card">
                    <div>
                        <h3 style="margin:0 0 4px 0;font-size:16px;font-weight:700;color:#0f172a">
                            bKash (বিকাশ ডিরেক্ট পিজিডব্লিউ) স্ট্যাটাস
                        </h3>
                        <p style="margin:0;font-size:13px;color:#64748b">
                            সরাসরি বিকাশ টোকেনাইজড পেমেন্ট গেটওয়ের মাধ্যমে পেমেন্ট গ্রহণের জন্য এটি অন রাখুন।
                        </p>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px">
                        <span style="font-weight:600;font-size:14px;color:#334155">বিকাশ অন/অফ:</span>
                        <label class="switch-wrapper">
                            <input type="checkbox" name="bkash_enabled" value="1" {{ ($settings['bkash_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>

                {{-- Sandbox Mode Controller Card --}}
                <div class="mode-toggle-card" style="background:#f8fafc;border-left:5px solid #d82a6b">
                    <div>
                        <div style="display:flex;align-items:center;gap:10px">
                            <h4 style="margin:0;font-size:15px;font-weight:700;color:#0f172a">
                                বিকাশ স্যান্ডবক্স (Sandbox) মোড নির্বাচন
                            </h4>
                            <span id="bkash-mode-badge" class="badge-mode {{ ($settings['bkash_mode'] ?? 'sandbox') === 'sandbox' ? 'badge-mode-sandbox' : 'badge-mode-live' }}">
                                @if(($settings['bkash_mode'] ?? 'sandbox') === 'sandbox')
                                    <i class="fa-solid fa-flask"></i> স্যান্ডবক্স মোড একটিভ
                                @else
                                    <i class="fa-solid fa-circle-check"></i> লাইভ মোড একটিভ
                                @endif
                            </span>
                        </div>
                        <p style="margin:4px 0 0 0;font-size:13px;color:#64748b">
                            স্যান্ডবক্স অন থাকলে বিকাশের সিমুলেটর ও টেস্ট ওটিপি/পিন ব্যবহার হবে। লাইভ অন থাকলে কাস্টমারের বাস্তব একাউন্ট চার্জ হবে।
                        </p>
                    </div>

                    <div style="display:flex;align-items:center;gap:16px">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:600;font-size:14px;color:#92400e">
                            <input type="radio" name="bkash_mode" value="sandbox"
                                   {{ ($settings['bkash_mode'] ?? 'sandbox') === 'sandbox' ? 'checked' : '' }}
                                   onchange="toggleEnvHighlight('bkash', 'sandbox')">
                            স্যান্ডবক্স (Sandbox / Test)
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:600;font-size:14px;color:#166534">
                            <input type="radio" name="bkash_mode" value="live"
                                   {{ ($settings['bkash_mode'] ?? 'sandbox') === 'live' ? 'checked' : '' }}
                                   onchange="toggleEnvHighlight('bkash', 'live')">
                            লাইভ (Live Production)
                        </label>
                    </div>
                </div>

                {{-- Currency Setting --}}
                <div style="margin-bottom:18px;display:flex;gap:16px;align-items:center">
                    <label style="font-weight:600;font-size:14px;color:#334155">ডিফল্ট কারেন্সি:</label>
                    <select name="bkash_currency" class="form-control" style="width:140px">
                        <option value="BDT" {{ ($settings['bkash_currency'] ?? 'BDT') === 'BDT' ? 'selected' : '' }}>BDT (৳)</option>
                    </select>
                </div>

                {{-- 2.1 bKash SANDBOX Credentials Section --}}
                <div id="bkash-sandbox-box" class="section-card {{ ($settings['bkash_mode'] ?? 'sandbox') === 'sandbox' ? 'is-active-env' : '' }}">
                    <div class="section-header sandbox-header">
                        <div style="display:flex;align-items:center;gap:10px">
                            <i class="fa-solid fa-flask" style="color:#d97706"></i>
                            <strong style="color:#92400e;font-size:15px">বিকাশ স্যান্ডবক্স সেটিংস (Sandbox / Test Environment)</strong>
                        </div>
                        <span class="badge-mode badge-mode-sandbox">টেস্ট ক্রেডেনশিয়াল</span>
                    </div>
                    <div class="section-body">
                        <div class="form-row" style="display:flex;gap:16px;margin-bottom:16px">
                            <div class="form-group" style="flex:1">
                                <label style="font-weight:600;font-size:13px;color:#475569">
                                    Sandbox App Key <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="bkash_sandbox_app_key" class="form-control"
                                       placeholder="e.g. 5bkash_sandbox_app_key..."
                                       value="{{ $settings['bkash_sandbox_app_key'] ?? '' }}">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label style="font-weight:600;font-size:13px;color:#475569">
                                    Sandbox App Secret <span class="text-danger">*</span>
                                </label>
                                <div class="password-input-group">
                                    <input type="password" id="bkash_sb_secret" name="bkash_sandbox_app_secret" class="form-control"
                                           placeholder="{{ !empty($settings['bkash_sandbox_app_secret']) ? '••••••••••••' : 'Enter Sandbox App Secret' }}"
                                           value="{{ $settings['bkash_sandbox_app_secret'] ?? '' }}">
                                    <button type="button" class="password-toggle-btn" onclick="togglePasswordView('bkash_sb_secret')">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <small style="color:#64748b">নতুন সিক্রেট না দিতে চাইলে খালি রাখুন</small>
                            </div>
                        </div>

                        <div class="form-row" style="display:flex;gap:16px;margin-bottom:16px">
                            <div class="form-group" style="flex:1">
                                <label style="font-weight:600;font-size:13px;color:#475569">
                                    Sandbox Username <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="bkash_sandbox_username" class="form-control"
                                       placeholder="e.g. sandboxTokenizedUser01"
                                       value="{{ $settings['bkash_sandbox_username'] ?? '' }}">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label style="font-weight:600;font-size:13px;color:#475569">
                                    Sandbox Password <span class="text-danger">*</span>
                                </label>
                                <div class="password-input-group">
                                    <input type="password" id="bkash_sb_pwd" name="bkash_sandbox_password" class="form-control"
                                           placeholder="{{ !empty($settings['bkash_sandbox_password']) ? '••••••••••••' : 'Enter Sandbox Password' }}"
                                           value="{{ $settings['bkash_sandbox_password'] ?? '' }}">
                                    <button type="button" class="password-toggle-btn" onclick="togglePasswordView('bkash_sb_pwd')">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <small style="color:#64748b">নতুন পাসওয়ার্ড না দিতে চাইলে খালি রাখুন</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="font-weight:600;font-size:13px;color:#475569">
                                Sandbox Base URL
                            </label>
                            <input type="url" name="bkash_sandbox_base_url" class="form-control"
                                   placeholder="https://tokenized.sandbox.bka.sh/v1.2.0-beta"
                                   value="{{ $settings['bkash_sandbox_base_url'] ?? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta' }}">
                            <small style="color:#64748b">ডিফল্ট: https://tokenized.sandbox.bka.sh/v1.2.0-beta</small>
                        </div>
                    </div>
                </div>

                {{-- 2.2 bKash LIVE Credentials Section --}}
                <div id="bkash-live-box" class="section-card {{ ($settings['bkash_mode'] ?? 'sandbox') === 'live' ? 'is-active-env' : '' }}">
                    <div class="section-header live-header">
                        <div style="display:flex;align-items:center;gap:10px">
                            <i class="fa-solid fa-shield-halved" style="color:#16a34a"></i>
                            <strong style="color:#166534;font-size:15px">বিকাশ লাইভ সেটিংস (Live / Production Environment)</strong>
                        </div>
                        <span class="badge-mode badge-mode-live">বাস্তব লেনদেন (Production)</span>
                    </div>
                    <div class="section-body">
                        <div class="form-row" style="display:flex;gap:16px;margin-bottom:16px">
                            <div class="form-group" style="flex:1">
                                <label style="font-weight:600;font-size:13px;color:#475569">
                                    Live App Key <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="bkash_live_app_key" class="form-control"
                                       placeholder="e.g. bKash_Live_App_Key..."
                                       value="{{ $settings['bkash_live_app_key'] ?? '' }}">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label style="font-weight:600;font-size:13px;color:#475569">
                                    Live App Secret <span class="text-danger">*</span>
                                </label>
                                <div class="password-input-group">
                                    <input type="password" id="bkash_live_secret" name="bkash_live_app_secret" class="form-control"
                                           placeholder="{{ !empty($settings['bkash_live_app_secret']) ? '••••••••••••' : 'Enter Live App Secret' }}"
                                           value="{{ $settings['bkash_live_app_secret'] ?? '' }}">
                                    <button type="button" class="password-toggle-btn" onclick="togglePasswordView('bkash_live_secret')">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <small style="color:#64748b">নতুন সিক্রেট না দিতে চাইলে খালি রাখুন</small>
                            </div>
                        </div>

                        <div class="form-row" style="display:flex;gap:16px;margin-bottom:16px">
                            <div class="form-group" style="flex:1">
                                <label style="font-weight:600;font-size:13px;color:#475569">
                                    Live Username <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="bkash_live_username" class="form-control"
                                       placeholder="e.g. merchantUserName"
                                       value="{{ $settings['bkash_live_username'] ?? '' }}">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label style="font-weight:600;font-size:13px;color:#475569">
                                    Live Password <span class="text-danger">*</span>
                                </label>
                                <div class="password-input-group">
                                    <input type="password" id="bkash_live_pwd" name="bkash_live_password" class="form-control"
                                           placeholder="{{ !empty($settings['bkash_live_password']) ? '••••••••••••' : 'Enter Live Password' }}"
                                           value="{{ $settings['bkash_live_password'] ?? '' }}">
                                    <button type="button" class="password-toggle-btn" onclick="togglePasswordView('bkash_live_pwd')">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <small style="color:#64748b">নতুন পাসওয়ার্ড না দিতে চাইলে খালি রাখুন</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="font-weight:600;font-size:13px;color:#475569">
                                Live Base URL
                            </label>
                            <input type="url" name="bkash_live_base_url" class="form-control"
                                   placeholder="https://tokenized.pay.bka.sh/v1.2.0-beta"
                                   value="{{ $settings['bkash_live_base_url'] ?? 'https://tokenized.pay.bka.sh/v1.2.0-beta' }}">
                            <small style="color:#64748b">ডিফল্ট: https://tokenized.pay.bka.sh/v1.2.0-beta</small>
                        </div>
                    </div>
                </div>

                {{-- bKash Callback URL Reference --}}
                <div class="section-card" style="background:#fcfcfc">
                    <div class="section-header">
                        <strong style="color:#334155;font-size:14px">
                            <i class="fa-solid fa-link"></i> bKash Callback URL
                        </strong>
                    </div>
                    <div class="section-body" style="padding:16px 22px">
                        <p style="font-size:13px;color:#64748b;margin-bottom:8px">
                            বিকাশ মার্চেন্ট পোর্টাল বা এগ্রিমেন্টে কলব্যাক URL হিসেবে নিচের লিংকটি প্রদান করুন:
                        </p>
                        <div class="callback-box">
                            <span id="bkash-callback-url">{{ url('/api/payment/bkash/callback') }}</span>
                            <button type="button" class="btn-copy" onclick="copyToClipboard('bkash-callback-url')">কপি করুন</button>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div style="margin-top:24px;display:flex;justify-content:flex-end">
                    <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-size:15px;font-weight:700;background:#d82a6b;border-color:#d82a6b">
                        <i class="fa-solid fa-floppy-disk"></i> বিকাশ (bKash) সেটিংস সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function switchGatewayTab(tabKey, element) {
        document.querySelectorAll('.nav-tab-item').forEach(function(el) {
            el.classList.remove('active');
        });
        document.querySelectorAll('.tab-content-panel').forEach(function(el) {
            el.classList.remove('active');
        });

        element.classList.add('active');
        const targetPanel = document.getElementById('tab-' + tabKey);
        if (targetPanel) {
            targetPanel.classList.add('active');
        }

        // Update URL query string without reloading
        const url = new URL(window.location);
        url.searchParams.set('tab', tabKey);
        window.history.replaceState({}, '', url);
    }

    function toggleEnvHighlight(gateway, mode) {
        const sandboxBox = document.getElementById(gateway + '-sandbox-box');
        const liveBox = document.getElementById(gateway + '-live-box');
        const badge = document.getElementById(gateway + '-mode-badge');

        if (mode === 'sandbox') {
            if (sandboxBox) sandboxBox.classList.add('is-active-env');
            if (liveBox) liveBox.classList.remove('is-active-env');
            if (badge) {
                badge.className = 'badge-mode badge-mode-sandbox';
                badge.innerHTML = '<i class="fa-solid fa-flask"></i> স্যান্ডবক্স মোড একটিভ';
            }
        } else {
            if (sandboxBox) sandboxBox.classList.remove('is-active-env');
            if (liveBox) liveBox.classList.add('is-active-env');
            if (badge) {
                badge.className = 'badge-mode badge-mode-live';
                badge.innerHTML = '<i class="fa-solid fa-circle-check"></i> লাইভ মোড একটিভ';
            }
        }
    }

    function togglePasswordView(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const btn = input.nextElementSibling;
        const icon = btn ? btn.querySelector('i') : null;

        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }

    function copyToClipboard(elementId) {
        const text = document.getElementById(elementId).innerText.trim();
        navigator.clipboard.writeText(text).then(() => {
            alert('ক্লিপবোর্ডে কপি করা হয়েছে: ' + text);
        }).catch(() => {
            prompt('নিচের লিংকটি কপি করুন:', text);
        });
    }
    </script>
</x-admin-layout>
