<x-admin-layout>
    <x-slot name="title">Google Auth Setup</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Google Auth Setup</h1>
            <p>Configure Google OAuth 2.0 single sign-in for users</p>
        </div>
    </div>

    <div class="card" style="max-width: 800px">
        <div class="card-header">
            <div style="display:flex;align-items:center;gap:10px">
                <i class="fa-brands fa-google" style="font-size:20px;color:#ea4335"></i>
                <span class="card-title">Google OAuth 2.0 Credentials</span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.google-auth.update') }}" class="card-body">
            @csrf

            <!-- Toggle Switch -->
            <div class="form-group" style="background:#f8fafc;padding:16px 20px;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:24px">
                <label class="form-check" style="justify-content:space-between;width:100%;margin:0;font-weight:700;font-size:15px;color:#0f172a">
                    <span>Enable Google Sign-In Button on Login Page</span>
                    <input type="checkbox" name="google_auth_enabled" value="1" {{ ($settings['google_auth_enabled'] ?? '0') == '1' ? 'checked' : '' }} style="width:20px;height:20px;accent-color:#2563eb">
                </label>
                <div style="font-size:12px;color:var(--text-muted);margin-top:4px">When enabled, users can click "Sign in with Google" on the login screen to authenticate if their email exists in the system.</div>
            </div>

            <!-- Client ID -->
            <div class="form-group">
                <label for="google_client_id">Google Client ID <span class="required">*</span></label>
                <input
                    type="text"
                    id="google_client_id"
                    name="google_client_id"
                    class="form-control"
                    value="{{ old('google_client_id', $settings['google_client_id'] ?? '') }}"
                    placeholder="e.g. 123456789-xxxxxx.apps.googleusercontent.com"
                >
            </div>

            <!-- Client Secret -->
            <div class="form-group">
                <label for="google_client_secret">Google Client Secret <span class="required">*</span></label>
                <div style="position:relative;display:flex;align-items:center">
                    <input
                        type="password"
                        id="google_client_secret"
                        name="google_client_secret"
                        class="form-control"
                        style="padding-right:44px"
                        value="{{ old('google_client_secret', $settings['google_client_secret'] ?? '') }}"
                        placeholder="GOCSPX-xxxxxxxxxxxxxxxx"
                    >
                    <button type="button" onclick="togglePasswordVisibility('google_client_secret', this)" style="position:absolute;right:8px;background:transparent;border:none;padding:6px;cursor:pointer;color:#64748b">
                        <i class="fa-solid fa-eye eye-show"></i>
                        <i class="fa-solid fa-eye-slash eye-hide" style="display:none"></i>
                    </button>
                </div>
            </div>

            <!-- Redirect URI -->
            <div class="form-group">
                <label for="google_redirect_uri">Authorized Redirect URI <span class="required">*</span></label>
                <div style="display:flex;gap:8px">
                    <input
                        type="text"
                        id="google_redirect_uri"
                        name="google_redirect_uri"
                        class="form-control"
                        value="{{ old('google_redirect_uri', $settings['google_redirect_uri'] ?? url('/auth/google/callback')) }}"
                        readonly
                        style="background:#f1f5f9;cursor:not-allowed"
                    >
                    <button type="button" class="btn btn-outline" onclick="copyRedirectUri()"><i class="fa-solid fa-copy"></i> Copy</button>
                </div>
                <div class="form-help">Copy this Redirect URI and add it under <b>Authorized redirect URIs</b> in Google Cloud Console.</div>
            </div>

            <!-- Help Box -->
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:16px;margin-top:24px">
                <div style="font-weight:700;color:#1e40af;font-size:13.5px;margin-bottom:6px"><i class="fa-solid fa-thumbtack"></i> How to get Google Credentials:</div>
                <ol style="font-size:12.5px;color:#1e3a8a;padding-left:18px;margin:0;line-height:1.6">
                    <li>Go to <a href="https://console.cloud.google.com" target="_blank" style="text-decoration:underline;font-weight:600">Google Cloud Console</a> and create or select a project.</li>
                    <li>Navigate to <b>APIs & Services > Credentials</b>.</li>
                    <li>Click <b>Create Credentials > OAuth Client ID</b> (Application type: <i>Web Application</i>).</li>
                    <li>Add the <b>Authorized Redirect URI</b> above.</li>
                    <li>Copy your <b>Client ID</b> & <b>Client Secret</b> and paste them into the fields above.</li>
                </ol>
            </div>

            <div style="margin-top:24px;display:flex;justify-content:flex-end">
                <button type="submit" class="btn btn-primary">Save Google Settings</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    function copyRedirectUri() {
        const input = document.getElementById('google_redirect_uri');
        input.select();
        navigator.clipboard.writeText(input.value);
        alert('Redirect URI copied to clipboard!');
    }
    </script>
    @endpush
</x-admin-layout>
