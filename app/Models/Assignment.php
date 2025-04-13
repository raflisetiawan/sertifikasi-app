<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'instructions',
        'submission_requirements',
        'due_date',
        'max_file_size_mb',
        'allowed_file_types'
    ];

    protected $casts = [
        'submission_requirements' => 'array',
        'due_date' => 'datetime'
    ];

    public function moduleContent()
    {
        return $this->morphOne(ModuleContent::class, 'content');
    }
}
