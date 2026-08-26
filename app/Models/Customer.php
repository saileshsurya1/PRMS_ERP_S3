<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Customer extends Model
{
    protected $fillable = [
        'customer_code',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'industry',
        'customer_type',
        'assigned_sales_engineer_id',
        'sales_engineer_id',
        'photo',
        'first_contact_date',
        'last_contact_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'first_contact_date' => 'date',
        'last_contact_date' => 'date',
    ];

    protected $appends = [
        'photo_url',
    ];

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && (Storage::disk('public')->exists($this->photo) || file_exists(public_path('storage/' . $this->photo)))) {
            return asset('storage/' . $this->photo);
        }

        return asset('assets/img/avatars/1.png');
    }

    public function salesEngineer()
    {
        return $this->belongsTo(User::class, 'sales_engineer_id')->withDefault(function ($user, $customer) {
            return $customer->assignedSalesEngineer;
        });
    }

    public function assignedSalesEngineer()
    {
        return $this->belongsTo(User::class, 'assigned_sales_engineer_id');
    }

    public function rfqs()
    {
        return $this->hasMany(Rfq::class);
    }

    public function complaints()
    {
        return $this->hasMany(CustomerComplaint::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
