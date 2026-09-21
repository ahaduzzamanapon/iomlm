<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionCircularBatch extends Model
{
    use HasFactory;

    protected $table = 'admission_circular_batches';

    protected $fillable = [
        'admission_circular_id',
        'course_id',
        'batch_id',
        'campus',
        'is_online_admission_enabled',
    ];

    protected $casts = [
        'is_online_admission_enabled' => 'boolean',
    ];

    public function circular()
    {
        return $this->belongsTo(AdmissionCircular::class, 'admission_circular_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
