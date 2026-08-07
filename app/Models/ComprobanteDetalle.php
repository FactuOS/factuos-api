<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteDetalle extends Model
{
    protected $fillable = [
        'comprobante_id',
        'item_id',
        'descripcion',
        'cantidad',
        'unidad_medida',
        'valor_unitario',
        'precio_unitario',
        'descuento',
        'subtotal',
        'igv',
        'total',
        'afectacion_igv',
        'porcentaje_igv',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3',
            'valor_unitario' => 'integer',
            'precio_unitario' => 'integer',
            'descuento' => 'integer',
            'subtotal' => 'integer',
            'igv' => 'integer',
            'total' => 'integer',
            'porcentaje_igv' => 'decimal:2',
        ];
    }

    public function comprobante(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}