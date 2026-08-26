<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incentive extends Model
{
    protected $fillable = ['sales_engineer_id', 'period_month', 'achievement_percentage', 'slab', 'base_incentive_amount', 'multiplier', 'final_incentive_amount', 'manager_status', 'manager_comments', 'approved_by', 'approved_at'];
    protected $casts = ['period_month' => 'date', 'approved_at' => 'datetime'];
    public function salesEngineer() { return $this->belongsTo(User::class, 'sales_engineer_id'); }
}
