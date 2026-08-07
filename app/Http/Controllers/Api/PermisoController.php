<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Permiso;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PermisoController extends Controller
{
    private function authorizeModule(Request $request, string $slug): bool
    {
        return $request->user()->canOnModule('permisos', $slug);
    }

    public function config(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'read')) {
            return response()->json(['message' => 'No tienes permiso para ver permisos.'], 403);
        }

        $roles = Role::where('is_active', true)
            ->orderBy('jerarquia')
            ->get(['id', 'nombre']);

        $modules = Module::where('is_active', true)
            ->with('permisos')
            ->orderBy('orden')
            ->get(['id', 'nombre', 'icon', 'rute', 'orden']);

        $moduleData = $modules->map(function (Module $module) {
            $permisoIds = $module->permisos
                ->mapWithKeys(fn (Permiso $p) => [$p->slug => $p->id]);

            return [
                'id' => $module->id,
                'nombre' => $module->nombre,
                'icon' => $module->icon,
                'rute' => $module->rute,
                'orden' => $module->orden,
                'permiso_ids' => $permisoIds,
            ];
        });

        $assignments = [];
        foreach ($roles as $role) {
            $assignments[$role->id] = [
                'module_ids' => $role->modules()->pluck('modules.id')->map(fn ($id) => (int) $id)->values(),
                'permiso_ids' => $role->permisos()->pluck('permisos.id')->map(fn ($id) => (int) $id)->values(),
            ];
        }

        return response()->json([
            'roles' => $roles,
            'modules' => $moduleData,
            'assignments' => $assignments,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'edit')) {
            return response()->json(['message' => 'No tienes permiso para editar permisos.'], 403);
        }

        $validated = $request->validate([
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'module_ids' => ['required', 'array'],
            'module_ids.*' => ['integer', Rule::exists('modules', 'id')],
            'permiso_ids' => ['required', 'array'],
            'permiso_ids.*' => ['integer', Rule::exists('permisos', 'id')],
        ]);

        $role = Role::findOrFail($validated['role_id']);

        $moduleIds = collect($validated['module_ids'])->map(fn ($id) => (int) $id)->unique();

        $validPermisoIds = Permiso::whereIn('id', $validated['permiso_ids'])
            ->whereIn('modulo_id', $moduleIds)
            ->pluck('id');

        if ($role->jerarquia === 1) {
            $protectedModuleIds = Module::whereIn('rute', ['usuarios', 'permisos', 'roles', 'modules'])->pluck('id');
            $protectedPermisoIds = Permiso::whereIn('modulo_id', $protectedModuleIds)->pluck('id');

            $moduleIds = $moduleIds->merge($protectedModuleIds)->unique();
            $validPermisoIds = $validPermisoIds->merge($protectedPermisoIds)->unique();
        }

        DB::transaction(function () use ($role, $moduleIds, $validPermisoIds) {
            $role->modules()->sync($moduleIds);
            $role->permisos()->sync($validPermisoIds);
        });

        return response()->json(['message' => 'Permisos actualizados correctamente.']);
    }
}
