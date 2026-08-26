<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiActual extends Model
{
    protected $fillable = ['sales_engineer_id', 'kpi_target_id', 'period_start', 'period_end', 'actual_value', 'achievement_percentage', 'weighted_score', 'calculation_status', 'calculated_at', 'approved_by'];
    protected $casts = ['period_start' => 'date', 'period_end' => 'date', 'calculated_at' => 'datetime'];
    public function target() { return $this->belongsTo(KpiTarget::class, 'kpi_target_id'); }
    public function salesEngineer() { return $this->belongsTo(User::class, 'sales_engineer_id'); }
}
