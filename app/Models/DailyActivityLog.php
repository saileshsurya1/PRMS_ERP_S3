<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyActivityLog extends Model
{
    protected $fillable = ['sales_engineer_id', 'activity_date', 'customer_calls', 'follow_up_calls', 'customer_visits', 'online_meetings', 'rfqs_received', 'quotations_submitted', 'crm_updated', 'notes'];
    protected $casts = ['activity_date' => 'date', 'crm_updated' => 'boolean'];
    public function salesEngineer() { return $this->belongsTo(User::class, 'sales_engineer_id'); }
}
