<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModuleContent extends Model
{
    use HasFactory;
    protected $with = ['content'];

    protected $fillable = [
        'module_id',
        'title',
        'content_type',
        'content_id',
        'order',
        'is_required',
        'minimum_duration_seconds',
        'completion_rules'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'completion_rules' => 'array'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function content(): MorphTo
    {
        return $this->morphTo();
    }
}
