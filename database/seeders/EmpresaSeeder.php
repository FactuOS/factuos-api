<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@factuos.com')->first();
        if (! $admin) {
            return;
        }

        $empresa = DB::table('empresas')->updateOrInsert(
            ['tipo_documento' => 'RUC', 'numero_documento' => '20123456789'],
            [
                'user_id' => $admin->id,
                'razon_social' => 'FactuOS Demo S.A.C.',
                'nombre_comercial' => 'FactuOS',
                'direccion' => 'Av. Ejemplo 456, Lima',
                'ubigeo' => '150101',
                'departamento' => 'LIMA',
                'provincia' => 'LIMA',
                'distrito' => 'LIMA',
                'email' => 'empresa@factuos.com',
                'telefono' => '01-5550101',
                'representante_legal' => 'Administrador',
                'dni_representante' => '43123456',
                'is_active' => true,
            ]
        );

        $empresaId = DB::table('empresas')
            ->where('tipo_documento', 'RUC')
            ->where('numero_documento', '20123456789')
            ->value('id');

        if (! $empresaId) {
            return;
        }

        $usuarioRole = Role::where('jerarquia', 2)->first();

        $usuario = User::updateOrCreate(
            ['email' => 'usuario@factuos.com'],
            [
                'name' => 'Usuario Demo',
                'password' => Hash::make('123456'),
                'is_active' => true,
                'empresa_id' => $empresaId,
            ]
        );

        if ($usuarioRole && ! $usuario->roles()->where('role_id', $usuarioRole->id)->exists()) {
            $usuario->roles()->attach($usuarioRole);
        }
    }
}
