<?php

declare(strict_types=1);

namespace App\Domain\Comprobantes\Models;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Empresa\Models\Empresa;
use App\Domain\Seguridad\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comprobante extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comprobantes';

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

    protected $casts = [
        'empresa_id' => 'integer',
        'user_id' => 'integer',
        'comprobante_tipo_id' => 'integer',
        'numero' => 'integer',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'cliente_id' => 'integer',
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2',
        'porcentaje_igv' => 'decimal:2',
        'descuento_total' => 'decimal:2',
        'comprobante_id_referenciado' => 'integer',
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

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function referenciado(): BelongsTo
    {
        return $this->belongsTo(self::class, 'comprobante_id_referenciado');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(ComprobanteDetalle::class, 'comprobante_id');
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(ComprobanteCuota::class, 'comprobante_id');
    }
}
