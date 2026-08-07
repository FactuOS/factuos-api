<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuiaRemisionItem extends Model
{
    protected $fillable = [
        'guia_remision_id',
        'item_id',
        'descripcion',
        'cantidad',
        'unidad_medida',
        'codigo',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3',
        ];
    }

    public function guiaRemision(): BelongsTo
    {
        return $this->belongsTo(GuiaRemision::class, 'guia_remision_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}