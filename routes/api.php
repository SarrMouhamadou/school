<?php

use App\Http\Controllers\API\ControllerEtudiants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ControllerClasses;
use App\Http\Controllers\API\ControllerEnseignants;
use App\Http\Controllers\API\ControllerMatieres;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/user', [UserController::class, 'getUser'])->middleware('auth:sanctum');

Route::post('/etudiant/inscrire', [ControllerEtudiants::class, 'inscrire']);
Route::get('/etudiants', [ControllerEtudiants::class, 'listerEtudiants']);
Route::put('/etudiant/{id}', [ControllerEtudiants::class, 'update']);
Route::delete('/etudiant/{id}', [ControllerEtudiants::class, 'destroy']);

Route::post('/classe', [ControllerClasses::class, 'store']);
Route::get('/classes', [ControllerClasses::class, 'index']);
Route::put('/classe/{id}', [ControllerClasses::class, 'update']);
Route::delete('/classe/{id}', [ControllerClasses::class, 'destroy']);

Route::post('/enseignant', [ControllerEnseignants::class, 'storeEnseignant']);
Route::get('/enseignants', [ControllerEnseignants::class, 'index']);
Route::post('/enseignant/affecter-matiere', [ControllerEnseignants::class, 'affecterMatiere']);
Route::put('/enseignant/{id}', [ControllerEnseignants::class, 'update']);
Route::delete('/enseignant/{id}', [ControllerEnseignants::class, 'destroy']);

Route::get('/matieres', [ControllerMatieres::class, 'index']);
Route::post('/matiere', [ControllerMatieres::class, 'store']);
Route::put('/matiere/{id}', [ControllerMatieres::class, 'update']);
Route::delete('/matiere/{id}', [ControllerMatieres::class, 'destroy']);
