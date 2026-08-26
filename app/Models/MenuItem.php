<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = ['label', 'route', 'icon', 'parent_id', 'sort_order', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function accesses()
    {
        return $this->hasMany(MenuAccess::class);
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereHas('accesses', function (Builder $access) use ($user) {
            $access->where(function (Builder $subject) use ($user) {
                $subject->where(fn (Builder $q) => $q->where('subject_type', 'role')->where('subject_value', $user->role))
                    ->orWhere(fn (Builder $q) => $q->where('subject_type', 'department')->where('subject_value', $user->department ?? ''))
                    ->orWhere(fn (Builder $q) => $q->where('subject_type', 'user')->where('subject_value', (string) $user->id));
            });
        });
    }
}