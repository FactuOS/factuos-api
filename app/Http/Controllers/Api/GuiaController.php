<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ComprobanteLog;
use App\Models\GuiaRemision;
use App\Models\GuiaRemisionItem;
use App\Models\Item;
use App\Models\Serie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GuiaController extends Controller
{
    use ResuelveEmpresa;

    public function index(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'clientes', 'read')) {
            return response()->json(['message' => 'No tienes permiso.'], 403);
        }

        $query = GuiaRemision::with('comprobanteTipo:id,codigo,nombre');

        if ($empresaId = $this->empresaId($request)) {
            $query->where('empresa_id', $empresaId);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $guias = $query->orderByDesc('id')->paginate(10);

        return response()->json([
            'guias' => $guias->items(),
            'pagination' => [
                'current_page' => $guias->currentPage(),
                'last_page' => $guias->lastPage(),
                'total' => $guias->total(),
                'per_page' => $guias->perPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'clientes', 'create')) {
            return response()->json(['message' => 'No tienes permiso.'], 403);
        }

        $empresaId = $this->empresaIdObligatoria($request);
        $tipo = \App\Models\ComprobanteTipo::where('codigo', '09')->firstOrFail();

        $validated = $this->validarStore($request);

        try {
            $guia = DB::transaction(function () use ($request, $empresaId, $tipo, $validated) {
                $serie = Serie::where('empresa_id', $empresaId)
                    ->where('comprobante_tipo_id', $tipo->id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (! $serie) {
                    abort(422, 'No existe una serie activa para guías de remisión.');
                }

                $numero = $serie->nextCorrelativo();
                $serie->increment('correlativo_actual');

                $guia = GuiaRemision::create(array_merge($validated, [
                    'empresa_id' => $empresaId,
                    'user_id' => $request->user()->id,
                    'comprobante_tipo_id' => $tipo->id,
                    'serie' => $serie->serie,
                    'numero' => $numero,
                    'hora_emision' => $validated['hora_emision'] ?? now()->format('H:i:s'),
                    'estado' => 'emitido',
                ]));

                $this->crearItems($guia, $validated['items']);
                $this->registrarLog($guia->id, null, 'emitido', $request->user());

                return $guia->fresh(['comprobanteTipo', 'items']);
            });
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Guía emitida correctamente.', 'guia' => $guia], 201);
    }

    public function show(Request $request, GuiaRemision $guia): JsonResponse
    {
        if (! $this->authorizeModule($request, 'clientes', 'read')) {
            return response()->json(['message' => 'No tienes permiso.'], 403);
        }

        $guia->load(['comprobanteTipo', 'items', 'comprobanteRelacionado:id,serie,numero']);

        return response()->json(['guia' => $guia]);
    }

    public function anular(Request $request, GuiaRemision $guia): JsonResponse
    {
        if (! $this->authorizeModule($request, 'clientes', 'delete')) {
            return response()->json(['message' => 'No tienes permiso.'], 403);
        }

        $anterior = $guia->estado;
        $guia->update(['estado' => 'anulado']);
        $this->registrarLog($guia, $anterior, 'anulado', $request->user());

        return response()->json(['message' => 'Guía anulada correctamente.']);
    }

    private function validarStore(Request $request): array
    {
        return $request->validate([
            'fecha_emision' => ['required', 'date'],
            'motivo_traslado' => ['required', 'string', 'size:2'],
            'peso_bruto' => ['required', 'numeric', 'gte:0'],
            'unidad_peso' => ['nullable', 'string', 'size:3'],
            'numero_bultos' => ['nullable', 'integer', 'min:0'],
            'partida_ubigeo' => ['required', 'string', 'size:6'],
            'partida_direccion' => ['required', 'string', 'max:255'],
            'llegada_ubigeo' => ['required', 'string', 'size:6'],
            'llegada_direccion' => ['required', 'string', 'max:255'],
            'destinatario_tipo_documento' => ['required', 'string', 'size:1'],
            'destinatario_numero_documento' => ['required', 'string', 'max:20'],
            'destinatario_razon_social' => ['required', 'string', 'max:255'],
            'modalidad_transporte' => ['required', Rule::in(['01', '02'])],
            'transportista_razon_social' => ['nullable', 'string', 'max:255'],
            'transportista_tipo_documento' => ['nullable', 'string', 'size:1'],
            'transportista_numero_documento' => ['nullable', 'string', 'max:20'],
            'conductor_licencia' => ['nullable', 'string', 'max:50'],
            'vehiculo_placa' => ['nullable', 'string', 'max:20'],
            'comprobante_id_relacionado' => ['nullable', 'exists:comprobantes,id'],
            'observacion' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.cantidad' => ['required', 'numeric', 'gt:0'],
        ]);
    }

    private function crearItems(GuiaRemision $guia, array $items): void
    {
        foreach ($items as $linea) {
            $item = Item::findOrFail($linea['item_id']);

            GuiaRemisionItem::create([
                'guia_remision_id' => $guia->id,
                'item_id' => $item->id,
                'descripcion' => $item->nombre,
                'cantidad' => $linea['cantidad'],
                'unidad_medida' => $item->unidad_medida,
                'codigo' => $item->codigo,
            ]);
        }
    }

    private function registrarLog(GuiaRemision $guia, ?string $anterior, string $nuevo, $user): void
    {
        ComprobanteLog::create([
            'comprobante_id' => $guia->id,
            'tipo_documento' => 'guia_remision',
            'estado_anterior' => $anterior,
            'estado_nuevo' => $nuevo,
            'user_id' => $user?->id,
            'created_at' => now(),
        ]);
    }
}