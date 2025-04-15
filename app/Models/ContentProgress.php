<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'module_content_id',
        'status',
        'completed_at',
        'score',
        'attempts',
        'last_attempt_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'score' => 'float'
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function moduleContent()
    {
        return $this->belongsTo(ModuleContent::class);
    }
}
