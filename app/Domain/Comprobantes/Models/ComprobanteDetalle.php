<?php

declare(strict_types=1);

namespace App\Domain\Comprobantes\Models;

use App\Domain\Items\Models\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteDetalle extends Model
{
    use HasFactory;

    protected $table = 'comprobante_detalles';

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

    protected $casts = [
        'comprobante_id' => 'integer',
        'item_id' => 'integer',
        'cantidad' => 'decimal:3',
        'valor_unitario' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2',
        'porcentaje_igv' => 'decimal:2',
    ];

    public function comprobante(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
