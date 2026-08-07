<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comprobante extends Model
{
    use SoftDeletes;

    public const ESTADOS = [
        'borrador' => 'borrador',
        'emitido' => 'emitido',
        'enviado' => 'enviado',
        'aceptado' => 'aceptado',
        'rechazado' => 'rechazado',
        'anulado' => 'anulado',
    ];

    public const FORMAS_PAGO = ['CONTADO', 'CREDITO'];

    protected $fillable = [
        'empresa_id',
        'user_id',
        'comprobante_tipo_id',
        'serie',
        'numero',
        'fecha_emision',
        'hora_emision',
        'fecha_vencimiento',
        'cliente_id',
        'moneda',
        'tipo_operacion',
        'subtotal',
        'igv',
        'total',
        'porcentaje_igv',
        'descuento_total',
        'forma_pago',
        'estado',
        'comprobante_id_referenciado',
        'serie_referenciada',
        'numero_referenciado',
        'motivo_codigo',
        'motivo_descripcion',
        'hash_cdr',
        'hash_ubl',
        'xml_path',
        'cdr_xml_path',
        'ticket',
        'sunat_code',
        'sunat_description',
        'observacion',
        'datos_extra',
    ];

    protected function casts(): array
    {
        return [
            'numero' => 'integer',
            'fecha_emision' => 'date',
            'hora_emision' => 'datetime:H:i:s',
            'fecha_vencimiento' => 'date',
            'subtotal' => 'integer',
            'igv' => 'integer',
            'total' => 'integer',
            'porcentaje_igv' => 'decimal:2',
            'descuento_total' => 'integer',
            'datos_extra' => 'array',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comprobanteTipo(): BelongsTo
    {
        return $this->belongsTo(ComprobanteTipo::class, 'comprobante_tipo_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function comprobanteReferenciado(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id_referenciado');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(ComprobanteDetalle::class, 'comprobante_id');
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(ComprobanteCuota::class, 'comprobante_id');
    }

    public function guias(): HasMany
    {
        return $this->hasMany(GuiaRemision::class, 'comprobante_id_relacionado');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ComprobanteLog::class, 'comprobante_id');
    }

    public function esFactura(): bool
    {
        return $this->comprobanteTipo->codigo === ComprobanteTipo::TIPOS['factura'];
    }

    public function esBoleta(): bool
    {
        return $this->comprobanteTipo->codigo === ComprobanteTipo::TIPOS['boleta'];
    }
}
