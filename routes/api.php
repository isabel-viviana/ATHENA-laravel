<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestDbController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Aquí centralizamos las rutas de diagnóstico y pruebas del sistema.
| Se utiliza un prefijo 'test-db' para mantener el orden.
*/

Route::prefix('test-db')->group(function () {
    // Diagnóstico de integridad de perfiles
    Route::get('/integrity', [TestDbController::class, 'verifyIntegrity']);

    // Diagnóstico de estructura académica (Banco de preguntas)
    Route::get('/academic', [TestDbController::class, 'verifyAcademicStructure']);

    // Diagnóstico de simulacros e historial
    Route::get('/simulators', [TestDbController::class, 'verifySimulators']);

    // Diagnóstico de monetización e IA
    Route::get('/commercial-ai', [TestDbController::class, 'verifyCommercialAndAi']);
});