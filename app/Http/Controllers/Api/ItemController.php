<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    use ResuelveEmpresa;

    public function index(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'servicios', 'read')) {
            return response()->json(['message' => 'No tienes permiso para ver servicios.'], 403);
        }

        $query = Item::query();

        if ($empresaId = $this->empresaId($request)) {
            $query->where('empresa_id', $empresaId);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        $items = $query->when($request->filled('q'), fn ($q) => $q->where('nombre', 'ilike', '%'.$request->input('q').'%'))
            ->orderBy('codigo')
            ->paginate(10);

        return response()->json([
            'items' => $items->items(),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
                'per_page' => $items->perPage(),
            ],
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        $query = Item::where('is_active', true);

        if ($empresaId = $this->empresaId($request)) {
            $query->where('empresa_id', $empresaId);
        }

        $items = $query->orderBy('nombre')
            ->get(['id', 'tipo', 'codigo', 'nombre', 'precio', 'unidad_medida', 'afectacion_igv']);

        return response()->json(['items' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'servicios', 'create')) {
            return response()->json(['message' => 'No tienes permiso para crear servicios.'], 403);
        }

        $empresaId = $this->empresaIdObligatoria($request);

        $validated = $request->validate([
            'tipo' => ['required', Rule::in(Item::TIPOS)],
            'codigo' => ['required', 'string', 'max:30', Rule::unique('items', 'codigo')->where(fn ($q) => $q->where('empresa_id', $empresaId))],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'unidad_medida' => ['nullable', Rule::in(Item::UNIDADES_MEDIDA)],
            'precio' => ['required', 'integer', 'min:0'],
            'afectacion_igv' => ['nullable', Rule::in(array_keys(Item::AFECTACIONES_IGV))],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $item = Item::create(array_merge($validated, [
            'empresa_id' => $empresaId,
            'unidad_medida' => $validated['unidad_medida'] ?? 'NIU',
            'afectacion_igv' => $validated['afectacion_igv'] ?? '10',
            'moneda' => 'PEN',
            'is_active' => $validated['is_active'] ?? true,
        ]));

        return response()->json(['message' => 'Servicio creado correctamente.', 'item' => $item], 201);
    }

    public function show(Request $request, Item $item): JsonResponse
    {
        if (! $this->authorizeModule($request, 'servicios', 'read')) {
            return response()->json(['message' => 'No tienes permiso.'], 403);
        }

        return response()->json(['item' => $item]);
    }

    public function update(Request $request, Item $item): JsonResponse
    {
        if (! $this->authorizeModule($request, 'servicios', 'edit')) {
            return response()->json(['message' => 'No tienes permiso para editar servicios.'], 403);
        }

        $empresaId = $this->empresaIdObligatoria($request);

        $validated = $request->validate([
            'tipo' => ['required', Rule::in(Item::TIPOS)],
            'codigo' => ['required', 'string', 'max:30'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'unidad_medida' => ['nullable', Rule::in(Item::UNIDADES_MEDIDA)],
            'precio' => ['required', 'integer', 'min:0'],
            'afectacion_igv' => ['nullable', Rule::in(array_keys(Item::AFECTACIONES_IGV))],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $item->update(array_merge($validated, [
            'empresa_id' => $empresaId,
            'moneda' => 'PEN',
        ]));

        return response()->json(['message' => 'Servicio actualizado correctamente.', 'item' => $item]);
    }

    public function destroy(Request $request, Item $item): JsonResponse
    {
        if (! $this->authorizeModule($request, 'servicios', 'delete')) {
            return response()->json(['message' => 'No tienes permiso para eliminar servicios.'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Servicio eliminado correctamente.']);
    }
}