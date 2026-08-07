<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

trait ResuelveEmpresa
{
    protected function authorizeModule(Request $request, string $module, string $slug): bool
    {
        return $request->user()->canOnModule($module, $slug);
    }

    protected function empresaId(Request $request): ?int
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            return $user->empresa_id;
        }

        $empresaId = $request->input('empresa_id');

        return $empresaId ? (int) $empresaId : null;
    }

    protected function empresaIdObligatoria(Request $request): int
    {
        $empresaId = $this->empresaId($request);

        if (! $empresaId) {
            abort(422, 'Debe especificar empresa_id.');
        }

        return $empresaId;
    }
}