<?php

$todayPath = __DIR__ . '/../resources/views/teacher/classes/today.blade.php';
$c = file_get_contents($todayPath);

$target = '@if($cs->meeting_link)
                            <a href="{{ $cs->meeting_link }}" target="_blank" class="btn btn-primary btn-sm">Join Class</a>
                        @elseif($cs->status !== \'COMPLETED\' && $cs->status !== \'CANCELLED\')
                            @if($meetingProvider === \'zoom\')
                                {{-- Zoom: one-click API generate --}}
                                <form method="POST" action="{{ route(\'teacher.classes.setLink\', $cs) }}">
                                    @csrf
                                    <button class="btn btn-outline btn-sm" style="color:#2563eb">Generate Zoom Meeting</button>
                                </form>
                            @else
                                {{-- Google Meet / Manual: show inline paste form --}}
                                <button class="btn btn-outline btn-sm" style="color:#f59e0b"
                                    onclick="document.getElementById(\'linkForm{{ $cs->id }}\').style.display=\'flex\'">
                                    Add Meeting Link
                                </button>
                                <form id="linkForm{{ $cs->id }}" method="POST"
                                    action="{{ route(\'teacher.classes.setLink\', $cs) }}"
                                    style="display:none;gap:6px;align-items:center">
                                    @csrf
                                    <input type="url" name="meeting_link" class="form-control"
                                        style="min-width:260px;font-size:12px"
                                        placeholder="{{ $meetingProvider === \'google_meet\' ? \'https://meet.google.com/xxx-xxxx-xxx\' : \'Paste meeting URL here…\' }}"
                                        required>
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                </form>
                            @endif
                        @endif';

$replacement = '@if($cs->meeting_link)
                            <a href="{{ $cs->meeting_link }}" target="_blank" class="btn btn-primary btn-sm">Join Class</a>
                            <button type="button" class="btn btn-outline btn-sm" style="font-size:11px"
                                onclick="document.getElementById(\'linkForm{{ $cs->id }}\').style.display = document.getElementById(\'linkForm{{ $cs->id }}\').style.display === \'none\' ? \'flex\' : \'none\'">
                                লিংক এডিট
                            </button>
                        @elseif($cs->status !== \'COMPLETED\' && $cs->status !== \'CANCELLED\')
                            <form method="POST" action="{{ route(\'teacher.classes.setLink\', $cs) }}" style="display:inline">
                                @csrf
                                <button class="btn btn-outline btn-sm" style="color:#2563eb" title="Auto Generate Zoom">
                                    <i class="fa-solid fa-bolt"></i> Auto Zoom
                                </button>
                            </form>
                            <button class="btn btn-outline btn-sm" style="color:#f59e0b"
                                onclick="document.getElementById(\'linkForm{{ $cs->id }}\').style.display = document.getElementById(\'linkForm{{ $cs->id }}\').style.display === \'none\' ? \'flex\' : \'none\'">
                                লিংক পেস্ট
                            </button>
                        @endif

                        @if($cs->status !== \'COMPLETED\' && $cs->status !== \'CANCELLED\')
                        <form id="linkForm{{ $cs->id }}" method="POST"
                            action="{{ route(\'teacher.classes.setLink\', $cs) }}"
                            style="display:none;gap:6px;align-items:center">
                            @csrf
                            <input type="text" name="meeting_link" class="form-control"
                                style="min-width:220px;font-size:11px"
                                value="{{ $cs->meeting_link }}"
                                placeholder="Zoom বা Google Meet লিংক..."
                                required>
                            <button type="submit" class="btn btn-primary btn-sm" style="font-size:11px">Save</button>
                        </form>
                        @endif';

$c = str_replace(
    str_replace("\r\n", "\n", $target),
    str_replace("\r\n", "\n", $replacement),
    str_replace("\r\n", "\n", $c)
);

file_put_contents($todayPath, $c);
echo "today.blade.php updated.\n";
