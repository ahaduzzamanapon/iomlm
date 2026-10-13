<x-admin-layout>
    <x-slot name="title">Notification Settings — Firebase & SMTP</x-slot>

    <style>
    .nav-tabs-custom { display:flex; gap:8px; border-bottom:2px solid #e2e8f0; margin-bottom:24px; }
    .nav-tab-item { padding:12px 20px; font-weight:700; font-size:14px; color:#64748b; cursor:pointer; border-bottom:3px solid transparent; margin-bottom:-2px; transition:all .2s; }
    .nav-tab-item:hover { color:#1e293b; }
    .nav-tab-item.active { color:#047857; border-bottom-color:#047857; }
    .tab-content-panel { display:none; }
    .tab-content-panel.active { display:block; }
    </style>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Notification & Communication Settings</h1>
            <p>Manage Firebase Push Notification credentials and SMTP Mail Server configuration</p>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-weight:600">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabs Navigation --}}
    <div class="nav-tabs-custom">
        <div class="nav-tab-item active" onclick="switchTab('firebaseTab', this)">
            <i class="fa-solid fa-fire text-orange"></i> Firebase Notification Tab
        </div>
        <div class="nav-tab-item" onclick="switchTab('smtpTab', this)">
            <i class="fa-solid fa-envelope text-blue"></i> SMTP Mail Configuration Tab
        </div>
        <div class="nav-tab-item" onclick="switchTab('smsTab', this)">
            <i class="fa-solid fa-comment-sms text-green"></i> SMS Gateway (MiM SMS) Tab
        </div>
    </div>

    {{-- TAB 1: FIREBASE NOTIFICATION --}}
    <div id="firebaseTab" class="tab-content-panel active">
        <form method="POST" action="{{ route('admin.settings.notifications.firebase') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:20px;max-width:960px">
                <div class="card">
                    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                        <span class="card-title">Firebase Cloud Messaging (FCM) Credentials</span>
                        <label class="form-check" style="margin:0;font-weight:700;color:#2563eb">
                            <input type="checkbox" name="firebase_enabled" value="1"
                                {{ ($settings['firebase_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            Enable Firebase Push Notifications
                        </label>
                    </div>
                    <div class="card-body">
                        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px;margin-bottom:20px;font-size:13px;color:#1e40af">
                            <strong><i class="fa-solid fa-circle-info"></i> How to get Firebase credentials:</strong> Go to
                            <a href="https://console.firebase.google.com" target="_blank" style="color:#2563eb;font-weight:700">console.firebase.google.com</a>
                            → Create Project → Add Web App → Copy SDK Configuration & Cloud Messaging Server Key.
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Firebase Project ID <span class="required">*</span></label>
                                <input type="text" name="firebase_project_id" class="form-control"
                                    placeholder="e.g. iom-learning-plus"
                                    value="{{ $settings['firebase_project_id'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Firebase Web API Key <span class="required">*</span></label>
                                <input type="text" name="firebase_api_key" class="form-control"
                                    placeholder="e.g. AIzaSyB..."
                                    value="{{ $settings['firebase_api_key'] ?? '' }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Auth Domain</label>
                                <input type="text" name="firebase_auth_domain" class="form-control"
                                    placeholder="e.g. iom-learning-plus.firebaseapp.com"
                                    value="{{ $settings['firebase_auth_domain'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Storage Bucket</label>
                                <input type="text" name="firebase_storage_bucket" class="form-control"
                                    placeholder="e.g. iom-learning-plus.appspot.com"
                                    value="{{ $settings['firebase_storage_bucket'] ?? '' }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Messaging Sender ID</label>
                                <input type="text" name="firebase_messaging_sender_id" class="form-control"
                                    placeholder="e.g. 102938475612"
                                    value="{{ $settings['firebase_messaging_sender_id'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>App ID</label>
                                <input type="text" name="firebase_app_id" class="form-control"
                                    placeholder="e.g. 1:102938475612:web:a1b2c3..."
                                    value="{{ $settings['firebase_app_id'] ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>FCM Server Key (Legacy HTTP API Key)</label>
                            <textarea name="firebase_server_key" class="form-control" rows="2"
                                placeholder="AAAA... (Found in Firebase Console → Project Settings → Cloud Messaging → 3 dots menu → Enable in Google Cloud Console)">{{ $settings['firebase_server_key'] ?? '' }}</textarea>
                            <span class="form-help">Used for Legacy Push Notification API. Click the 3-dots menu on Cloud Messaging page to enable in Google Cloud Console.</span>
                        </div>

                        <div class="form-group">
                            <label>Firebase Service Account Private Key (JSON) — Recommended for FCM HTTP v1</label>
                            <textarea name="firebase_service_account_json" class="form-control" rows="4"
                                placeholder='{"type": "service_account", "project_id": "iomm-316e7", "private_key_id": "...", "private_key": "-----BEGIN PRIVATE KEY-----\n...", "client_email": "..."}'>{{ $settings['firebase_service_account_json'] ?? '' }}</textarea>
                            <span class="form-help">Found in Firebase Console → Project Settings → <strong>Service accounts</strong> → Generate new private key.</span>
                        </div>

                        <div class="form-group">
                            <label>VAPID Public Key (Web Push Certificate)</label>
                            <input type="text" name="firebase_vapid_key" class="form-control"
                                placeholder="e.g. BEcvBekci2MxeLCqFRnCiBj51vSzyJ89AEJ6UgIqc-6fs1Qzb-wnkvPViL61z8PdDSySZWZTAILJnbWlt-IA2m8"
                                value="{{ $settings['firebase_vapid_key'] ?? 'BEcvBekci2MxeLCqFRnCiBj51vSzyJ89AEJ6UgIqc-6fs1Qzb-wnkvPViL61z8PdDSySZWZTAILJnbWlt-IA2m8' }}">
                        </div>
                    </div>
                    <div class="card-footer" style="text-align:right">
                        <button type="submit" class="btn btn-primary">Save Firebase Credentials</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- TAB 2: SMTP MAIL CONFIGURATION --}}
    <div id="smtpTab" class="tab-content-panel">
        <form method="POST" action="{{ route('admin.settings.notifications.smtp') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:20px;max-width:960px">
                <div class="card">
                    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                        <span class="card-title">SMTP Mail Server Configuration</span>
                        <label class="form-check" style="margin:0;font-weight:700;color:#2563eb">
                            <input type="checkbox" name="smtp_enabled" value="1"
                                {{ ($settings['smtp_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            Enable SMTP Email Sending
                        </label>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Mail Driver</label>
                                <select name="smtp_driver" class="form-control">
                                    <option value="smtp" {{ ($settings['smtp_driver'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                    <option value="sendmail" {{ ($settings['smtp_driver'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>SMTP Host <span class="required">*</span></label>
                                <input type="text" name="smtp_host" class="form-control"
                                    placeholder="e.g. smtp.gmail.com or smtp.mailtrap.io"
                                    value="{{ $settings['smtp_host'] ?? '' }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>SMTP Port <span class="required">*</span></label>
                                <input type="text" name="smtp_port" class="form-control"
                                    placeholder="587 / 465 / 2525"
                                    value="{{ $settings['smtp_port'] ?? '587' }}">
                            </div>
                            <div class="form-group">
                                <label>Encryption</label>
                                <select name="smtp_encryption" class="form-control">
                                    <option value="tls" {{ ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                                    <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                    <option value="none" {{ ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>SMTP Username</label>
                                <input type="text" name="smtp_username" class="form-control"
                                    placeholder="e.g. your_email@domain.com"
                                    value="{{ $settings['smtp_username'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>SMTP Password</label>
                                <input type="password" name="smtp_password" class="form-control"
                                    placeholder="Leave blank to keep existing password"
                                    autocomplete="new-password">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>From Sender Email <span class="required">*</span></label>
                                <input type="email" name="smtp_from_address" class="form-control"
                                    placeholder="e.g. noreply@iom.edu.bd"
                                    value="{{ $settings['smtp_from_address'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>From Sender Name <span class="required">*</span></label>
                                <input type="text" name="smtp_from_name" class="form-control"
                                    placeholder="e.g. IOM Education"
                                    value="{{ $settings['smtp_from_name'] ?? 'IOM Education' }}">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center">
                        <button type="button" class="btn btn-outline" onclick="openTestMailModal()">
                            Send Test Email
                        </button>
                        <button type="submit" class="btn btn-primary">Save SMTP Settings</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- TAB 3: SMS GATEWAY (MiM SMS) --}}
    <div id="smsTab" class="tab-content-panel">
        <form method="POST" action="{{ route('admin.settings.notifications.sms') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:20px;max-width:960px">
                <div class="card">
                    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                        <span class="card-title">MiM SMS Gateway Configuration (sms.mimsms.com)</span>
                        <label class="form-check" style="margin:0;font-weight:700;color:#059669">
                            <input type="checkbox" name="sms_enabled" value="1"
                                {{ ($settings['sms_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            Enable SMS Gateway
                        </label>
                    </div>
                    <div class="card-body">
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px;margin-bottom:20px;font-size:13px;color:#166534">
                           
                            <span style="color:#b91c1c;font-weight:700"><i class="fa-solid fa-triangle-exclamation"></i> গুরুত্বপূর্ণ:</span> লাইভ API ব্যবহারের জন্য আপনার সার্ভারের IP address / Domain (<code style="background:#fee2e2;padding:2px 6px;border-radius:4px">iom.edu.bd</code> অথবা সার্ভার পাবলিক আইপি) MiM SMS প্যানেলের <strong>Developer Option &rarr; Whitelist</strong>-এ যুক্ত থাকতে হবে।
                        </div>

                        <input type="hidden" name="sms_provider" value="mimsms">

                        <div class="form-row">
                            <div class="form-group">
                                <label>API URL <span class="required">*</span></label>
                                <input type="text" name="sms_api_url" class="form-control"
                                    placeholder="https://api.mimsms.com/api/V2/SMS"
                                    value="{{ $settings['sms_api_url'] ?? 'https://api.mimsms.com/api/V2/SMS' }}" required>
                                <small style="color:#64748b">ডিফল্ট: https://api.mimsms.com/api/V2/SMS</small>
                            </div>
                            <div class="form-group">
                                <label>API UserName <span class="required">*</span></label>
                                <input type="text" name="sms_username" class="form-control"
                                    placeholder="kmarsus@gmail.com"
                                    value="{{ $settings['sms_username'] ?? 'kmarsus@gmail.com' }}" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>API Key <span class="required">*</span></label>
                                <input type="text" name="sms_api_key" class="form-control"
                                    placeholder="RZW30XSQHWPQVIH"
                                    value="{{ $settings['sms_api_key'] ?? 'RZW30XSQHWPQVIH' }}" required>
                            </div>
                            <div class="form-group">
                                <label>Sender ID / Sender Name (ঐচ্ছিক / Optional)</label>
                                <input type="text" name="sms_sender_name" class="form-control"
                                    placeholder="মাস্কিং বা নন-মাস্কিং Sender ID (না থাকলে খালি রাখুন)"
                                    value="{{ $settings['sms_sender_name'] ?? '' }}">
                                <small style="color:#64748b;font-family:'Kalpurush',sans-serif;">
                                    📌 <strong>নোট:</strong> Sender ID ছাড়া নন-মাস্কিং এসএমএস পাঠাতে চাইলে এটি খালি রাখতে পারেন। তবে আপনার MiM SMS প্যানেলে (<strong>sms.mimsms.com &rarr; Utility &rarr; Sender ID</strong>) কোনো নির্ধারিত নন-মাস্কিং নম্বর বরাদ্দ থাকলে সেটি এখানে লিখুন।
                                </small>
                            </div>
                        </div>

                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-top:8px;font-size:13px;color:#475569">
                            <strong><i class="fa-solid fa-bell"></i> স্বয়ংক্রিয় এসএমএস পাঠানোর সুযোগ:</strong>
                            ভর্তি কনফার্মেশন (অনলাইন পেমেন্ট অথবা অ্যাডমিন অ্যাপ্রুভাল)-এর সময় শিক্ষার্থীর ফোন নম্বরে স্বয়ংক্রিয়ভাবে Student ID ও Password সহ এসএমএস এবং ইমেইল দুটোই পৌঁছে যাবে।
                        </div>
                    </div>
                    <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center">
                        <button type="button" class="btn btn-outline" onclick="openTestSmsModal()">
                            <i class="fa-solid fa-paper-plane"></i> Send Test SMS
                        </button>
                        <button type="submit" class="btn btn-primary">Save SMS Settings</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- TEST MAIL MODAL --}}
    <div class="modal-overlay" id="testMailModal">
        <div class="modal" style="max-width:480px">
            <div class="modal-header">
                <span class="modal-title"><i class="fa-solid fa-envelope"></i> Send Test Email</span>
                <button type="button" class="modal-close" onclick="closeModal('testMailModal')">&times;</button>
            </div>
            <form id="testMailForm" onsubmit="handleSendTestMail(event)">
                @csrf
                <div class="modal-body">
                    <p style="font-size:13px;color:#64748b;margin-bottom:16px">
                        Enter an email address to verify your SMTP server connection and mail settings instantly.
                    </p>
                    <div class="form-group">
                        <label>Recipient Email Address <span class="required">*</span></label>
                        <input type="email" id="test_recipient_email" class="form-control"
                            placeholder="e.g. yourname@gmail.com" required
                            value="{{ auth()->user()->email }}">
                    </div>
                    <div id="testMailAlert" style="display:none;padding:12px;border-radius:8px;font-size:13px;font-weight:600;margin-top:12px"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('testMailModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="testMailSubmitBtn">Send Test Mail</button>
                </div>
            </form>
        </div>
    </div>

    {{-- TEST SMS MODAL --}}
    <div class="modal-overlay" id="testSmsModal">
        <div class="modal" style="max-width:480px">
            <div class="modal-header">
                <span class="modal-title"><i class="fa-solid fa-comment-sms text-green"></i> Send Test SMS</span>
                <button type="button" class="modal-close" onclick="closeModal('testSmsModal')">&times;</button>
            </div>
            <form id="testSmsForm" onsubmit="handleSendTestSms(event)">
                @csrf
                <div class="modal-body">
                    <p style="font-size:13px;color:#64748b;margin-bottom:16px">
                        Enter a Bangladeshi phone number (e.g. <code>01712345678</code> or <code>8801712345678</code>) to test your MiM SMS Gateway configuration.
                    </p>
                    <div class="form-group">
                        <label>Recipient Phone Number <span class="required">*</span></label>
                        <input type="text" id="test_recipient_phone" class="form-control"
                            placeholder="e.g. 017XXXXXXXX" required>
                    </div>
                    <div id="testSmsAlert" style="display:none;padding:12px;border-radius:8px;font-size:13px;font-weight:600;margin-top:12px"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('testSmsModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="testSmsSubmitBtn">Send Test SMS</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.add('active', 'open');
            el.style.display = 'flex';
        }
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.remove('active', 'open');
            el.style.display = 'none';
        }
    }

    function switchTab(tabId, el) {
        document.querySelectorAll('.tab-content-panel').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.nav-tab-item').forEach(t => t.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        el.classList.add('active');
    }

    function openTestMailModal() {
        document.getElementById('testMailAlert').style.display = 'none';
        openModal('testMailModal');
    }

    function openTestSmsModal() {
        document.getElementById('testSmsAlert').style.display = 'none';
        openModal('testSmsModal');
    }

    async function handleSendTestMail(e) {
        e.preventDefault();
        const email = document.getElementById('test_recipient_email').value;
        const btn = document.getElementById('testMailSubmitBtn');
        const alert = document.getElementById('testMailAlert');

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
        alert.style.display = 'none';

        try {
            const res = await fetch('{{ route("admin.settings.notifications.test-mail", [], false) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ test_email: email })
            });

            const data = await res.json();
            alert.style.display = 'block';
            if (data.success) {
                alert.style.background = '#f0fdf4';
                alert.style.color = '#15803d';
                alert.style.border = '1px solid #bbf7d0';
                alert.textContent = data.message;
            } else {
                alert.style.background = '#fef2f2';
                alert.style.color = '#b91c1c';
                alert.style.border = '1px solid #fecaca';
                alert.textContent = data.message;
            }
        } catch (err) {
            alert.style.display = 'block';
            alert.style.background = '#fef2f2';
            alert.style.color = '#b91c1c';
            alert.style.border = '1px solid #fecaca';
            alert.textContent = 'Error connecting to server: ' + err.message;
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Test Mail';
        }
    }

    async function handleSendTestSms(e) {
        e.preventDefault();
        const phone = document.getElementById('test_recipient_phone').value;
        const btn = document.getElementById('testSmsSubmitBtn');
        const alert = document.getElementById('testSmsAlert');

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
        alert.style.display = 'none';

        try {
            const res = await fetch('{{ route("admin.settings.notifications.test-sms", [], false) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ test_phone: phone })
            });

            const data = await res.json();
            alert.style.display = 'block';
            if (data.success) {
                alert.style.background = '#f0fdf4';
                alert.style.color = '#15803d';
                alert.style.border = '1px solid #bbf7d0';
                alert.textContent = data.message;
            } else {
                alert.style.background = '#fef2f2';
                alert.style.color = '#b91c1c';
                alert.style.border = '1px solid #fecaca';
                alert.textContent = data.message || 'Failed to send SMS.';
            }
        } catch (err) {
            alert.style.display = 'block';
            alert.style.background = '#fef2f2';
            alert.style.color = '#b91c1c';
            alert.style.border = '1px solid #fecaca';
            alert.textContent = 'Error connecting to server: ' + err.message;
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Test SMS';
        }
    }
    </script>
</x-admin-layout>
