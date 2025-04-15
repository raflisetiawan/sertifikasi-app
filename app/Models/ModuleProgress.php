<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'module_id',
        'status',
        'progress_percentage',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'progress_percentage' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function contentProgress()
    {
        return $this->hasManyThrough(
            ContentProgress::class,
            ModuleContent::class,
            'module_id',
            'module_content_id',
            'module_id',
            'id'
        );
    }
}
