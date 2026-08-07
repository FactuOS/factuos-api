<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Comprobante;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    use ResuelveEmpresa;

    public function resumen(Request $request): JsonResponse
    {
        if (! $this->authorizeModule($request, 'reportes', 'read')) {
            return response()->json(['message' => 'No tienes permiso para ver reportes.'], 403);
        }

        $empresaId = $this->empresaId($request);

        $base = fn ($query, string $table) => $query
            ->when($empresaId, fn ($q) => $q->where($table.'.empresa_id', $empresaId))
            ->whereNull($table.'.deleted_at');

        $porEstado = $base(Comprobante::query(), 'comprobantes')
            ->select('estado', DB::raw('count(*) as total'), DB::raw('sum(total) as monto'))
            ->groupBy('estado')
            ->pluck('estado', 'total');

        $ventasMensuales = $base(Comprobante::query(), 'comprobantes')
            ->select(DB::raw("to_char(fecha_emision, 'YYYY-MM') as mes"), DB::raw('sum(total) as monto'))
            ->whereIn('estado', ['emitido', 'aceptado'])
            ->groupBy(DB::raw("to_char(fecha_emision, 'YYYY-MM')"))
            ->orderBy('mes')
            ->get();

        $topClientes = $base(Comprobante::query(), 'comprobantes')
            ->join('clientes', 'cliente_id', '=', 'comprobantes.cliente_id')
            ->select('clientes.razon_social', DB::raw('sum(comprobantes.total) as monto'), DB::raw('count(*) as docs'))
            ->whereNotNull('cliente_id')
            ->groupBy('clientes.razon_social')
            ->orderByDesc('monto')
            ->limit(5)
            ->get();

        return response()->json([
            'por_estado' => $porEstado,
            'ventas_mensuales' => $ventasMensuales,
            'top_clientes' => $topClientes,
        ]);
    }
}