<x-admin-layout>
    <x-slot name="title">Send Notification Broadcast</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <a href="{{ route('admin.notifications.index') }}" style="color:#64748b;text-decoration:none;font-weight:600;font-size:13px">← Back to History</a>
            <h1 style="margin-top:4px">Compose &amp; Send Broadcast Notification</h1>
            <p>Target specific students, batches, or semesters via Firebase Push Notification and Email</p>
        </div>
    </div>

    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-weight:600">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.notifications.send') }}" enctype="multipart/form-data">
        @csrf
        <div style="display:grid;grid-template-columns: 1fr 340px;gap:24px">

            {{-- MAIN FORM --}}
            <div style="display:flex;flex-direction:column;gap:20px">

                <!-- 1. CHANNEL & TARGET AUDIENCE -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">1. Broadcast Channel &amp; Target Audience</span>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Delivery Channel / Medium <span class="required">*</span></label>
                            <div style="display:flex;gap:16px;margin-top:6px">
                                <label style="display:flex;align-items:center;gap:8px;padding:12px 16px;background:#f8fafc;border:2px solid #e2e8f0;border-radius:8px;cursor:pointer;flex:1">
                                    <input type="radio" name="channel" value="BOTH" checked>
                                    <div>
                                        <div style="font-weight:700;color:#0f172a">Push + Email</div>
                                        <div style="font-size:11px;color:#64748b">Send to both devices &amp; inbox</div>
                                    </div>
                                </label>
                                <label style="display:flex;align-items:center;gap:8px;padding:12px 16px;background:#f8fafc;border:2px solid #e2e8f0;border-radius:8px;cursor:pointer;flex:1">
                                    <input type="radio" name="channel" value="PUSH">
                                    <div>
                                        <div style="font-weight:700;color:#0f172a">Push Only</div>
                                        <div style="font-size:11px;color:#64748b">Firebase Push Notification</div>
                                    </div>
                                </label>
                                <label style="display:flex;align-items:center;gap:8px;padding:12px 16px;background:#f8fafc;border:2px solid #e2e8f0;border-radius:8px;cursor:pointer;flex:1">
                                    <input type="radio" name="channel" value="EMAIL">
                                    <div>
                                        <div style="font-weight:700;color:#0f172a">Email Only</div>
                                        <div style="font-size:11px;color:#64748b">SMTP Email Broadcast</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Target Audience Filter <span class="required">*</span></label>
                            <select name="recipient_type" id="recipient_type" class="form-control" onchange="toggleRecipientFilters(this.value)">
                                <option value="ALL_STUDENTS">All Active Students</option>
                                <option value="ALL_TEACHERS">All Faculty Members / Teachers</option>
                                <option value="SPECIFIC_STUDENT">Specific Student (Select individual student)</option>
                                <option value="BATCH_WISE">Batch Wise (Select specific batch)</option>
                                <option value="SEMESTER_WISE">Semester Wise (Select course &amp; semester)</option>
                            </select>
                        </div>

                        {{-- DYNAMIC FILTER: SPECIFIC STUDENT --}}
                        <div id="filter_specific_student" style="display:none">
                            <div class="form-group">
                                <label>Select Student <span class="required">*</span></label>
                                <select name="specific_student_id" class="form-control">
                                    <option value="">— Choose Student —</option>
                                    @foreach($students as $st)
                                        <option value="{{ $st->user_id }}">{{ $st->name }} (Roll: {{ $st->roll_no ?? 'N/A' }} · {{ $st->user->email ?? '' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- DYNAMIC FILTER: BATCH WISE --}}
                        <div id="filter_batch_wise" style="display:none">
                            <div class="form-group">
                                <label>Select Batch <span class="required">*</span></label>
                                <select name="batch_id" class="form-control">
                                    <option value="">— Choose Batch —</option>
                                    @foreach($batches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- DYNAMIC FILTER: SEMESTER WISE --}}
                        <div id="filter_semester_wise" style="display:none">
                            <div class="form-group">
                                <label>Select Course &amp; Semester <span class="required">*</span></label>
                                <select name="semester_id" class="form-control">
                                    <option value="">— Choose Semester —</option>
                                    @foreach($courses as $c)
                                        <optgroup label="{{ $c->name }}">
                                            @foreach($c->semesters as $sem)
                                                <option value="{{ $sem->id }}">{{ $c->name }} — {{ $sem->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. MESSAGE CONTENT -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">2. Notification Message &amp; Media</span>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Message Title / Subject <span class="required">*</span></label>
                            <input type="text" name="title" id="title_input" class="form-control"
                                placeholder="e.g. Important Notice: Mid-Term Examination Schedule Released"
                                required oninput="updateLivePreview()">
                        </div>

                        <div class="form-group">
                            <label>Message Body / Content <span class="required">*</span></label>
                            <textarea name="message" id="message_input" class="form-control" rows="5"
                                placeholder="Write your announcement or notification details here..."
                                required oninput="updateLivePreview()"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Image Banner Upload (optional)</label>
                                <input type="file" name="image_file" class="form-control" accept="image/*">
                                <span class="form-help">Upload image file (JPG/PNG, max 3MB)</span>
                            </div>
                            <div class="form-group">
                                <label>OR Image URL (optional)</label>
                                <input type="url" name="image_url" id="image_url_input" class="form-control"
                                    placeholder="https://..." oninput="updateLivePreview()">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Action Button Link / URL (optional)</label>
                            <input type="url" name="action_url" class="form-control"
                                placeholder="e.g. https://iom.edu.bd/student/exams or route link">
                            <span class="form-help">Clicking the push notification or email CTA button opens this page.</span>
                        </div>
                    </div>
                    <div class="card-footer" style="text-align:right">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Dispatch Notification Broadcast
                        </button>
                    </div>
                </div>

            </div>

            {{-- LIVE PREVIEW CARD --}}
            <div>
                <div style="position:sticky;top:20px">
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title">Live Notification Preview</span>
                        </div>
                        <div class="card-body" style="background:#f8fafc">
                            <div style="background:#ffffff;border-radius:12px;padding:16px;box-shadow:0 4px 16px rgba(0,0,0,0.08);border:1px solid #e2e8f0">
                                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                                    <img src="{{ asset('images/logo.png') }}" style="width:24px;height:24px;object-fit:contain">
                                    <span style="font-size:12px;font-weight:700;color:#1e293b">IOM Learning Plus</span>
                                    <span style="margin-left:auto;font-size:10px;color:#94a3b8">now</span>
                                </div>
                                <div id="preview_title" style="font-weight:700;font-size:14px;color:#0f172a;margin-bottom:6px">
                                    Notification Title...
                                </div>
                                <div id="preview_body" style="font-size:12px;color:#475569;line-height:1.4;white-space:pre-line">
                                    Notification body text preview will appear here in real-time as you type.
                                </div>
                                <div id="preview_img_box" style="display:none;margin-top:10px">
                                    <img id="preview_img" src="" style="width:100%;border-radius:8px;max-height:140px;object-fit:cover">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <script>
    function toggleRecipientFilters(val) {
        document.getElementById('filter_specific_student').style.display = val === 'SPECIFIC_STUDENT' ? 'block' : 'none';
        document.getElementById('filter_batch_wise').style.display       = val === 'BATCH_WISE'        ? 'block' : 'none';
        document.getElementById('filter_semester_wise').style.display    = val === 'SEMESTER_WISE'     ? 'block' : 'none';
    }

    function updateLivePreview() {
        const title = document.getElementById('title_input').value;
        const body  = document.getElementById('message_input').value;
        const img   = document.getElementById('image_url_input').value;

        document.getElementById('preview_title').textContent = title || 'Notification Title...';
        document.getElementById('preview_body').textContent  = body  || 'Notification body text preview will appear here in real-time as you type.';

        const imgBox = document.getElementById('preview_img_box');
        const previewImg = document.getElementById('preview_img');
        if (img) {
            previewImg.src = img;
            imgBox.style.display = 'block';
        } else {
            imgBox.style.display = 'none';
        }
    }
    </script>
</x-admin-layout>
