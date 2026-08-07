<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ComprobanteController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\GuiaController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\PermisoController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SerieController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/modules', [ModuleController::class, 'index']);
    Route::get('/modules/all', [ModuleController::class, 'all']);
    Route::get('/modules/options', [ModuleController::class, 'options']);
    Route::post('/modules', [ModuleController::class, 'store']);
    Route::get('/modules/{module}', [ModuleController::class, 'show']);
    Route::put('/modules/{module}', [ModuleController::class, 'update']);
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy']);

    Route::get('/clientes/all', [ClienteController::class, 'all']);
    Route::apiResource('/clientes', ClienteController::class);
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/all', [RoleController::class, 'all']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::get('/roles/{role}', [RoleController::class, 'show']);
    Route::put('/roles/{role}', [RoleController::class, 'update']);
    Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
    Route::apiResource('/usuarios', UsuarioController::class);

    Route::get('/permisos/config', [PermisoController::class, 'config']);
    Route::put('/permisos', [PermisoController::class, 'update']);

    Route::get('/items/all', [ItemController::class, 'all']);
    Route::apiResource('/items', ItemController::class);
    Route::get('/series', [SerieController::class, 'index']);
    Route::get('/series/tipos', [SerieController::class, 'tipos']);
    Route::post('/series', [SerieController::class, 'store']);

    Route::get('/empresas', [EmpresaController::class, 'show']);
    Route::get('/empresas/all', [EmpresaController::class, 'listAll']);
    Route::put('/empresas', [EmpresaController::class, 'update']);
    Route::get('/empresas/sunat-config', [EmpresaController::class, 'show']);
    Route::put('/empresas/sunat-config', [EmpresaController::class, 'updateSunatConfig']);

    Route::get('/comprobantes', [ComprobanteController::class, 'index']);
    Route::post('/comprobantes', [ComprobanteController::class, 'store']);
    Route::get('/comprobantes/{comprobante}', [ComprobanteController::class, 'show']);
    Route::post('/comprobantes/{comprobante}/anular', [ComprobanteController::class, 'anular']);

    Route::get('/guias', [GuiaController::class, 'index']);
    Route::post('/guias', [GuiaController::class, 'store']);
    Route::get('/guias/{guia}', [GuiaController::class, 'show']);
    Route::post('/guias/{guia}/anular', [GuiaController::class, 'anular']);

    Route::get('/reportes/resumen', [ReporteController::class, 'resumen']);
});
