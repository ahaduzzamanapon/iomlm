<?php

$filePath = __DIR__ . '/../resources/views/admin/batches/show.blade.php';
$content = file_get_contents($filePath);

$target = '<form method="POST" action="{{ route(\'admin.batches.split-students\', $batch) }}" style="display:inline">';

$replacement = '<button type="button" class="btn btn-primary btn-sm" style="font-size:11px;padding:2px 8px;font-family:\'Kalpurush\',sans-serif" onclick="document.getElementById(\'zoomOverflowModal\').style.display=\'flex\'">
                        <i class="fa-solid fa-arrows-split-up-and-left"></i> জুম লিমিট ওভারফ্লো সমন্বয়
                    </button>
                    <form method="POST" action="{{ route(\'admin.batches.split-students\', $batch) }}" style="display:inline">';

$content = str_replace($target, $replacement, $content);

// Update select options
$targetSelect = '<option value="">— অটো / ডিফল্ট —</option>
                                            <option value="GROUP_A" {{ $en->group_tag === \'GROUP_A\' ? \'selected\' : \'\' }}>গ্রুপ ক (Group A)</option>
                                            <option value="GROUP_B" {{ $en->group_tag === \'GROUP_B\' ? \'selected\' : \'\' }}>গ্রুপ খ (Group B)</option>';

$replacementSelect = '<option value="">— অটো / ডিফল্ট —</option>
                                            <option value="MALE" {{ $en->group_tag === \'MALE\' ? \'selected\' : \'\' }}>ভাই শাখা (জুম)</option>
                                            <option value="FEMALE" {{ $en->group_tag === \'FEMALE\' ? \'selected\' : \'\' }}>বোন শাখা (জুম)</option>
                                            <option value="GROUP_A" {{ $en->group_tag === \'GROUP_A\' ? \'selected\' : \'\' }}>গ্রুপ ক (Group A)</option>
                                            <option value="GROUP_B" {{ $en->group_tag === \'GROUP_B\' ? \'selected\' : \'\' }}>গ্রুপ খ (Group B)</option>';

$content = str_replace(
    str_replace("\r\n", "\n", $targetSelect),
    str_replace("\r\n", "\n", $replacementSelect),
    str_replace("\r\n", "\n", $content)
);

// Append Modal before </x-admin-layout>
$modalHtml = '    <!-- Zoom Limit Overflow Modal -->
    <div id="zoomOverflowModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:99999;align-items:center;justify-content:center;backdrop-filter:blur(3px);">
        <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:480px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);font-family:\'Kalpurush\',sans-serif">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #e2e8f0;padding-bottom:12px;margin-bottom:16px">
                <h4 style="margin:0;font-size:16px;color:#1e293b;font-weight:700">
                    <i class="fa-solid fa-arrows-split-up-and-left" style="color:#2563eb"></i> জুম লিমিট ওভারফ্লো সমন্বয় (Zoom Limit Overflow)
                </h4>
                <button type="button" onclick="document.getElementById(\'zoomOverflowModal\').style.display=\'none\'" style="background:none;border:none;font-size:18px;color:#64748b;cursor:pointer">&times;</button>
            </div>
            <p style="font-size:12px;color:#64748b;margin-bottom:16px;line-height:1.5">
                বোন শাখায় জুম পার্টিসিপেন্ট লিমিট শেষ হয়ে গেলে শেষের রোলের বোনদের ভাইদের জুম রুমে স্থানান্তর করতে এই অপশনটি ব্যবহার করুন।
            </p>
            <form method="POST" action="{{ route(\'admin.batches.bulk-allocate-group\', $batch) }}">
                @csrf
                <div style="margin-bottom:12px">
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#334155">শিক্ষার্থী ফিল্টার:</label>
                    <select name="gender" class="form-control" style="width:100%;font-size:12px;font-family:\'Kalpurush\',sans-serif">
                        <option value="FEMALE" selected>শুধুমাত্র বোন শিক্ষার্থী (Female)</option>
                        <option value="MALE">শুধুমাত্র ভাই শিক্ষার্থী (Male)</option>
                        <option value="ALL">সকল শিক্ষার্থী (All)</option>
                    </select>
                </div>
                <div style="margin-bottom:12px">
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#334155">টার্গেট শাখা / গ্রুপ:</label>
                    <select name="target_group" class="form-control" style="width:100%;font-size:12px;font-family:\'Kalpurush\',sans-serif" required>
                        <option value="MALE" selected>ভাই শাখা (MALE - ভাইদের জুম ক্লাস)</option>
                        <option value="FEMALE">বোন শাখা (FEMALE)</option>
                        <option value="GROUP_A">গ্রুপ ক (Group A)</option>
                        <option value="GROUP_B">গ্রুপ খ (Group B)</option>
                        <option value="RESET">রিসেট করুন (পূর্বের ডিফল্ট অবস্থায় ফেরত)</option>
                    </select>
                </div>
                <div style="margin-bottom:12px">
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#334155">বরাদ্দ পদ্ধতি (Mode):</label>
                    <div style="display:flex;gap:16px;font-size:12px;margin-bottom:8px">
                        <label style="cursor:pointer;display:flex;align-items:center;gap:4px">
                            <input type="radio" name="mode" value="last_count" checked onchange="toggleOverflowMode(\'last_count\')"> শেষের N জন শিক্ষার্থী
                        </label>
                        <label style="cursor:pointer;display:flex;align-items:center;gap:4px">
                            <input type="radio" name="mode" value="roll_range" onchange="toggleOverflowMode(\'roll_range\')"> রোল নম্বর রেঞ্জ
                        </label>
                    </div>
                </div>
                <div id="modeLastCountSection" style="margin-bottom:16px">
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#334155">সর্বশেষ কতজন শিক্ষার্থী স্থানান্তর করবেন?</label>
                    <input type="number" name="last_count" class="form-control" placeholder="যেমন: 30 বা 50" value="30" min="1" style="width:100%;font-size:12px">
                </div>
                <div id="modeRollRangeSection" style="display:none;margin-bottom:16px">
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#334155">রোল রেঞ্জ (Roll From - To):</label>
                    <div style="display:flex;gap:8px">
                        <input type="text" name="roll_from" class="form-control" placeholder="রোল হতে (যেমন 150)" style="width:50%;font-size:12px">
                        <input type="text" name="roll_to" class="form-control" placeholder="রোল পর্যন্ত (যেমন 220)" style="width:50%;font-size:12px">
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px">
                    <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById(\'zoomOverflowModal\').style.display=\'none\'">বাতিল</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="font-family:\'Kalpurush\',sans-serif">সমন্বয় নিশ্চিত করুন</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function toggleOverflowMode(mode) {
            document.getElementById(\'modeLastCountSection\').style.display = mode === \'last_count\' ? \'block\' : \'none\';
            document.getElementById(\'modeRollRangeSection\').style.display = mode === \'roll_range\' ? \'block\' : \'none\';
        }
    </script>
</x-admin-layout>';

$content = str_replace('</x-admin-layout>', $modalHtml, $content);
file_put_contents($filePath, $content);
echo "Batch show view patched successfully.\n";
