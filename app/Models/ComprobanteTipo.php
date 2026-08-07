<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComprobanteTipo extends Model
{
    use SoftDeletes;

    public const TIPOS = [
        'factura' => '01',
        'recibo_honorarios' => '02',
        'boleta' => '03',
        'liquidacion_compra' => '04',
        'nota_credito' => '07',
        'nota_debito' => '08',
        'guia_remision' => '09',
        'comprobante_retencion' => '20',
        'guia_bienes_fiscalizados' => '31',
        'comprobante_percepcion' => '40',
    ];

    protected $fillable = [
        'codigo',
        'nombre',
        'abreviacion',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function series(): HasMany
    {
        return $this->hasMany(Serie::class, 'comprobante_tipo_id');
    }

    public function comprobantes(): HasMany
    {
        return $this->hasMany(Comprobante::class, 'comprobante_tipo_id');
    }
}
