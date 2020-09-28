<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProcessamentoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('get/flights/all', [ProcessamentoController::class, 'getListaVoos']);
Route::get('get/flights/groups', [ProcessamentoController::class, 'getAgrupamentoVoos']);
Route::get('get/flights/groups/orderbypreco', [ProcessamentoController::class, 'getAgrupamentoVoosByPreco']);
Route::get('get/flights/groups/result', [ProcessamentoController::class, 'getAgrupamentoVoosCompleto']);