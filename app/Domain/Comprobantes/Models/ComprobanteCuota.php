<?php

declare(strict_types=1);

namespace App\Domain\Comprobantes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteCuota extends Model
{
    use HasFactory;

    protected $table = 'comprobante_cuotas';

    protected $fillable = [
        'comprobante_id',
        'numero',
        'monto',
        'fecha_pago',
    ];

    protected $casts = [
        'comprobante_id' => 'integer',
        'numero' => 'integer',
        'monto' => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    public function comprobante(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }
}
