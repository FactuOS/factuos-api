<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Models\ComprobanteCuota;
use App\Models\ComprobanteDetalle;
use App\Models\ComprobanteLog;
use App\Models\ComprobanteTipo;
use App\Models\Item;
use App\Models\Serie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ComprobanteController extends Controller
{
    use ResuelveEmpresa;

    public function index(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'boletas', 'read') && ! $this->authorizeModule($request, 'facturas', 'read')) {
            return response()->json(['message' => 'No tienes permiso para ver comprobantes.'], 403);
        }

        $query = Comprobante::with(['comprobanteTipo:id,codigo,nombre', 'cliente:id,razon_social']);

        if ($empresaId = $this->empresaId($request)) {
            $query->where('empresa_id', $empresaId);
        }

        if ($request->filled('tipo')) {
            $query->whereHas('comprobanteTipo', fn ($q) => $q->where('codigo', $request->input('tipo')));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $comprobantes = $query->orderByDesc('id')->paginate(10);

        return response()->json([
            'comprobantes' => $comprobantes->items(),
            'pagination' => [
                'current_page' => $comprobantes->currentPage(),
                'last_page' => $comprobantes->lastPage(),
                'total' => $comprobantes->total(),
                'per_page' => $comprobantes->perPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['tipo' => ['required', Rule::in(['01', '03'])]]);
        $tipoCodigo = $request->input('tipo');

        if (! $this->authorizeModule($request, $tipoCodigo === '03' ? 'boletas' : 'facturas', 'create')) {
            return response()->json(['message' => 'No tienes permiso para emitir comprobantes.'], 403);
        }

        $empresaId = $this->empresaIdObligatoria($request);
        $tipo = ComprobanteTipo::where('codigo', $tipoCodigo)->firstOrFail();

        $validated = $this->validarStore($request);

        try {
            $comprobante = DB::transaction(function () use ($request, $empresaId, $tipo, $validated) {
                $serie = $this->resolverSerie($empresaId, $tipo->id);

                $numero = $serie->nextCorrelativo();
                $serie->increment('correlativo_actual');

                $comprobante = Comprobante::create([
                    'empresa_id' => $empresaId,
                    'user_id' => $request->user()->id,
                    'comprobante_tipo_id' => $tipo->id,
                    'serie' => $serie->serie,
                    'numero' => $numero,
                    'fecha_emision' => $validated['fecha_emision'],
                    'hora_emision' => now()->format('H:i:s'),
                    'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
                    'cliente_id' => $validated['cliente_id'] ?? null,
                    'moneda' => $validated['moneda'] ?? 'PEN',
                    'tipo_operacion' => '0101',
                    'forma_pago' => $validated['forma_pago'],
                    'estado' => 'emitido',
                    'observacion' => $validated['observacion'] ?? null,
                ]);

                $totales = $this->crearDetalles($comprobante, $validated['items']);
                $comprobante->update($totales);

                if ($validated['forma_pago'] === 'CREDITO') {
                    $this->crearCuotas($comprobante, $validated['cuotas'], $comprobante->total);
                }

                $this->registrarLog($comprobante, null, 'emitido', $request->user(), 'Comprobante emitido.');

                return $comprobante->fresh(['comprobanteTipo', 'cliente', 'detalles', 'cuotas']);
            });
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Comprobante emitido correctamente.', 'comprobante' => $comprobante], 201);
    }

    public function show(Request $request, Comprobante $comprobante): JsonResponse
    {
        $tipo = $comprobante->comprobanteTipo;

        if (! $this->authorizeModule($request, $tipo->codigo === '03' ? 'boletas' : 'facturas', 'read')) {
            return response()->json(['message' => 'No tienes permiso.'], 403);
        }

        $comprobante->load(['comprobanteTipo', 'cliente', 'detalles', 'cuotas', 'user:id,name']);

        return response()->json(['comprobante' => $comprobante]);
    }

    public function anular(Request $request, Comprobante $comprobante): JsonResponse
    {
        $tipo = $comprobante->comprobanteTipo;
        $module = $tipo->codigo === '03' ? 'boletas' : 'facturas';

        if (! $this->authorizeModule($request, $module, 'delete')) {
            return response()->json(['message' => 'No tienes permiso para anular.'], 403);
        }

        if ($comprobante->estado === 'anulado') {
            return response()->json(['message' => 'El comprobante ya está anulado.']);
        }

        DB::transaction(function () use ($comprobante, $request) {
            $anterior = $comprobante->estado;
            $comprobante->update(['estado' => 'anulado']);
            $this->registrarLog($comprobante, $anterior, 'anulado', $request->user(), 'Comprobante anulado.');
        });

        return response()->json(['message' => 'Comprobante anulado correctamente.']);
    }

    private function validarStore(Request $request): array
    {
        return $request->validate([
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:fecha_emision'],
            'moneda' => ['nullable', 'string', 'size:3'],
            'forma_pago' => ['required', Rule::in(['CONTADO', 'CREDITO'])],
            'observacion' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'items.*.descuento' => ['sometimes', 'integer', 'min:0'],
            'cuotas' => ['required_if:forma_pago,CREDITO', 'array'],
            'cuotas.*.monto' => ['required_if:forma_pago,CREDITO', 'integer', 'min:1'],
            'cuotas.*.fecha_pago' => ['required_if:forma_pago,CREDITO', 'date'],
        ]);
    }

    private function resolverSerie(int $empresaId, int $tipoId): Serie
    {
        $serie = Serie::where('empresa_id', $empresaId)
            ->where('comprobante_tipo_id', $tipoId)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();

        if (! $serie) {
            abort(422, 'No existe una serie activa para este tipo de comprobante y empresa.');
        }

        return $serie;
    }

    private function crearDetalles(Comprobante $comprobante, array $items): array
    {
        $subtotal = $igv = $total = $descuentoTotal = 0;

        foreach ($items as $linea) {
            $item = Item::findOrFail($linea['item_id']);
            $cantidad = (float) $linea['cantidad'];
            $descuento = (int) ($linea['descuento'] ?? 0);
            $pct = $item->porcentajeIgv();

            $base = (int) round($item->precio * $cantidad);
            $subtotalLinea = $base - $descuento;
            $igvLinea = (int) round($subtotalLinea * $pct / 100);
            $precioUnitario = (int) round($item->precio * (1 + $pct / 100));
            $totalLinea = $subtotalLinea + $igvLinea;

            ComprobanteDetalle::create([
                'comprobante_id' => $comprobante->id,
                'item_id' => $item->id,
                'descripcion' => $item->nombre,
                'cantidad' => $cantidad,
                'unidad_medida' => $item->unidad_medida,
                'valor_unitario' => $item->precio,
                'precio_unitario' => $precioUnitario,
                'descuento' => $descuento,
                'subtotal' => $subtotalLinea,
                'igv' => $igvLinea,
                'total' => $totalLinea,
                'afectacion_igv' => $item->afectacion_igv,
                'porcentaje_igv' => $item->porcentajeIgv(),
            ]);

            $subtotal += $subtotalLinea;
            $igv += $igvLinea;
            $total += $totalLinea;
            $descuentoTotal += $descuento;
        }

        return [
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $total,
            'descuento_total' => $descuentoTotal,
        ];
    }

    private function crearCuotas(Comprobante $comprobante, array $cuotas, int $total): void
    {
        $suma = array_sum(array_column($cuotas, 'monto'));

        if ($suma !== $total) {
            abort(422, 'La suma de las cuotas debe ser igual al total del comprobante.');
        }

        foreach ($cuotas as $i => $cuota) {
            ComprobanteCuota::create([
                'comprobante_id' => $comprobante->id,
                'numero' => $i + 1,
                'monto' => $cuota['monto'],
                'fecha_pago' => $cuota['fecha_pago'],
            ]);
        }
    }

    private function registrarLog(Comprobante $comprobante, ?string $anterior, string $nuevo, $user, string $detalle): void
    {
        ComprobanteLog::create([
            'comprobante_id' => $comprobante->id,
            'tipo_documento' => 'comprobante',
            'estado_anterior' => $anterior,
            'estado_nuevo' => $nuevo,
            'user_id' => $user?->id,
            'detalle' => $detalle,
            'created_at' => now(),
        ]);
    }
}