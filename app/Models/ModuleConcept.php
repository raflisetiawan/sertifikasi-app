<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleConcept extends Model
{
    use HasFactory;
    protected $fillable = [
        'module_id',
        'title',
        'order'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
