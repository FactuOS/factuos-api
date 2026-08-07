<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'is_active',
        'jerarquia',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'jerarquia' => 'integer',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'role_modules');
    }

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'role_permisos');
    }
}
