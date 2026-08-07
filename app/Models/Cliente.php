<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    public const TIPOS_DOCUMENTO = ['DNI', 'RUC', 'CE', 'Pasaporte'];

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

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_clientes');
    }
}
