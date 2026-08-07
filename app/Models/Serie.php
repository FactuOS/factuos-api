<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Serie extends Model
{
    protected $fillable = [
        'empresa_id',
        'comprobante_tipo_id',
        'serie',
        'correlativo_actual',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'correlativo_actual' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function comprobanteTipo(): BelongsTo
    {
        return $this->belongsTo(ComprobanteTipo::class, 'comprobante_tipo_id');
    }

    public function nextCorrelativo(): int
    {
        return $this->correlativo_actual + 1;
    }
}
