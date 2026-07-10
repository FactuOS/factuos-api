<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Empresa\Models\Empresa;
use App\Domain\Seguridad\Models\Role;
use App\Domain\Seguridad\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Empresa Demo de Prueba
        $empresa = Empresa::updateOrCreate(
            ['numero_documento' => '20123456789'],
            [
                'tipo_documento' => 'RUC',
                'razon_social' => 'FACTUOS SOFTWARE S.A.C.',
                'nombre_comercial' => 'FactuOS Perú',
                'direccion' => 'Av. Javier Prado Este 1234',
                'ubigeo' => '150101',
                'departamento' => 'Lima',
                'provincia' => 'Lima',
                'distrito' => 'Lima',
                'email' => 'contacto@factuos.pe',
                'telefono' => '987654321',
                'is_active' => true,
            ]
        );

        // 2. Crear Usuario Admin Demo
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@factuos.pe'],
            [
                'name' => 'Administrador FactuOS',
                'password' => Hash::make('password123'),
                'empresa_id' => $empresa->id,
                'is_active' => true,
            ]
        );

        // Asignar user_id en la empresa
        $empresa->update(['user_id' => $adminUser->id]);

        // Asignar Rol de Admin Empresa
        $roleAdmin = Role::where('nombre', 'Admin Empresa')->first();
        if ($roleAdmin) {
            $adminUser->roles()->syncWithoutDetaching([$roleAdmin->id]);
        }
    }
}
