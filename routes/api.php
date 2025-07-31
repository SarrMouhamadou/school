<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClassesController;
use App\Http\Controllers\EtudiantsController;
use App\Http\Controllers\EnseignantsController;
use App\Http\Controllers\MatieresController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\ParentsController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Page d'accueil
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Routes authentifiées
Route::middleware(['auth'])->group(function () {
    // Redirection du dashboard selon le rôle
    Route::get('/dashboard', function () {
        $user = auth()->user();
        \Log::info('Dashboard accessed', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'role_name' => $user->role ? $user->role->name : 'null'
        ]);

        if (!$user->role) {
            \Log::error('User has no role', ['user_id' => $user->id, 'email' => $user->email]);
            return redirect()->route('home')->with('error', 'Rôle non défini. Contactez l’administrateur.');
        }

        switch ($user->role->name) {
            case 'admin':
                \Log::info('Redirecting to admin.dashboard', ['user_id' => $user->id]);
                return redirect()->route('admin.dashboard');
            case 'enseignant':
                \Log::info('Redirecting to enseignant.dashboard', ['user_id' => $user->id]);
                return redirect()->route('enseignant.dashboard');
            case 'eleve':
                \Log::info('Redirecting to eleve.dashboard', ['user_id' => $user->id]);
                return redirect()->route('eleve.dashboard');
            case 'parent':
                \Log::info('Redirecting to parent.dashboard', ['user_id' => $user->id]);
                return redirect()->route('parent.dashboard');
            default:
                \Log::warning('Unknown role', ['role' => $user->role->name, 'user_id' => $user->id]);
                return view('dashboard');
        }
    })->name('dashboard');

    // Routes pour admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'indexWeb'])->name('admin.dashboard');
        Route::resource('admin/classes', ClassesController::class)->names('admin.classes');
        Route::resource('admin/etudiants', EtudiantsController::class)->names('admin.etudiants');
        Route::resource('admin/enseignants', EnseignantsController::class)->names('admin.enseignants');
        Route::resource('admin/matieres', MatieresController::class)->names('admin.matieres');
        Route::resource('admin/parents', ParentsController::class)->names('admin.parents');
        Route::put('/admin/etudiants/{id}/affecter-classe', [EtudiantsController::class, 'affecterClasse'])->name('admin.etudiants.affecter-classe');
        Route::put('/admin/enseignants/affecter-matiere', [EnseignantsController::class, 'affecterMatiere'])->name('admin.enseignants.affecter-matiere');
        Route::get('/admin/bulletins/{classeId}/{semestre}/download', [DashboardController::class, 'downloadClassBulletins'])->name('admin.bulletins.download')->where('semestre', 'S1|S2');
    });

    // Routes pour enseignant
    Route::middleware('role:enseignant')->group(function () {
        Route::get('/enseignant/dashboard', [NotesController::class, 'indexWeb'])->name('enseignant.dashboard');
        Route::get('/enseignant/notes/create', [NotesController::class, 'create'])->name('enseignant.notes.create');
        Route::post('/enseignant/notes', [NotesController::class, 'store'])->name('enseignant.notes.store');
        Route::get('/enseignant/notes/{id}/edit', [NotesController::class, 'edit'])->name('enseignant.notes.edit');
        Route::put('/enseignant/notes/{id}', [NotesController::class, 'update'])->name('enseignant.notes.update');
        Route::delete('/enseignant/notes/{id}', [NotesController::class, 'destroy'])->name('enseignant.notes.destroy');
    });

    // Routes pour élève
    Route::middleware('role:eleve')->group(function () {
        Route::get('/eleve/dashboard', [NotesController::class, 'getStudentNotesWeb'])->name('eleve.dashboard');
        Route::get('/eleve/bulletin/{semestre}', [NotesController::class, 'calculateBulletinWeb'])->name('eleve.bulletin')->where('semestre', 'S1|S2');
        Route::get('/eleve/bulletin/{semestre}/download', [NotesController::class, 'downloadBulletin'])->name('eleve.bulletin.download')->where('semestre', 'S1|S2');
    });

    // Routes pour parent
    Route::middleware('role:parent')->group(function () {
        Route::get('/parent/dashboard', [ParentsController::class, 'getStudentsWeb'])->name('parent.dashboard');
        Route::get('/parent/etudiants/{etudiantId}/bulletin/{semestre}', [NotesController::class, 'calculateBulletinWeb'])->name('parent.bulletin')->where('semestre', 'S1|S2');
        Route::get('/parent/etudiants/{etudiantId}/bulletin/{semestre}/download', [NotesController::class, 'downloadBulletin'])->name('parent.bulletin.download')->where('semestre', 'S1|S2');
    });

    // Routes de profil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
