<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectModule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_hidden'                => 'boolean',
        'is_active'                => 'boolean',
        'is_locked_until_previous' => 'boolean',
        'recorded_videos'          => 'array',
    ];

    protected $appends = [
        'videos',
        'has_recorded_videos',
        'video_count',
    ];

    /**
     * Get array of all recorded class videos with backward compatibility
     *
     * @return array
     */
    public function getVideosAttribute(): array
    {
        $videos = $this->recorded_videos;
        if (is_string($videos)) {
            $videos = json_decode($videos, true) ?: [];
        }
        if (is_array($videos) && !empty($videos)) {
            return $videos;
        }

        // Backward compatibility for legacy single recorded_url & embed_code
        if (!empty($this->recorded_url) || !empty($this->embed_code)) {
            return [
                [
                    'id'         => 'legacy_' . $this->id,
                    'title'      => $this->title . ' (ক্লাস রেকর্ড)',
                    'url'        => $this->recorded_url,
                    'embed_code' => $this->embed_code,
                    'file_path'  => null,
                ]
            ];
        }

        return [];
    }

    /**
     * Check if module has any recorded class videos
     */
    public function getHasRecordedVideosAttribute(): bool
    {
        return count($this->videos) > 0;
    }

    /**
     * Get count of recorded class videos
     */
    public function getVideoCountAttribute(): int
    {
        return count($this->videos);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function learningResources()
    {
        return $this->hasMany(LearningResource::class, 'module_id');
    }

    public function timelines()
    {
        return $this->hasMany(Timeline::class, 'module_id');
    }

    public function cloneModule(): SubjectModule
    {
        $maxSeq = SubjectModule::where('subject_id', $this->subject_id)->max('sequence_no') ?? $this->sequence_no;

        $newMod = $this->replicate();
        $newMod->title = $this->title . ' (Copy)';
        $newMod->sequence_no = $maxSeq + 1;
        $newMod->save();

        foreach ($this->learningResources as $lr) {
            $newLr = $lr->replicate();
            $newLr->module_id = $newMod->id;
            $newLr->save();
        }

        return $newMod;
    }
}
