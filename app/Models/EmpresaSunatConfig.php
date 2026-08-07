<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaSunatConfig extends Model
{
    protected $fillable = [
        'empresa_id',
        'sol_user',
        'sol_pass',
        'certificate_path',
        'certificate_password',
        'production',
    ];

    protected $hidden = [
        'sol_pass',
        'certificate_password',
    ];

    protected function casts(): array
    {
        return [
            'production' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}