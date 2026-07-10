<?php

declare(strict_types=1);

namespace App\Domain\Items\Models;

use App\Domain\Comprobantes\Models\ComprobanteDetalle;
use App\Domain\Empresa\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'items';

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

    protected $casts = [
        'empresa_id' => 'integer',
        'precio' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function comprobanteDetalles(): HasMany
    {
        return $this->hasMany(ComprobanteDetalle::class, 'item_id');
    }
}
