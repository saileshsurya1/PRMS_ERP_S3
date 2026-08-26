<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = ['rfq_id', 'quotation_number', 'quotation_date', 'quoted_date', 'submission_target_date', 'actual_submitted_date', 'quoted_price', 'awarded_price', 'status', 'commercial_accuracy', 'loss_reason', 'notes'];
    protected $casts = ['quotation_date' => 'date', 'quoted_date' => 'date', 'submission_target_date' => 'date', 'actual_submitted_date' => 'date', 'commercial_accuracy' => 'boolean'];
    public function rfq() { return $this->belongsTo(Rfq::class); }
    public function getSubmissionLeadTimeHoursAttribute(): ?float { return $this->actual_submitted_date && $this->rfq?->rfq_received_date ? round($this->rfq->rfq_received_date->diffInHours($this->actual_submitted_date), 2) : null; }
}
