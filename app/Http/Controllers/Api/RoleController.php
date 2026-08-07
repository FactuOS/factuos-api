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

class RoleController extends Controller
{
    private const PROTECTED_MODULE_RUTES = ['usuarios', 'permisos', 'roles', 'modules'];

    private function authorizeModule(Request $request, string $slug): bool
    {
        return $request->user()->canOnModule('roles', $slug);
    }

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'No tienes permiso.'], 403);
        }

        $roles = Role::where('is_active', true)
            ->orderBy('jerarquia')
            ->get(['id', 'nombre']);

        return response()->json(['roles' => $roles]);
    }

    public function all(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'read')) {
            return response()->json(['message' => 'No tienes permiso para ver roles.'], 403);
        }

        $roles = Role::query()
            ->withCount('users')
            ->orderBy('jerarquia')
            ->orderBy('id')
            ->paginate(10);

        $data = collect($roles->items())->map(fn (Role $role) => $this->roleData($role));

        return response()->json([
            'roles' => $data,
            'pagination' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'total' => $roles->total(),
                'per_page' => $roles->perPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'create')) {
            return response()->json(['message' => 'No tienes permiso para crear roles.'], 403);
        }

        $validated = $this->validateRole($request);

        $role = DB::transaction(function () use ($validated) {
            $role = Role::create([
                'nombre' => $validated['nombre'],
                'jerarquia' => $validated['jerarquia'] ?? 1,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $this->syncAccess($role, $validated['module_ids'] ?? [], $validated['permiso_ids'] ?? []);

            return $role;
        });

        return response()->json(['message' => 'Rol creado correctamente.', 'rol' => $this->roleData($role)], 201);
    }

    public function show(Request $request, Role $role): JsonResponse
    {
        if (! $this->authorizeModule($request, 'read')) {
            return response()->json(['message' => 'No tienes permiso para ver roles.'], 403);
        }

        return response()->json(['rol' => $this->roleData($role)]);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        if (! $this->authorizeModule($request, 'edit')) {
            return response()->json(['message' => 'No tienes permiso para editar roles.'], 403);
        }

        if ($role->jerarquia === 1 && $request->has('is_active') && ! $request->boolean('is_active')) {
            return response()->json(['message' => 'El rol Admin no puede desactivarse.'], 422);
        }

        $validated = $this->validateRole($request, $role);

        DB::transaction(function () use ($role, $validated) {
            $role->update([
                'nombre' => $validated['nombre'],
                'jerarquia' => $validated['jerarquia'] ?? $role->jerarquia,
                'is_active' => $validated['is_active'] ?? $role->is_active,
            ]);

            $this->syncAccess($role, $validated['module_ids'] ?? [], $validated['permiso_ids'] ?? []);
        });

        return response()->json(['message' => 'Rol actualizado correctamente.', 'rol' => $this->roleData($role->fresh())]);
    }

    public function destroy(Request $request, Role $role): JsonResponse
    {
        if (! $this->authorizeModule($request, 'delete')) {
            return response()->json(['message' => 'No tienes permiso para eliminar roles.'], 403);
        }

        if ($role->jerarquia === 1) {
            return response()->json(['message' => 'El rol Admin no puede eliminarse.'], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Rol eliminado correctamente.']);
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('roles', 'nombre')->ignore($role?->id)],
            'jerarquia' => ['sometimes', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
            'module_ids' => ['sometimes', 'array'],
            'module_ids.*' => ['integer', Rule::exists('modules', 'id')],
            'permiso_ids' => ['sometimes', 'array'],
            'permiso_ids.*' => ['integer', Rule::exists('permisos', 'id')],
        ]);
    }

    private function roleData(Role $role): array
    {
        return [
            'id' => $role->id,
            'nombre' => $role->nombre,
            'jerarquia' => $role->jerarquia,
            'is_active' => $role->is_active,
            'module_ids' => $role->modules()->pluck('modules.id')->map(fn ($id) => (int) $id)->values(),
            'permiso_ids' => $role->permisos()->pluck('permisos.id')->map(fn ($id) => (int) $id)->values(),
            'users_count' => $role->users_count ?? $role->users()->count(),
            'created_at' => $role->created_at,
        ];
    }

    private function syncAccess(Role $role, array $moduleIds, array $permisoIds): void
    {
        $moduleIds = collect($moduleIds)->map(fn ($id) => (int) $id)->unique();

        $validPermisoIds = Permiso::whereIn('id', $permisoIds)
            ->whereIn('modulo_id', $moduleIds)
            ->pluck('id');

        if ($role->jerarquia === 1) {
            $protectedModuleIds = Module::whereIn('rute', self::PROTECTED_MODULE_RUTES)->pluck('id');
            $protectedPermisoIds = Permiso::whereIn('modulo_id', $protectedModuleIds)->pluck('id');

            $moduleIds = $moduleIds->merge($protectedModuleIds)->unique();
            $validPermisoIds = $validPermisoIds->merge($protectedPermisoIds)->unique();
        }

        $role->modules()->sync($moduleIds);
        $role->permisos()->sync($validPermisoIds);
    }
}
