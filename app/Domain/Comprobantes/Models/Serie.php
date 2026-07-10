<?php

declare(strict_types=1);

namespace App\Domain\Comprobantes\Models;

use App\Domain\Empresa\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Serie extends Model
{
    use HasFactory;

    protected $table = 'series';

    protected $fillable = [
        'empresa_id',
        'comprobante_tipo_id',
        'serie',
        'correlativo_actual',
        'is_active',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'comprobante_tipo_id' => 'integer',
        'correlativo_actual' => 'integer',
        'is_active' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(ComprobanteTipo::class, 'comprobante_tipo_id');
    }
}
