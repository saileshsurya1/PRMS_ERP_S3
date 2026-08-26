<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rfq extends Model
{
    protected $table = 'rfqs';
    protected $fillable = ['customer_id', 'sales_engineer_id', 'rfq_received_date', 'rfq_number', 'rfq_description', 'quantity', 'lead_time_days', 'action_open_date', 'quotation_submission_target_date', 'current_status', 'order_cancelled', 'order_cancelled_amount', 'order_cancel_reason', 'total_quoted_price', 'total_awarded_price', 'total_invoiced_price', 'advance_received', 'pending_amount_due_date', 'payment_pending_reason'];
    protected $casts = ['rfq_received_date' => 'date', 'action_open_date' => 'date', 'quotation_submission_target_date' => 'date', 'pending_amount_due_date' => 'date', 'order_cancelled' => 'boolean'];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function salesEngineer() { return $this->belongsTo(User::class, 'sales_engineer_id'); }
    public function quotations() { return $this->hasMany(Quotation::class); }
    public function payments() { return $this->hasMany(PaymentTransaction::class); }
    public function getInvoicePendingPriceAttribute(): float { return max((float) $this->total_awarded_price - (float) $this->total_invoiced_price, 0); }
    public function getPendingAmountAttribute(): float { return max((float) $this->total_invoiced_price - (float) $this->payments()->sum('amount'), 0); }
}
