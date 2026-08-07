<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    public const TIPOS = ['servicio', 'producto'];

    public const UNIDADES_MEDIDA = ['NIU', 'ZZ', 'KGM', 'MTR', 'LTR'];

    public const AFECTACIONES_IGV = [
        '10' => 'Gravado',
        '20' => 'Exonerado',
        '30' => 'Inafecto',
    ];

    protected $fillable = [
        'empresa_id',
        'tipo',
        'codigo',
        'nombre',
        'descripcion',
        'unidad_medida',
        'precio',
        'moneda',
        'afectacion_igv',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function comprobanteDetalles(): HasMany
    {
        return $this->hasMany(ComprobanteDetalle::class, 'item_id');
    }

    public function esProducto(): bool
    {
        return $this->tipo === 'producto';
    }

    public function esServicio(): bool
    {
        return $this->tipo === 'servicio';
    }

    public function porcentajeIgv(): float
    {
        return $this->afectacion_igv === '10' ? 18 : 0;
    }
}