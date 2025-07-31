<?php
namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Etudiant;
use App\Models\Enseignant;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function indexWeb($semestre = null)
    {
        $stats = [
            'nombre_eleves' => Etudiant::count(),
            'nombre_enseignants' => Enseignant::count(),
            'nombre_classes' => Classe::count(),
            'nombre_parents' => User::where('role_id', function ($query) {
                $query->select('id')->from('roles')->where('name', 'parent');
            })->count(),
        ];
        return view('admin.dashboard', compact('stats', 'semestre'));
    }

    // Réutiliser la méthode downloadClassBulletins existante
    public function downloadClassBulletins($classeId, $semestre)
    {
        // Logique existante (inchangée)
    }
}
