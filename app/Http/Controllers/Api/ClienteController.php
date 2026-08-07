<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    private function authorizeModule(Request $request, string $slug): bool
    {
        return $request->user()->canOnModule('clientes', $slug);
    }

    private function scopeForUser(Request $request)
    {
        $query = Cliente::query();

        if (! $request->user()->isAdmin()) {
            $query->whereHas('users', fn ($q) => $q->where('users.id', $request->user()->id));
        }

        return $query;
    }

    public function index(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'read')) {
            return response()->json(['message' => 'No tienes permiso para ver clientes.'], 403);
        }

        $clientes = $this->scopeForUser($request)
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json([
            'clientes' => $clientes->items(),
            'pagination' => [
                'current_page' => $clientes->currentPage(),
                'last_page' => $clientes->lastPage(),
                'total' => $clientes->total(),
                'per_page' => $clientes->perPage(),
            ],
        ]);
    }

    public function all(Request $request): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'No tienes permiso.'], 403);
        }

        $clientes = Cliente::where('is_active', true)
            ->orderBy('razon_social')
            ->get(['id', 'tipo_documento', 'numero_documento', 'razon_social']);

        return response()->json(['clientes' => $clientes]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'create')) {
            return response()->json(['message' => 'No tienes permiso para crear clientes.'], 403);
        }

        $validated = $request->validate([
            'tipo_documento' => ['required', Rule::in(Cliente::TIPOS_DOCUMENTO)],
            'numero_documento' => ['required', 'string', 'max:20', Rule::unique('clientes', 'numero_documento')->where(fn ($q) => $q->where('tipo_documento', $request->tipo_documento))],
            'razon_social' => ['required', 'string', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $cliente = Cliente::create($validated);

        if (! $request->user()->isAdmin()) {
            $request->user()->clientes()->attach($cliente->id);
        }

        return response()->json(['message' => 'Cliente creado correctamente.', 'cliente' => $cliente], 201);
    }

    public function show(Request $request, Cliente $cliente): JsonResponse
    {
        if (! $this->authorizeModule($request, 'read')) {
            return response()->json(['message' => 'No tienes permiso para ver clientes.'], 403);
        }

        if (! $request->user()->isAdmin() && ! $request->user()->clientes()->whereKey($cliente->id)->exists()) {
            return response()->json(['message' => 'Cliente no encontrado.'], 404);
        }

        return response()->json(['cliente' => $cliente]);
    }

    public function update(Request $request, Cliente $cliente): JsonResponse
    {
        if (! $this->authorizeModule($request, 'edit')) {
            return response()->json(['message' => 'No tienes permiso para editar clientes.'], 403);
        }

        if (! $request->user()->isAdmin() && ! $request->user()->clientes()->whereKey($cliente->id)->exists()) {
            return response()->json(['message' => 'Cliente no encontrado.'], 404);
        }

        $validated = $request->validate([
            'tipo_documento' => ['required', Rule::in(Cliente::TIPOS_DOCUMENTO)],
            'numero_documento' => ['required', 'string', 'max:20', Rule::unique('clientes', 'numero_documento')->ignore($cliente->id)->where(fn ($q) => $q->where('tipo_documento', $request->tipo_documento))],
            'razon_social' => ['required', 'string', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $cliente->update($validated);

        return response()->json(['message' => 'Cliente actualizado correctamente.', 'cliente' => $cliente]);
    }

    public function destroy(Request $request, Cliente $cliente): JsonResponse
    {
        if (! $this->authorizeModule($request, 'delete')) {
            return response()->json(['message' => 'No tienes permiso para eliminar clientes.'], 403);
        }

        if (! $request->user()->isAdmin() && ! $request->user()->clientes()->whereKey($cliente->id)->exists()) {
            return response()->json(['message' => 'Cliente no encontrado.'], 404);
        }

        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado correctamente.']);
    }
}
