<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ComprobanteTipo;
use App\Models\Serie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SerieController extends Controller
{
    use ResuelveEmpresa;

    public function index(Request $request): JsonResponse
    {
        $query = Serie::with('comprobanteTipo:id,codigo,nombre');

        if ($empresaId = $this->empresaId($request)) {
            $query->where('empresa_id', $empresaId);
        }

        if ($request->filled('comprobante_tipo_id')) {
            $query->where('comprobante_tipo_id', $request->input('comprobante_tipo_id'));
        }

        $series = $query->orderBy('serie')->get();

        return response()->json(['series' => $series]);
    }

    public function tipos(Request $request): JsonResponse
    {
        $tipos = ComprobanteTipo::orderBy('codigo')->get(['id', 'codigo', 'nombre', 'abreviacion']);

        return response()->json(['tipos' => $tipos]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'boletas', 'create')) {
            return response()->json(['message' => 'No tienes permiso para crear series.'], 403);
        }

        $empresaId = $this->empresaIdObligatoria($request);

        $validated = $request->validate([
            'comprobante_tipo_id' => ['required', 'exists:comprobante_tipos,id'],
            'serie' => ['required', 'string', 'max:4', Rule::unique('series', 'serie')->where(fn ($q) => $q->where('empresa_id', $empresaId))],
            'correlativo_actual' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $serie = Serie::create([
            'empresa_id' => $empresaId,
            'comprobante_tipo_id' => $validated['comprobante_tipo_id'],
            'serie' => $validated['serie'],
            'correlativo_actual' => $validated['correlativo_actual'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json(['message' => 'Serie creada correctamente.', 'serie' => $serie], 201);
    }
}