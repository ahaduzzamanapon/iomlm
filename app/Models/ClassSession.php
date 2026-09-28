<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSession extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'session_date'    => 'date',
        'is_extra_class'  => 'boolean',
        'teacher_present' => 'boolean',
        'class_conducted' => 'boolean',
        'has_recording'   => 'boolean',
        'recorded_videos' => 'array',
        'started_at'      => 'datetime',
        'ended_at'        => 'datetime',
    ];

    protected $appends = [
        'is_extra',
        'has_recorded_videos',
        'video_count',
    ];

    // ── Relationships ──────────────────────────────────────────

    /** Optional curriculum plan link */
    public function timeline()
    {
        return $this->belongsTo(Timeline::class, 'timeline_id');
    }

    /** Recurring routine entry this session belongs to */
    public function routineEntry()
    {
        return $this->belongsTo(RoutineEntry::class, 'routine_entry_id');
    }

    /** Direct subject ref (no need to join timeline) */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /** Direct batch ref */
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /** Which module the teacher covered in this session (optional) */
    public function moduleCovered()
    {
        return $this->belongsTo(SubjectModule::class, 'module_covered_id');
    }

    public function mergedGroups()
    {
        return $this->hasMany(MergedClassGroup::class, 'class_session_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_session_id');
    }

    // ── Accessors ──────────────────────────────────────────────

    /** Resolved subject name */
    public function getSubjectNameAttribute(): string
    {
        return $this->subject?->name ?? '—';
    }

    /** Resolved batch name */
    public function getBatchNameAttribute(): string
    {
        return $this->batch?->name ?? '—';
    }

    /** Display date */
    public function getDisplayDateAttribute(): string
    {
        return $this->session_date
            ? \Carbon\Carbon::parse($this->session_date)->format('d M Y (D)')
            : 'TBA';
    }

    /** Resolved group label */
    public function getGroupLabelAttribute(): string
    {
        return match($this->group_tag) {
            'MALE'    => 'ভাই শাখা',
            'FEMALE'  => 'বোন শাখা',
            'GROUP_A' => 'গ্রুপ ক',
            'GROUP_B' => 'গ্রুপ খ',
            'ALL'     => 'যৌথ',
            default   => 'যৌথ',
        };
    }

    /** Check if this is an extra class */
    public function getIsExtraAttribute(): bool
    {
        return (bool) ($this->is_extra_class || empty($this->routine_entry_id));
    }

    /** Bangld label for class type */
    public function getClassTypeLabelAttribute(): string
    {
        return $this->is_extra ? 'এক্সট্রা ক্লাস' : 'নিয়মিত ক্লাস';
    }

    /**
     * Normalized recorded videos list for this class session.
     * Supports multiple videos (Part 1, Part 2, etc.) or single video fallback.
     */
    public function getVideosAttribute(): array
    {
        $rawVideos = $this->recorded_videos;
        if (is_string($rawVideos)) {
            $decoded = json_decode($rawVideos, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $rawVideos = $decoded;
            }
        }

        $list = [];
        if (!empty($rawVideos) && is_array($rawVideos)) {
            foreach ($rawVideos as $idx => $v) {
                if (!is_array($v)) continue;
                $url       = trim($v['url'] ?? '');
                $file      = trim($v['file'] ?? ($v['existing_file'] ?? ''));
                $embedCode = trim($v['embed_code'] ?? '');
                $title     = trim($v['title'] ?? '') ?: ('ক্লাস ভিডিও ' . ($idx + 1));

                if (empty($url) && empty($file) && empty($embedCode)) {
                    continue;
                }

                $list[] = $this->formatVideoPayload($title, $url, $file, $embedCode);
            }
        }

        // Fallback for single/legacy columns if recorded_videos was empty
        if (empty($list) && (!empty($this->recording_url) || !empty($this->recording_file) || !empty($this->recording_embed))) {
            $list[] = $this->formatVideoPayload(
                'ক্লাস ভিডিও রেকর্ড',
                trim($this->recording_url ?? ''),
                trim($this->recording_file ?? ''),
                trim($this->recording_embed ?? '')
            );
        }

        return $list;
    }

    /**
     * Format individual video structure with player metadata
     */
    protected function formatVideoPayload(string $title, ?string $url, ?string $file, ?string $embedCode): array
    {
        $type = 'generic';
        $embedUrl = null;
        $fileUrl = null;

        if (!empty($file)) {
            $type = 'file';
            $fileUrl = str_starts_with($file, 'http') ? $file : asset('storage/' . ltrim($file, '/'));
        } elseif (!empty($embedCode)) {
            $type = 'embed';
        } elseif (!empty($url)) {
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $m)) {
                $type = 'youtube';
                $embedUrl = 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
            } elseif (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)/i', $url, $m)) {
                $type = 'vimeo';
                $embedUrl = 'https://player.vimeo.com/video/' . $m[3];
            } elseif (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/i', $url, $m)) {
                $type = 'drive';
                $embedUrl = 'https://drive.google.com/file/d/' . $m[1] . '/preview';
            } elseif (preg_match('/\.(mp4|webm|ogg|mov)(\?.*)?$/i', $url)) {
                $type = 'file';
                $fileUrl = $url;
            } else {
                $type = 'url';
            }
        }

        return [
            'title'      => $title,
            'url'        => $url ?: null,
            'file'       => $file ?: null,
            'file_url'   => $fileUrl,
            'embed_code' => $embedCode ?: null,
            'type'       => $type,
            'embed_url'  => $embedUrl,
        ];
    }

    /** Check if this session has any recorded videos */
    public function getHasRecordedVideosAttribute(): bool
    {
        return !empty($this->videos) || (bool)$this->has_recording;
    }

    /** Count of recorded videos */
    public function getVideoCountAttribute(): int
    {
        return count($this->videos);
    }

    /** Scope to only extra classes */
    public function scopeExtra($query)
    {
        return $query->where(function ($q) {
            $q->where('is_extra_class', true)
              ->orWhereNull('routine_entry_id');
        });
    }

    /** Scope to regular routine classes */
    public function scopeRegular($query)
    {
        return $query->where('is_extra_class', false)
                     ->whereNotNull('routine_entry_id');
    }

    /** Scope to classes having recorded video */
    public function scopeWithRecording($query)
    {
        return $query->where(function ($q) {
            $q->where('has_recording', true)
              ->orWhereNotNull('recorded_videos')
              ->orWhereNotNull('recording_url')
              ->orWhereNotNull('recording_file')
              ->orWhereNotNull('recording_embed');
        });
    }
}
