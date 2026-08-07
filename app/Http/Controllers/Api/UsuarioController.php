<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    private function authorizeModule(Request $request, string $slug): bool
    {
        return $request->user()->canOnModule('usuarios', $slug);
    }

    public function index(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'read')) {
            return response()->json(['message' => 'No tienes permiso para ver usuarios.'], 403);
        }

        $usuarios = User::query()
            ->with(['roles', 'clientes'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        $data = collect($usuarios->items())->map(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'roles' => $user->roles->map(fn ($role) => $role->nombre),
                'cliente_ids' => $user->clientes->pluck('id'),
                'clientes' => $user->clientes,
                'created_at' => $user->created_at,
            ];
        });

        return response()->json([
            'usuarios' => $data,
            'pagination' => [
                'current_page' => $usuarios->currentPage(),
                'last_page' => $usuarios->lastPage(),
                'total' => $usuarios->total(),
                'per_page' => $usuarios->perPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'create')) {
            return response()->json(['message' => 'No tienes permiso para crear usuarios.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'is_active' => ['sometimes', 'boolean'],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'cliente_ids' => ['sometimes', 'array'],
            'cliente_ids.*' => ['integer', Rule::exists('clientes', 'id')],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $user->roles()->sync([$validated['role_id']]);
        $user->clientes()->sync($validated['cliente_ids'] ?? []);

        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'usuario' => $user->load(['roles', 'clientes']),
        ], 201);
    }

    public function show(Request $request, User $usuario): JsonResponse
    {
        if (! $this->authorizeModule($request, 'read')) {
            return response()->json(['message' => 'No tienes permiso para ver usuarios.'], 403);
        }

        return response()->json(['usuario' => $usuario->load(['roles', 'clientes'])]);
    }

    public function update(Request $request, User $usuario): JsonResponse
    {
        if (! $this->authorizeModule($request, 'edit')) {
            return response()->json(['message' => 'No tienes permiso para editar usuarios.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['sometimes', 'boolean'],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'cliente_ids' => ['sometimes', 'array'],
            'cliente_ids.*' => ['integer', Rule::exists('clientes', 'id')],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $validated['is_active'] ?? $usuario->is_active,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $usuario->update($data);
        $usuario->roles()->sync([$validated['role_id']]);
        $usuario->clientes()->sync($validated['cliente_ids'] ?? []);

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'usuario' => $usuario->load(['roles', 'clientes']),
        ]);
    }

    public function destroy(Request $request, User $usuario): JsonResponse
    {
        if (! $this->authorizeModule($request, 'delete')) {
            return response()->json(['message' => 'No tienes permiso para eliminar usuarios.'], 403);
        }

        if ($usuario->id === $request->user()->id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta.'], 422);
        }

        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente.']);
    }
}
