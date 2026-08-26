<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = ['rfq_id', 'received_date', 'amount', 'currency', 'notes'];
    protected $casts = ['received_date' => 'date'];
    public function rfq() { return $this->belongsTo(Rfq::class); }
}
