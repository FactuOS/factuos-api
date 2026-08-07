<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteCuota extends Model
{
    protected $fillable = [
        'comprobante_id',
        'numero',
        'monto',
        'fecha_pago',
    ];

    protected function casts(): array
    {
        return [
            'numero' => 'integer',
            'monto' => 'integer',
            'fecha_pago' => 'date',
        ];
    }

    public function comprobante(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }
}