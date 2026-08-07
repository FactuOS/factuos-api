<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteLog extends Model
{
    public const TIPOS_DOCUMENTO = ['comprobante', 'guia_remision'];

    public $timestamps = false;

    protected $fillable = [
        'comprobante_id',
        'tipo_documento',
        'estado_anterior',
        'estado_nuevo',
        'user_id',
        'detalle',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function comprobante(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}