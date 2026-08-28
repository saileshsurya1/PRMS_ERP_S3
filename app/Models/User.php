<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'password',
        'role',
        'status',
        'profile_photo_path',
        'department',
        'employee_code',
        'active',
        'joined_date',
        'customer_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
        'joined_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function ($user) {
            if (!empty($user->first_name) || !empty($user->last_name)) {
                $user->name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            }
        });
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo_path && (Storage::disk('public')->exists($this->profile_photo_path) || file_exists(public_path('storage/' . $this->profile_photo_path)))) {
            return asset('storage/' . $this->profile_photo_path);
        }

        return asset('assets/img/avatars/1.png');
    }

    public function getNameAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }

        $combined = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        return $combined !== '' ? $combined : 'User';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'owner';
    }

    public function isSalesEngineer(): bool
    {
        return $this->role === 'sales_engineer';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active' && ($this->active ?? true);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function todos()
    {
        return $this->hasMany(Todo::class, 'user_id');
    }

    public function assignedTodos()
    {
        return $this->hasMany(Todo::class, 'assigned_to_id');
    }
}
