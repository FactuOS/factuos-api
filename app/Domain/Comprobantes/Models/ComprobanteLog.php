<?php

declare(strict_types=1);

namespace App\Domain\Comprobantes\Models;

use App\Domain\Seguridad\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'comprobante_logs';

    protected $fillable = [
        'comprobante_id',
        'tipo_documento',
        'estado_anterior',
        'estado_nuevo',
        'user_id',
        'detalle',
        'created_at',
    ];

    protected $casts = [
        'comprobante_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
