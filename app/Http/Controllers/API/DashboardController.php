<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Enseignant;
use App\Models\Classe;
use App\Models\Note;
use App\Models\Matiere;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user || $user->role->name !== 'admin') {
            return response()->json(['message' => 'Unauthorized ! Only admins can view the dashboard', 'status' => false], 403);
        }

        // Statistiques globales
        $stats = [
            'nombre_eleves' => Etudiant::count(),
            'nombre_enseignants' => Enseignant::count(),
            'nombre_classes' => Classe::count(),
        ];

        // Moyennes générales par classe
        $moyennes_classes = Classe::with(['etudiants.notes.matiere'])->get()->map(function ($classe) {
            $etudiants = $classe->etudiants;
            if ($etudiants->isEmpty()) {
                return ['nom' => $classe->nom, 'moyenne_generale' => null];
            }

            $totalMoyennes = $etudiants->map(function ($etudiant) {
                $notes = $etudiant->notes;
                $moyennesMatieres = $notes->groupBy('matiere_id')->map(function ($matiereNotes) {
                    $devoir = $matiereNotes->where('type_evaluation', 'devoir')->first();
                    $examen = $matiereNotes->where('type_evaluation', 'examen')->first();
                    if ($devoir && $examen) {
                        return ($devoir->valeur * 0.4) + ($examen->valeur * 0.6);
                    }
                    return null;
                })->filter()->avg();

                return $moyennesMatieres ?: null;
            })->filter()->avg();

            return [
                'nom' => $classe->nom,
                'moyenne_generale' => $totalMoyennes ? number_format($totalMoyennes, 2) : null,
            ];
        });

        return response()->json([
            'message' => 'Statistiques du tableau de bord récupérées avec succès.',
            'stats' => $stats,
            'moyennes_classes' => $moyennes_classes,
            'status' => true
        ], 200);
    }
}
