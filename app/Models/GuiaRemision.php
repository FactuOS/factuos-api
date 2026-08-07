<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuiaRemision extends Model
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

    public const MODALIDADES_TRANSPORTE = ['01' => 'Público', '02' => 'Privado'];

    protected $fillable = [
        'empresa_id',
        'user_id',
        'comprobante_tipo_id',
        'serie',
        'numero',
        'fecha_emision',
        'hora_emision',
        'motivo_traslado',
        'motivo_descripcion',
        'peso_bruto',
        'unidad_peso',
        'numero_bultos',
        'partida_ubigeo',
        'partida_direccion',
        'llegada_ubigeo',
        'llegada_direccion',
        'destinatario_tipo_documento',
        'destinatario_numero_documento',
        'destinatario_razon_social',
        'modalidad_transporte',
        'transportista_tipo_documento',
        'transportista_numero_documento',
        'transportista_razon_social',
        'conductor_tipo_documento',
        'conductor_numero_documento',
        'conductor_nombres',
        'conductor_apellidos',
        'conductor_licencia',
        'vehiculo_placa',
        'comprobante_id_relacionado',
        'documento_referencia',
        'estado',
        'hash_cdr',
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
            'peso_bruto' => 'decimal:3',
            'numero_bultos' => 'integer',
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

    public function comprobanteRelacionado(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id_relacionado');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GuiaRemisionItem::class, 'guia_remision_id');
    }
}