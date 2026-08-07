<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use SoftDeletes;

    public const TIPOS_DOCUMENTO = ['RUC', 'DNI', 'CE', 'Pasaporte'];

    protected $fillable = [
        'user_id',
        'tipo_documento',
        'numero_documento',
        'razon_social',
        'nombre_comercial',
        'direccion',
        'ubigeo',
        'departamento',
        'provincia',
        'distrito',
        'email',
        'telefono',
        'logo',
        'representante_legal',
        'dni_representante',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'empresa_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'empresa_id');
    }

    public function sunatConfig(): HasOne
    {
        return $this->hasOne(EmpresaSunatConfig::class, 'empresa_id');
    }

    public function series(): HasMany
    {
        return $this->hasMany(Serie::class, 'empresa_id');
    }

    public function comprobantes(): HasMany
    {
        return $this->hasMany(Comprobante::class, 'empresa_id');
    }
}
