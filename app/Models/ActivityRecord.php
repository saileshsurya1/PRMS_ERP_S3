<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityRecord extends Model
{
    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'details'];
    protected $casts = ['details' => 'array'];
    public function user() { return $this->belongsTo(User::class); }
}
