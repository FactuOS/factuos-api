<?php

declare(strict_types=1);

namespace App\Domain\Seguridad\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permiso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permisos';

    protected $fillable = [
        'modulo_id',
        'nombre',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'modulo_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'modulo_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permisos', 'permiso_id', 'role_id');
    }
}
