<?php

declare(strict_types=1);

namespace App\Domain\Empresa\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaSunatConfig extends Model
{
    use HasFactory;

    protected $table = 'empresa_sunat_configs';

    protected $fillable = [
        'empresa_id',
        'sol_user',
        'sol_pass',
        'certificate_path',
        'certificate_password',
        'production',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'production' => 'boolean',
        'sol_pass' => 'encrypted',
        'certificate_password' => 'encrypted',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
