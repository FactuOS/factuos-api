<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Seguridad\Models\Module;
use App\Domain\Seguridad\Models\Permiso;
use App\Domain\Seguridad\Models\Role;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Roles Principales
        $superAdminRole = Role::updateOrCreate(
            ['nombre' => 'Super Admin'],
            ['jerarquia' => 1, 'is_active' => true]
        );

        $adminEmpresaRole = Role::updateOrCreate(
            ['nombre' => 'Admin Empresa'],
            ['jerarquia' => 2, 'is_active' => true]
        );

        $vendedorRole = Role::updateOrCreate(
            ['nombre' => 'Vendedor'],
            ['jerarquia' => 3, 'is_active' => true]
        );

        // 2. Crear Módulos y Permisos
        $modulosEstructura = [
            [
                'nombre' => 'Dashboard',
                'icon' => 'pi pi-home',
                'rute' => '/dashboard',
                'orden' => 1,
                'permisos' => [
                    ['nombre' => 'Ver Dashboard', 'slug' => 'dashboard.ver'],
                ],
            ],
            [
                'nombre' => 'Empresa',
                'icon' => 'pi pi-building',
                'rute' => '/empresa',
                'orden' => 2,
                'permisos' => [
                    ['nombre' => 'Ver Empresa', 'slug' => 'empresa.ver'],
                    ['nombre' => 'Editar Empresa', 'slug' => 'empresa.editar'],
                    ['nombre' => 'Configurar SUNAT', 'slug' => 'empresa.sunat'],
                ],
            ],
            [
                'nombre' => 'Clientes',
                'icon' => 'pi pi-users',
                'rute' => '/clientes',
                'orden' => 3,
                'permisos' => [
                    ['nombre' => 'Ver Clientes', 'slug' => 'clientes.ver'],
                    ['nombre' => 'Crear Clientes', 'slug' => 'clientes.crear'],
                    ['nombre' => 'Editar Clientes', 'slug' => 'clientes.editar'],
                    ['nombre' => 'Eliminar Clientes', 'slug' => 'clientes.eliminar'],
                ],
            ],
            [
                'nombre' => 'Servicios y Productos',
                'icon' => 'pi pi-box',
                'rute' => '/items',
                'orden' => 4,
                'permisos' => [
                    ['nombre' => 'Ver Items', 'slug' => 'items.ver'],
                    ['nombre' => 'Crear Items', 'slug' => 'items.crear'],
                    ['nombre' => 'Editar Items', 'slug' => 'items.editar'],
                    ['nombre' => 'Eliminar Items', 'slug' => 'items.eliminar'],
                ],
            ],
            [
                'nombre' => 'Comprobantes',
                'icon' => 'pi pi-file',
                'rute' => '/comprobantes',
                'orden' => 5,
                'permisos' => [
                    ['nombre' => 'Ver Comprobantes', 'slug' => 'comprobantes.ver'],
                    ['nombre' => 'Emitir Comprobantes', 'slug' => 'comprobantes.emitir'],
                    ['nombre' => 'Anular Comprobantes', 'slug' => 'comprobantes.anular'],
                    ['nombre' => 'Descargar PDF/XML', 'slug' => 'comprobantes.descargar'],
                ],
            ],
            [
                'nombre' => 'Guías de Remisión',
                'icon' => 'pi pi-truck',
                'rute' => '/guias',
                'orden' => 6,
                'permisos' => [
                    ['nombre' => 'Ver Guías', 'slug' => 'guias.ver'],
                    ['nombre' => 'Emitir Guías', 'slug' => 'guias.emitir'],
                ],
            ],
        ];

        $todosLosPermisosIds = [];
        $todosLosModulosIds = [];

        foreach ($modulosEstructura as $modData) {
            $modulo = Module::updateOrCreate(
                ['nombre' => $modData['nombre']],
                [
                    'icon' => $modData['icon'],
                    'rute' => $modData['rute'],
                    'orden' => $modData['orden'],
                    'is_active' => true,
                ]
            );

            $todosLosModulosIds[] = $modulo->id;

            foreach ($modData['permisos'] as $permData) {
                $permiso = Permiso::updateOrCreate(
                    ['slug' => $permData['slug']],
                    [
                        'modulo_id' => $modulo->id,
                        'nombre' => $permData['nombre'],
                        'is_active' => true,
                    ]
                );

                $todosLosPermisosIds[] = $permiso->id;
            }
        }

        // 3. Asignar Todos los Permisos y Módulos al Super Admin y Admin Empresa
        $superAdminRole->modules()->sync($todosLosModulosIds);
        $superAdminRole->permisos()->sync($todosLosPermisosIds);

        $adminEmpresaRole->modules()->sync($todosLosModulosIds);
        $adminEmpresaRole->permisos()->sync($todosLosPermisosIds);

        // 4. Asignar Módulos/Permisos limitados al Vendedor
        $permisosVendedorSlugs = [
            'dashboard.ver',
            'clientes.ver',
            'clientes.crear',
            'items.ver',
            'comprobantes.ver',
            'comprobantes.emitir',
            'comprobantes.descargar',
        ];

        $permisosVendedorIds = Permiso::whereIn('slug', $permisosVendedorSlugs)->pluck('id')->toArray();
        $vendedorRole->permisos()->sync($permisosVendedorIds);
    }
}
