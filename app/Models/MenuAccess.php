<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuAccess extends Model
{
    protected $fillable = ['menu_item_id', 'subject_type', 'subject_value'];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}