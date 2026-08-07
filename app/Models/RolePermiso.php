<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermiso extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'permiso_id',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permiso()
    {
        return $this->belongsTo(Permiso::class);
    }
}
