<?php

declare(strict_types=1);

namespace App\Domain\Guias\Models;

use App\Domain\Items\Models\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuiaRemisionItem extends Model
{
    use HasFactory;

    protected $table = 'guia_remision_items';

    protected $fillable = [
        'guia_remision_id',
        'item_id',
        'descripcion',
        'cantidad',
        'unidad_medida',
        'codigo',
    ];

    protected $casts = [
        'guia_remision_id' => 'integer',
        'item_id' => 'integer',
        'cantidad' => 'decimal:3',
    ];

    public function guiaRemision(): BelongsTo
    {
        return $this->belongsTo(GuiaRemision::class, 'guia_remision_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
