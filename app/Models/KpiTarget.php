<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiTarget extends Model
{
    protected $fillable = ['sales_engineer_id', 'kpi_code', 'kpi_name', 'period_type', 'target_value', 'target_unit', 'weight_percentage', 'valid_from', 'valid_to', 'created_by'];
    protected $casts = ['valid_from' => 'date', 'valid_to' => 'date'];
    public function salesEngineer() { return $this->belongsTo(User::class, 'sales_engineer_id'); }
}
