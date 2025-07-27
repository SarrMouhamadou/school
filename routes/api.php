<?php

use App\Http\Controllers\API\ControllerEtudiants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ControllerClasses;
use App\Http\Controllers\API\ControllerEnseignants;
use App\Http\Controllers\API\ControllerMatieres;
use App\Http\Controllers\API\ControllerNotes;
use App\Http\Controllers\API\ControllerParents;
use App\Http\Controllers\API\DashboardController;

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

Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'getUser'])->name('user');

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/etudiant/inscrire', [ControllerEtudiants::class, 'inscrire']);
    Route::get('/etudiants', [ControllerEtudiants::class, 'listerEtudiants']);
    Route::put('/etudiant/{id}', [ControllerEtudiants::class, 'update']);
    Route::delete('/etudiant/{id}', [ControllerEtudiants::class, 'destroy']);
    Route::put('/etudiant/{id}/affecter-classe', [ControllerEtudiants::class, 'affecterClasse']); // Ajouter cette ligne
    Route::put('/etudiant/{id}/affecter-classe', [ControllerEtudiants::class, 'affecterClasse']); // Assurez-vous qu'elle est décommetée
    Route::post('/classe', [ControllerClasses::class, 'store']);
    Route::get('/classes', [ControllerClasses::class, 'index']);
    Route::put('/classe/{id}', [ControllerClasses::class, 'update']);
    Route::delete('/classe/{id}', [ControllerClasses::class, 'destroy']);
    Route::post('/matiere', [ControllerMatieres::class, 'store']);
    Route::get('/matieres', [ControllerMatieres::class, 'index']);
    Route::put('/matiere/{id}', [ControllerMatieres::class, 'update']);
    Route::delete('/matiere/{id}', [ControllerMatieres::class, 'destroy']);
    Route::post('/enseignant', [ControllerEnseignants::class, 'storeEnseignant']);
    Route::post('/enseignant/affecter-matiere', [ControllerEnseignants::class, 'affecterMatiere']);
    Route::get('/enseignants', [ControllerEnseignants::class, 'index']);
    Route::put('/enseignant/{id}', [ControllerEnseignants::class, 'update']);
    Route::delete('/enseignant/{id}', [ControllerEnseignants::class, 'destroy']);
    Route::post('/parent/register', [ControllerParents::class, 'register']);
    Route::put('/parent/{parentId}/link-student', [ControllerParents::class, 'linkStudent']);
    Route::get('/parent/{parentId}/students', [ControllerParents::class, 'getStudents']);
    Route::get('/parents', [ControllerParents::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/{semestre?}', [DashboardController::class, 'index'])->where('semestre', 'S1|S2');
    Route::get('/dashboard/{classeId}/bulletins/{semestre}/download', [DashboardController::class, 'downloadClassBulletins']);
});



Route::middleware(['auth:sanctum', 'role:enseignant'])->group(function () {
    Route::post('/notes', [ControllerNotes::class, 'store']);
    Route::put('/notes/{id}', [ControllerNotes::class, 'update']);
    Route::delete('/notes/{id}', [ControllerNotes::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:eleve,parent'])->group(function () {
    Route::get('/etudiants/{etudiantId}/notes/{semestre}', [ControllerNotes::class, 'getStudentNotes']);
    Route::get('/etudiants/{etudiantId}/bulletin/{semestre}', [ControllerNotes::class, 'calculateBulletin']);
    Route::get('/portail/bulletins/{userId}', [ControllerNotes::class, 'getBulletins']);
    Route::get('/portail/bulletins/{userId}/{semestre}/download', [ControllerNotes::class, 'downloadBulletin']);
    Route::get('/portail/bulletins/{userId}/{semestre}/{studentId?}/download', [ControllerNotes::class, 'downloadBulletin']);
});

Route::post('/register', [UserController::class, 'register'])->name('register');
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');
