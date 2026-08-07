<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'icon',
        'orden',
        'rute',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_modules');
    }

    public function permisos(): HasMany
    {
        return $this->hasMany(Permiso::class, 'modulo_id');
    }
}
