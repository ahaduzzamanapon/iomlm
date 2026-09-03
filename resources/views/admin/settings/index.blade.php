<x-admin-layout>
    <x-slot name="title">Settings</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>System Settings & Business Rules</h1>
            <p>Configure institute preferences, exam eligibility rules, and meeting platform</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')
        <div style="display:flex;flex-direction:column;gap:20px;max-width:960px">

            <!-- 1. General -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">1. General Institute Settings</span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Institute Name</label>
                        <input type="text" name="institute_name" class="form-control"
                            value="{{ $settings['institute_name']->value ?? 'Learning Plus Institute of Technology' }}">
                    </div>
                    <div class="form-group">
                        <label>Weekend Days</label>
                        <input type="text" name="weekend_days" class="form-control"
                            placeholder="e.g. FRI,SAT"
                            value="{{ $settings['weekend_days']->value ?? 'FRI,SAT' }}">
                        <span class="form-help">Comma-separated day codes (FRI, SAT, SUN …)</span>
                    </div>
                </div>
            </div>

            <!-- 2. Meeting Platform -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">2. Meeting Platform Configuration</span>
                    <span style="font-size:11px;color:var(--text-muted)">Used when teacher clicks "Generate Link"</span>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label>Meeting Provider <span class="required">*</span></label>
                        <select name="meeting_provider" id="meeting_provider" class="form-control"
                            onchange="toggleMeetingConfig(this.value)">
                            <option value="manual"
                                {{ ($settings['meeting_provider']->value ?? 'manual') === 'manual' ? 'selected' : '' }}>
                                Manual — Teacher pastes their own link
                            </option>
                            <option value="google_meet"
                                {{ ($settings['meeting_provider']->value ?? '') === 'google_meet' ? 'selected' : '' }}>
                                Google Meet — Teacher pastes Google Meet link
                            </option>
                            <option value="zoom"
                                {{ ($settings['meeting_provider']->value ?? 'zoom') === 'zoom' ? 'selected' : '' }}>
                                Zoom — Auto-generate via Zoom API
                            </option>
                        </select>
                        <span class="form-help">
                            <strong>Manual / Google Meet:</strong> Teacher pastes the link manually.<br>
                            <strong>Zoom:</strong> System auto-creates a Zoom meeting and stores the real join URL.
                        </span>
                    </div>

                    {{-- Zoom API Credentials --}}
                    <div id="zoom_config" style="{{ ($settings['meeting_provider']->value ?? 'manual') === 'zoom' ? '' : 'display:none' }}">
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:16px;margin-bottom:16px">
                            <p style="font-size:13px;font-weight:600;color:#065f46;margin-bottom:4px"><i class="fa-solid fa-key" style="color:#059669"></i> Zoom Server-to-Server OAuth App</p>
                            <p style="font-size:12px;color:#047857;margin-bottom:0">
                                Create a <strong>Server-to-Server OAuth</strong> app at
                                <a href="https://marketplace.zoom.us/develop/create" target="_blank" style="color:#059669;font-weight:600">marketplace.zoom.us</a><br>
                                Required scope: <code style="background:#d1fae5;padding:1px 4px;border-radius:3px">meeting:write:admin</code>
                            </p>
                        </div>
                        <div class="form-group">
                            <label>Zoom Account ID</label>
                            <input type="text" name="zoom_account_id" class="form-control"
                                placeholder="e.g. aBcDeFgHiJkL…"
                                value="{{ $settings['zoom_account_id']->value ?? 'Df3MHQ2pRtC6a9V4L9TyHA' }}">
                        </div>
                        <div class="form-group">
                            <label>Zoom Client ID</label>
                            <input type="text" name="zoom_client_id" class="form-control"
                                placeholder="e.g. xYzAbCdEfGhIj…"
                                value="{{ $settings['zoom_client_id']->value ?? 'tf_Z6Tn5TI6PLVsm6Ft58A' }}">
                        </div>
                        <div class="form-group">
                            <label>Zoom Client Secret</label>
                            <input type="password" name="zoom_client_secret" class="form-control"
                                value="{{ $settings['zoom_client_secret']->value ?? 'yGFSWGCvfWCyce5J17pkqyZcggePRDBC' }}"
                                placeholder="Enter Client Secret"
                                autocomplete="new-password">
                            <span class="form-help" style="color:#059669">Client Secret is configured</span>
                        </div>
                        <div class="form-group">
                            <label>Default Meeting Duration (minutes)</label>
                            <input type="number" name="zoom_meeting_duration" class="form-control"
                                min="15" max="480"
                                value="{{ $settings['zoom_meeting_duration']->value ?? '60' }}">
                        </div>
                    </div>

                    {{-- Google Meet info --}}
                    <div id="gmeet_config" style="{{ ($settings['meeting_provider']->value ?? '') === 'google_meet' ? '' : 'display:none' }}">
                        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px">
                            <p style="font-size:13px;font-weight:600;color:#1e40af;margin-bottom:4px"><i class="fa-solid fa-circle-info" style="color:#2563eb"></i> Google Meet — Manual Paste Mode</p>
                            <p style="font-size:12px;color:#1d4ed8;margin-bottom:0">
                                Teacher creates a meeting at
                                <a href="https://meet.google.com" target="_blank" style="color:#2563eb;font-weight:600">meet.google.com</a>
                                and pastes the link into the session form. No API key required.
                            </p>
                        </div>
                    </div>

                    {{-- Manual info --}}
                    <div id="manual_config" style="{{ in_array($settings['meeting_provider']->value ?? 'manual', ['manual', '']) ? '' : 'display:none' }}">
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px">
                            <p style="font-size:13px;font-weight:600;color:#475569;margin-bottom:4px"><i class="fa-solid fa-circle-info" style="color:#64748b"></i> Manual Link Mode</p>
                            <p style="font-size:12px;color:#64748b;margin-bottom:0">
                                Teacher pastes any meeting URL (Zoom, Teams, Google Meet, Jitsi…) into the class session form.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 3. Academic Rules -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">3. Academic &amp; Exam Eligibility Rules</span>
                </div>
                <div class="card-body">
                    <label class="form-check" style="margin-bottom:16px">
                        <input type="checkbox" name="min_attendance_required" value="1"
                            {{ ($settings['min_attendance_required']->value ?? '0') == '1' ? 'checked' : '' }}>
                        Require Minimum Attendance % for Exam Eligibility
                    </label>
                    <div class="form-group">
                        <label>Minimum Attendance Threshold (%)</label>
                        <input type="number" name="min_attendance_percent" class="form-control"
                            value="{{ $settings['min_attendance_percent']->value ?? '75' }}" min="0" max="100">
                        <span class="form-help">Only applied when attendance requirement toggle is enabled above.</span>
                    </div>
                    <div class="form-group">
                        <label>Multi-Attempt Result Count Policy</label>
                        <select name="final_result_policy" class="form-control">
                            <option value="BEST_ATTEMPT"   {{ ($settings['final_result_policy']->value ?? '') === 'BEST_ATTEMPT'   ? 'selected' : '' }}>BEST_ATTEMPT — Count highest marks across all retakes</option>
                            <option value="LATEST_ATTEMPT" {{ ($settings['final_result_policy']->value ?? '') === 'LATEST_ATTEMPT' ? 'selected' : '' }}>LATEST_ATTEMPT — Count most recent retake result</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 4. Fee Enforcement -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">4. Fee Due Enforcement Guard Level</span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Enforcement Level on Academic Actions</label>
                        <select name="due_enforcement_level" class="form-control">
                            <option value="NONE"        {{ ($settings['due_enforcement_level']->value ?? '') === 'NONE'        ? 'selected' : '' }}>NONE — Notice only, no block</option>
                            <option value="BLOCK_CLASS" {{ ($settings['due_enforcement_level']->value ?? '') === 'BLOCK_CLASS' ? 'selected' : '' }}>BLOCK_CLASS — Block Live Class join if due exists</option>
                            <option value="BLOCK_EXAM"  {{ ($settings['due_enforcement_level']->value ?? '') === 'BLOCK_EXAM'  ? 'selected' : '' }}>BLOCK_EXAM — Block Exam Admit Card if due exists</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer" style="text-align:right">
                    <button type="submit" class="btn btn-primary btn-lg">Save All Settings</button>
                </div>
            </div>

        </div>
    </form>

    <script>
    function toggleMeetingConfig(val) {
        document.getElementById('zoom_config').style.display   = val === 'zoom'        ? '' : 'none';
        document.getElementById('gmeet_config').style.display  = val === 'google_meet' ? '' : 'none';
        document.getElementById('manual_config').style.display = val === 'manual'      ? '' : 'none';
    }
    </script>
</x-admin-layout>
