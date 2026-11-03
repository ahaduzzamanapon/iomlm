<?php

// Patch conduct.blade.php
$conductPath = __DIR__ . '/../resources/views/teacher/classes/conduct.blade.php';
$c = file_get_contents($conductPath);

$targetActions = '@if($class->meeting_link)
                <a href="{{ $class->meeting_link }}" target="_blank" class="btn btn-outline btn-sm">Join Meeting</a>
            @elseif($meetingProvider === \'zoom\')
                <form method="POST" action="{{ route(\'teacher.classes.setLink\', $class) }}">
                    @csrf
                    <button class="btn btn-outline btn-sm">Generate Zoom Meeting</button>
                </form>
            @else
                <button class="btn btn-outline btn-sm" style="color:#f59e0b"
                    onclick="document.getElementById(\'linkFormTop\').style.display=\'flex\'">
                    Add Meeting Link
                </button>
                <form id="linkFormTop" method="POST" action="{{ route(\'teacher.classes.setLink\', $class) }}"
                    style="display:none;gap:6px;align-items:center">
                    @csrf
                    <input type="url" name="meeting_link" class="form-control" style="min-width:240px;font-size:12px"
                        placeholder="{{ $meetingProvider === \'google_meet\' ? \'https://meet.google.com/xxx-xxxx-xxx\' : \'Paste any meeting URL…\' }}"
                        required>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </form>
            @endif';

$replacementActions = '@if($class->meeting_link)
                <a href="{{ $class->meeting_link }}" target="_blank" class="btn btn-primary btn-sm" style="font-weight:600">
                    <i class="fa-solid fa-video"></i> ক্লাসে যুক্ত হোন (Join Live)
                </a>
                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById(\'linkFormTop\').style.display = document.getElementById(\'linkFormTop\').style.display === \'none\' ? \'flex\' : \'none\'">
                    <i class="fa-solid fa-pen"></i> লিংক পরিবর্তন
                </button>
            @else
                <form method="POST" action="{{ route(\'teacher.classes.setLink\', $class) }}" style="display:inline">
                    @csrf
                    <button class="btn btn-primary btn-sm" title="স্বয়ংক্রিয় জুম মিটিং রুম তৈরি">
                        <i class="fa-solid fa-bolt"></i> Auto Zoom Generate
                    </button>
                </form>
                <button type="button" class="btn btn-outline btn-sm" style="color:#f59e0b"
                    onclick="document.getElementById(\'linkFormTop\').style.display = document.getElementById(\'linkFormTop\').style.display === \'none\' ? \'flex\' : \'none\'">
                    <i class="fa-solid fa-link"></i> লিংক পেস্ট করুন
                </button>
            @endif
            <form id="linkFormTop" method="POST" action="{{ route(\'teacher.classes.setLink\', $class) }}"
                style="display:none;gap:6px;align-items:center">
                @csrf
                <input type="text" name="meeting_link" class="form-control" style="min-width:240px;font-size:12px"
                    value="{{ $class->meeting_link }}"
                    placeholder="Zoom বা Google Meet লিংক পেস্ট করুন (যেমন zoom.us/j/...)"
                    required>
                <button type="submit" class="btn btn-primary btn-sm">সংরক্ষণ</button>
            </form>';

$c = str_replace(
    str_replace("\r\n", "\n", $targetActions),
    str_replace("\r\n", "\n", $replacementActions),
    str_replace("\r\n", "\n", $c)
);

// Add instruction alert banner right below page-header
$guideBanner = '<div class="alert alert-info" style="margin-bottom:16px;font-size:12px;display:flex;align-items:flex-start;gap:10px;font-family:\'Kalpurush\',sans-serif;background:#f0f9ff;border:1px solid #bae6fd;color:#0369a1;border-radius:8px;padding:12px 16px;">
        <i class="fa-solid fa-circle-info" style="font-size:16px;margin-top:2px;color:#0284c7"></i>
        <div>
            <strong>লাইভ ক্লাস পরিচালনা ও জুম লিংক নির্দেশিকা:</strong>
            <ul style="margin:4px 0 0 16px;padding:0;line-height:1.6">
                <li><strong>পদ্ধতি ১ (অটো জেনারেট):</strong> "Auto Zoom Generate" বাটনে ক্লিক করলে সিস্টেম স্বয়ংক্রিয়ভাবে জুম মিটিং রুম তৈরি করে লিংক যুক্ত করে দেবে।</li>
                <li><strong>পদ্ধতি ২ (ম্যানুয়ালি পেস্ট):</strong> নিজস্ব জুম অ্যাপ বা গুগল মিটের লিংক "লিংক পেস্ট করুন" বক্সে বসিয়ে সংরক্ষণ করতে পারেন (লিংকের শুরুতে https না থাকলেও সিস্টেম নিজে ঠিক করে নেবে)।</li>
                <li>শিক্ষার্থীরা তাদের পোর্টাল থেকে "Join Class" এ ক্লিক করা মাত্রই তাদের উপস্থিতি (Present) স্বয়ংক্রিয়ভাবে রেকর্ড হয়ে যাবে।</li>
            </ul>
        </div>
    </div>';

$targetHeaderEnd = '</div>' . "\n\n" . '    @if($class->meeting_link || $class->zoom_meeting_id)';
$replacementHeaderEnd = '</div>' . "\n\n    " . $guideBanner . "\n\n" . '    @if($class->meeting_link || $class->zoom_meeting_id)';

$c = str_replace(
    str_replace("\r\n", "\n", $targetHeaderEnd),
    str_replace("\r\n", "\n", $replacementHeaderEnd),
    str_replace("\r\n", "\n", $c)
);

file_put_contents($conductPath, $c);
echo "conduct.blade.php updated.\n";
