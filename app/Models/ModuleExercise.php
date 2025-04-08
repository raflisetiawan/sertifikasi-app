<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleExercise extends Model
{
    use HasFactory;
    protected $fillable = [
        'module_id',
        'description',
        'order'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
