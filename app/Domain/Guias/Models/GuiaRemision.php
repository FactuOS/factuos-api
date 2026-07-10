<?php

declare(strict_types=1);

namespace App\Domain\Guias\Models;

use App\Domain\Comprobantes\Models\Comprobante;
use App\Domain\Comprobantes\Models\ComprobanteTipo;
use App\Domain\Empresa\Models\Empresa;
use App\Domain\Seguridad\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuiaRemision extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'guias_remision';

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

    protected $casts = [
        'empresa_id' => 'integer',
        'user_id' => 'integer',
        'comprobante_tipo_id' => 'integer',
        'numero' => 'integer',
        'fecha_emision' => 'date',
        'peso_bruto' => 'decimal:3',
        'numero_bultos' => 'integer',
        'comprobante_id_relacionado' => 'integer',
        'datos_extra' => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipo(): BelongsTo
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
