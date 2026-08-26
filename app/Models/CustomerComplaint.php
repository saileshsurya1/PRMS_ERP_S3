<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerComplaint extends Model
{
    protected $fillable = ['customer_id', 'sales_engineer_id', 'reported_date', 'subject', 'description', 'status', 'resolution'];
    protected $casts = ['reported_date' => 'date'];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function salesEngineer() { return $this->belongsTo(User::class, 'sales_engineer_id'); }
}
