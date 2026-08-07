<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Permiso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    private const PROTECTED_MODULE_RUTES = ['usuarios', 'permisos', 'roles', 'modules'];

    private const ACTIONS = [
        ['nombre' => 'Crear', 'slug' => 'create'],
        ['nombre' => 'Editar', 'slug' => 'edit'],
        ['nombre' => 'Leer', 'slug' => 'read'],
        ['nombre' => 'Eliminar', 'slug' => 'delete'],
    ];

    private function authorizeModule(Request $request, string $slug): bool
    {
        return $request->user()->canOnModule('modules', $slug);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $roleIds = $user->roles()->pluck('roles.id');

        $modules = Module::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('roles.id', $roleIds))
            ->orderBy('orden')
            ->get(['id', 'nombre', 'icon', 'orden', 'rute']);

        $slugByModule = Permiso::query()
            ->join('role_permisos', 'role_permisos.permiso_id', '=', 'permisos.id')
            ->whereIn('role_permisos.role_id', $roleIds)
            ->get(['permisos.modulo_id', 'permisos.slug'])
            ->groupBy('modulo_id')
            ->map(fn ($group) => $group->pluck('slug')->unique()->values());

        $result = $modules->map(function (Module $module) use ($slugByModule) {
            return [
                'id' => $module->id,
                'nombre' => $module->nombre,
                'icon' => $module->icon,
                'orden' => $module->orden,
                'rute' => $module->rute,
                'permisos' => $slugByModule->get($module->id, collect()),
            ];
        });

        return response()->json(['modules' => $result]);
    }

    public function all(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'read')) {
            return response()->json(['message' => 'No tienes permiso para ver módulos.'], 403);
        }

        $modules = Module::query()
            ->withCount('permisos')
            ->orderBy('orden')
            ->orderBy('id')
            ->paginate(10);

        $data = collect($modules->items())->map(fn (Module $module) => $this->moduleData($module));

        return response()->json([
            'modules' => $data,
            'pagination' => [
                'current_page' => $modules->currentPage(),
                'last_page' => $modules->lastPage(),
                'total' => $modules->total(),
                'per_page' => $modules->perPage(),
            ],
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        $modules = Module::where('is_active', true)
            ->orderBy('orden')
            ->get(['id', 'nombre', 'icon', 'orden', 'rute', 'is_active']);

        $data = $modules->map(fn (Module $module) => $this->moduleData($module, false));

        return response()->json(['modules' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'create')) {
            return response()->json(['message' => 'No tienes permiso para crear módulos.'], 403);
        }

        $validated = $this->validateModule($request);

        $module = DB::transaction(function () use ($validated) {
            $module = Module::create($validated);

            foreach (self::ACTIONS as $action) {
                Permiso::create([
                    'modulo_id' => $module->id,
                    'nombre' => $action['nombre'],
                    'slug' => $action['slug'],
                    'is_active' => true,
                ]);
            }

            return $module;
        });

        return response()->json(['message' => 'Módulo creado correctamente.', 'modulo' => $this->moduleData($module)], 201);
    }

    public function show(Request $request, Module $module): JsonResponse
    {
        if (! $this->authorizeModule($request, 'read')) {
            return response()->json(['message' => 'No tienes permiso para ver módulos.'], 403);
        }

        return response()->json(['modulo' => $this->moduleData($module)]);
    }

    public function update(Request $request, Module $module): JsonResponse
    {
        if (! $this->authorizeModule($request, 'edit')) {
            return response()->json(['message' => 'No tienes permiso para editar módulos.'], 403);
        }

        $validated = $this->validateModule($request, $module);

        $module->update($validated);

        return response()->json(['message' => 'Módulo actualizado correctamente.', 'modulo' => $this->moduleData($module->fresh())]);
    }

    public function destroy(Request $request, Module $module): JsonResponse
    {
        if (! $this->authorizeModule($request, 'delete')) {
            return response()->json(['message' => 'No tienes permiso para eliminar módulos.'], 403);
        }

        if (in_array($module->rute, self::PROTECTED_MODULE_RUTES)) {
            return response()->json(['message' => 'Este módulo está protegido y no puede eliminarse.'], 422);
        }

        $module->delete();

        return response()->json(['message' => 'Módulo eliminado correctamente.']);
    }

    private function validateModule(Request $request, ?Module $module = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('modules', 'nombre')->ignore($module?->id)],
            'icon' => ['nullable', 'string', 'max:100'],
            'orden' => ['sometimes', 'integer', 'min:0'],
            'rute' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('modules', 'rute')->ignore($module?->id)],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function moduleData(Module $module, bool $withPaginationFields = true): array
    {
        $data = [
            'id' => $module->id,
            'nombre' => $module->nombre,
            'icon' => $module->icon,
            'orden' => $module->orden,
            'rute' => $module->rute,
            'is_active' => $module->is_active,
            'permiso_ids' => $module->permisos
                ->mapWithKeys(fn (Permiso $p) => [$p->slug => $p->id]),
        ];

        if ($withPaginationFields) {
            $data['permisos_count'] = $module->permisos_count ?? $module->permisos()->count();
            $data['created_at'] = $module->created_at;
        }

        return $data;
    }
}
