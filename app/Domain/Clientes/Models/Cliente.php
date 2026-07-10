<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Models;

use App\Domain\Empresa\Models\Empresa;
use App\Domain\Seguridad\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'empresa_id',
        'tipo_documento',
        'numero_documento',
        'razon_social',
        'nombre_comercial',
        'email',
        'telefono',
        'direccion',
        'is_active',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_clientes', 'cliente_id', 'user_id');
    }
}
