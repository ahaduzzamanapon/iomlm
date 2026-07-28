<x-admin-layout>
    <x-slot name="title">Settings</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>System Settings & Business Rules</h1>
            <p>Configure institute preferences, exam eligibility rules, and result policies</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')
        <div class="grid-2" style="max-width:960px">

            <!-- General Settings -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">1. General Institute Settings</span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Institute Name</label>
                        <input type="text" name="institute_name" class="form-control" value="{{ $settings['institute_name']->value ?? 'Learning Plus Institute of Technology' }}">
                    </div>
                </div>
            </div>

            <!-- Academic & Exam Eligibility Rules -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">2. Academic & Exam Eligibility Rules</span>
                </div>
                <div class="card-body">
                    <label class="form-check" style="margin-bottom:16px">
                        <input type="checkbox" name="min_attendance_required" value="1" {{ ($settings['min_attendance_required']->value ?? '0') == '1' ? 'checked' : '' }}>
                        Require Minimum Attendance % for Exam Eligibility
                    </label>

                    <div class="form-group">
                        <label>Minimum Attendance Threshold (%)</label>
                        <input type="number" name="min_attendance_percent" class="form-control" value="{{ $settings['min_attendance_percent']->value ?? '75' }}" min="0" max="100">
                        <span class="form-help">Only applied when attendance requirement toggle is enabled above. Default is disabled (§9.2).</span>
                    </div>

                    <div class="form-group">
                        <label>Multi-Attempt Result Count Policy</label>
                        <select name="final_result_policy" class="form-control">
                            <option value="BEST_ATTEMPT" {{ ($settings['final_result_policy']->value ?? '') === 'BEST_ATTEMPT' ? 'selected' : '' }}>BEST_ATTEMPT (Count highest marks across all retakes)</option>
                            <option value="LATEST_ATTEMPT" {{ ($settings['final_result_policy']->value ?? '') === 'LATEST_ATTEMPT' ? 'selected' : '' }}>LATEST_ATTEMPT (Count most recent retake result)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Accounts Enforcement Hook -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">3. Fee Due Enforcement Guard Level</span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Enforcement Level on Academic Actions (§15)</label>
                        <select name="due_enforcement_level" class="form-control">
                            <option value="NONE" {{ ($settings['due_enforcement_level']->value ?? '') === 'NONE' ? 'selected' : '' }}>NONE (Notice only, no block)</option>
                            <option value="BLOCK_CLASS" {{ ($settings['due_enforcement_level']->value ?? '') === 'BLOCK_CLASS' ? 'selected' : '' }}>BLOCK_CLASS (Block Live Class join if due exists)</option>
                            <option value="BLOCK_EXAM" {{ ($settings['due_enforcement_level']->value ?? '') === 'BLOCK_EXAM' ? 'selected' : '' }}>BLOCK_EXAM (Block Exam Admit Card issuance if due exists)</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer" style="text-align:right">
                    <button type="submit" class="btn btn-primary btn-lg">Save Business Settings</button>
                </div>
            </div>

        </div>
    </form>
</x-admin-layout>
