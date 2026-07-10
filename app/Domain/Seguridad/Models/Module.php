<?php

declare(strict_types=1);

namespace App\Domain\Seguridad\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'modules';

    protected $fillable = [
        'nombre',
        'icon',
        'rute',
        'orden',
        'is_active',
    ];

    protected $casts = [
        'orden' => 'integer',
        'is_active' => 'boolean',
    ];

    public function permisos(): HasMany
    {
        return $this->hasMany(Permiso::class, 'modulo_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_modules', 'module_id', 'role_id');
    }
}
