<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->title }} — IOM Form</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            padding: 30px 15px;
            margin: 0;
        }
        .survey-container {
            max-width: 680px;
            margin: 0 auto;
        }
        .form-card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
            padding: 24px 28px;
        }
        .header-card {
            border-top: 8px solid #2563eb;
        }
        .question-label {
            font-weight: 700;
            font-size: 15px;
            color: #0f172a;
            margin-bottom: 8px;
            display: block;
        }
        .req-star {
            color: #dc2626;
            margin-left: 4px;
        }
        .help-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            margin-bottom: 8px;
        }
        .choice-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: background .15s;
        }
        .choice-option:hover {
            background: #f8fafc;
        }
        .btn-submit {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #ffffff;
            border: none;
            padding: 12px 32px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
            transition: transform .15s, box-shadow .15s;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37,99,235,0.4);
        }
    </style>
</head>
<body>
    <div class="survey-container">
        
        {{-- Header Card --}}
        <div class="form-card header-card">
            @if($survey->banner_image)
                <div style="margin:-24px -28px 20px; overflow:hidden; border-top-left-radius:14px; border-top-right-radius:14px">
                    <img src="{{ $survey->banner_image }}" alt="Survey Banner" style="width:100%; max-height:220px; object-fit:cover; display:block">
                </div>
            @endif

            <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px">
                <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" style="width:32px; height:32px; object-fit:contain">
                <span style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.05em">Islamic Online Madrasah (IOM)</span>
            </div>

            <h1 style="margin:0 0 10px; font-size:24px; font-weight:800; color:#0f172a; line-height:1.3">
                {{ $survey->title }}
            </h1>

            @if($survey->description)
                <div style="font-size:14px; color:#475569; line-height:1.6; border-top:1px solid #f1f5f9; padding-top:12px; margin-top:12px">
                    {!! nl2br(e($survey->description)) !!}
                </div>
            @endif

            <div style="font-size:12px; color:#94a3b8; margin-top:14px">
                * Indicates required question
            </div>
        </div>

        @if($errors->any())
            <div class="form-card" style="border-left:5px solid #dc2626; background:#fef2f2">
                <strong style="color:#991b1b; font-size:14px">Please correct the errors below:</strong>
                <ul style="margin:8px 0 0; padding-left:20px; color:#b91c1c; font-size:13px">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('public.survey.submit', $survey->slug) }}" enctype="multipart/form-data">
            @csrf

            {{-- Optional Respondent Info Card if not logged in --}}
            @guest
                <div class="form-card">
                    <label class="question-label">Your Name &amp; Contact Info (Optional)</label>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
                        <div>
                            <input type="text" name="respondent_name" class="form-control" placeholder="Your Name" value="{{ old('respondent_name') }}">
                        </div>
                        <div>
                            <input type="email" name="respondent_email" class="form-control" placeholder="Your Email Address" value="{{ old('respondent_email') }}">
                        </div>
                    </div>
                </div>
            @endguest

            {{-- Dynamic Survey Questions Cards --}}
            @foreach($survey->fields as $field)
                @php $fieldKey = 'field_' . $field->id; @endphp
                <div class="form-card">
                    <label class="question-label">
                        {{ $field->label }}
                        @if($field->is_required)
                            <span class="req-star">*</span>
                        @endif
                    </label>

                    @if($field->help_text)
                        <div class="help-text">{{ $field->help_text }}</div>
                    @endif

                    <div style="margin-top:10px">
                        {{-- 1. Short Text --}}
                        @if($field->field_type === 'text')
                            <input type="text" name="{{ $fieldKey }}" class="form-control" value="{{ old($fieldKey) }}" {{ $field->is_required ? 'required' : '' }} placeholder="Your answer">

                        {{-- 2. Paragraph Textarea --}}
                        @elseif($field->field_type === 'textarea')
                            <textarea name="{{ $fieldKey }}" class="form-control" rows="3" {{ $field->is_required ? 'required' : '' }} placeholder="Your detailed response">{{ old($fieldKey) }}</textarea>

                        {{-- 3. Number --}}
                        @elseif($field->field_type === 'number')
                            <input type="number" step="any" name="{{ $fieldKey }}" class="form-control" value="{{ old($fieldKey) }}" {{ $field->is_required ? 'required' : '' }} placeholder="0">

                        {{-- 4. Date --}}
                        @elseif($field->field_type === 'date')
                            <input type="date" name="{{ $fieldKey }}" class="form-control" value="{{ old($fieldKey) }}" {{ $field->is_required ? 'required' : '' }}>

                        {{-- 5. Dropdown Select --}}
                        @elseif($field->field_type === 'select')
                            <select name="{{ $fieldKey }}" class="form-control" {{ $field->is_required ? 'required' : '' }}>
                                <option value="">-- Choose an option --</option>
                                @foreach($field->options ?? [] as $opt)
                                    <option value="{{ $opt }}" {{ old($fieldKey) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>

                        {{-- 6. Radio Buttons (Single Choice) --}}
                        @elseif($field->field_type === 'radio')
                            @foreach($field->options ?? [] as $opt)
                                <label class="choice-option">
                                    <input type="radio" name="{{ $fieldKey }}" value="{{ $opt }}" {{ old($fieldKey) == $opt ? 'checked' : '' }} {{ $field->is_required ? 'required' : '' }}>
                                    <span style="font-size:14px">{{ $opt }}</span>
                                </label>
                            @endforeach

                        {{-- 7. Checkboxes (Multiple Choice) --}}
                        @elseif($field->field_type === 'checkbox')
                            @foreach($field->options ?? [] as $opt)
                                @php $checked = is_array(old($fieldKey)) && in_array($opt, old($fieldKey)); @endphp
                                <label class="choice-option">
                                    <input type="checkbox" name="{{ $fieldKey }}[]" value="{{ $opt }}" {{ $checked ? 'checked' : '' }}>
                                    <span style="font-size:14px">{{ $opt }}</span>
                                </label>
                            @endforeach

                        {{-- 8. File / Image Upload --}}
                        @elseif(in_array($field->field_type, ['file', 'image']))
                            <input type="file" name="{{ $fieldKey }}" class="form-control" {{ $field->field_type === 'image' ? 'accept="image/*"' : '' }} {{ $field->is_required ? 'required' : '' }}>
                            <div style="font-size:11px; color:#94a3b8; margin-top:4px">
                                {{ $field->field_type === 'image' ? 'Upload an image file (JPG, PNG, WebP up to 5MB).' : 'Upload document file (up to 10MB).' }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px">
                <button type="submit" class="btn-submit">
                    Submit Response →
                </button>
                <button type="reset" class="btn btn-outline" style="font-size:13px">Clear form</button>
            </div>
        </form>

        <div style="text-align:center; font-size:12px; color:#94a3b8; margin-top:30px">
            Powered by IOM ERP
        </div>
    </div>
</body>
</html>
