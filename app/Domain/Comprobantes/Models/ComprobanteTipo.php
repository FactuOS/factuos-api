<?php

declare(strict_types=1);

namespace App\Domain\Comprobantes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComprobanteTipo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comprobante_tipos';

    protected $fillable = [
        'codigo',
        'nombre',
        'abreviacion',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function series(): HasMany
    {
        return $this->hasMany(Serie::class, 'comprobante_tipo_id');
    }

    public function comprobantes(): HasMany
    {
        return $this->hasMany(Comprobante::class, 'comprobante_tipo_id');
    }
}
