<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    use ResuelveEmpresa;

    public function listAll(Request $request): JsonResponse
    {
        $empresas = Empresa::where('is_active', true)
            ->orderBy('razon_social')
            ->get(['id', 'razon_social', 'nombre_comercial']);

        return response()->json(['empresas' => $empresas]);
    }

    public function show(Request $request): JsonResponse
    {
        $empresa = $this->resolveEmpresa($request);

        if (! $empresa) {
            return response()->json(['message' => 'Sin empresa asociada.'], 404);
        }

        $empresa->load('sunatConfig');

        return response()->json(['empresa' => $empresa]);
    }

    public function update(Request $request): JsonResponse
    {
        $empresa = $this->resolveEmpresa($request);

        if (! $empresa) {
            return response()->json(['message' => 'Sin empresa asociada.'], 404);
        }

        $validated = $request->validate([
            'razon_social' => ['required', 'string', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ubigeo' => ['nullable', 'string', 'size:6'],
            'departamento' => ['nullable', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
            'distrito' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'logo' => ['nullable', 'string', 'max:255'],
            'representante_legal' => ['nullable', 'string', 'max:255'],
            'dni_representante' => ['nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $empresa->update($validated);

        return response()->json(['message' => 'Empresa actualizada correctamente.', 'empresa' => $empresa]);
    }

    public function updateSunatConfig(Request $request): JsonResponse
    {
        $empresa = $this->resolveEmpresa($request);

        if (! $empresa) {
            return response()->json(['message' => 'Sin empresa asociada.'], 404);
        }

        $validated = $request->validate([
            'sol_user' => ['required', 'string', 'max:255'],
            'sol_pass' => ['required', 'string', 'max:255'],
            'certificate_path' => ['nullable', 'string', 'max:255'],
            'certificate_password' => ['nullable', 'string', 'max:255'],
            'production' => ['sometimes', 'boolean'],
        ]);

        $config = $empresa->sunatConfig()->firstOrNew([]);
        $config->fill($validated);
        $config->production = $validated['production'] ?? ($config->production ?? false);
        $config->save();

        return response()->json(['message' => 'Configuración SUNAT guardada correctamente.']);
    }

    private function resolveEmpresa(Request $request): ?Empresa
    {
        $id = $this->empresaId($request);

        if ($id) {
            return Empresa::find($id);
        }

        return $request->user()->empresa;
    }
}