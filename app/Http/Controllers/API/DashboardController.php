<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Enseignant;
use App\Models\Classe;
use App\Models\Note;
use App\Models\Matiere;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index($semestre = null)
    {
        $user = auth()->user();
        if (!$user || $user->role->name !== 'admin') {
            return response()->json(['message' => 'Unauthorized ! Only admins can view the dashboard', 'status' => false], 403);
        }

        // Si aucun semestre spécifié, utiliser tous les semestres
        if ($semestre === null) {
            $semestres = ['S1', 'S2'];
            $noteCount = Note::count(); // Total des notes
            $hasData = $noteCount > 0;
            $message = $hasData ? 'Statistiques globales du tableau de bord récupérées avec succès.' : 'Aucune donnée disponible pour aucun semestre, seules les statistiques globales sont affichées.';
        } else {
            // Valider le paramètre semestre
            if (!in_array($semestre, ['S1', 'S2'])) {
                return response()->json(['message' => 'Semestre invalide. Utilisez S1 ou S2.', 'status' => false], 404);
            }
            $semestres = [$semestre];
            $noteCount = Note::where('semestre', $semestre)->count();
            $hasData = $noteCount > 0;
            $message = $hasData ? 'Statistiques du tableau de bord récupérées avec succès.' : "Aucune donnée disponible pour le semestre $semestre, seules les statistiques globales sont affichées.";
        }

        // Statistiques globales (toujours affichées, indépendantes du semestre)
        $stats = [
            'nombre_eleves' => Etudiant::count(),
            'nombre_enseignants' => Enseignant::count(),
            'nombre_classes' => Classe::count(),
            'nombre_parents' => User::where('role_id', function ($query) {
                $query->select('id')->from('roles')->where('name', 'parent');
            })->count(),
        ];

        // Réponse de base avec stats
        $response = [
            'message' => $message,
            'stats' => $stats,
            'status' => true
        ];

        // Ajouter les sections dépendantes du semestre uniquement s'il y a des données
        if ($hasData) {
            $moyennes_classes = Classe::with(['etudiants.notes.matiere'])->get()->map(function ($classe) use ($semestres) {
                $etudiants = $classe->etudiants;

                if ($etudiants->isEmpty()) {
                    return ['nom' => $classe->nom, 'moyenne' => null];
                }

                $totalMoyennes = collect();
                foreach ($semestres as $s) {
                    $moyennes = $etudiants->map(function ($etudiant) use ($s) {
                        $notes = $etudiant->notes->where('semestre', $s);
                        if ($notes->isEmpty()) return null;
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
                    if ($moyennes !== null) {
                        $totalMoyennes->push($moyennes);
                    }
                }
                $moyenne = $totalMoyennes->avg() ? number_format($totalMoyennes->avg(), 2) : null;
                return ['nom' => $classe->nom, 'moyenne' => $moyenne];
            });

            $top_eleves = Etudiant::with(['notes.matiere'])
                ->get()
                ->map(function ($etudiant) use ($semestres) {
                    $notes = $etudiant->notes->whereIn('semestre', $semestres);
                    if ($notes->isEmpty()) return null;
                    $moyenneGenerale = $notes->groupBy('matiere_id')->map(function ($matiereNotes) {
                        $devoir = $matiereNotes->where('type_evaluation', 'devoir')->first();
                        $examen = $matiereNotes->where('type_evaluation', 'examen')->first();
                        if ($devoir && $examen) {
                            return ($devoir->valeur * 0.4) + ($examen->valeur * 0.6);
                        }
                        return null;
                    })->filter()->avg();
                    return [
                        'id' => $etudiant->id,
                        'nom' => $etudiant->nom,
                        'prenom' => $etudiant->prenom,
                        'moyenne_generale' => $moyenneGenerale ? number_format($moyenneGenerale, 2) : null,
                    ];
                })
                ->filter()
                ->sortByDesc('moyenne_generale')
                ->take(5);

            $notes_par_semestre = [];
            foreach ($semestres as $s) {
                $notes_par_semestre[$s] = Note::where('semestre', $s)->count();
            }

            $matieres_par_enseignant = Enseignant::with('matieres')->get()->map(function ($enseignant) {
                return [
                    'id' => $enseignant->id,
                    'nom' => $enseignant->nom,
                    'prenom' => $enseignant->prenom,
                    'matieres' => $enseignant->matieres->pluck('nom')->unique()->values(),
                ];
            });

            $response['moyennes_classes'] = $moyennes_classes;
            $response['top_eleves'] = $top_eleves->values();
            $response['notes_par_semestre'] = $notes_par_semestre;
            $response['matieres_par_enseignant'] = $matieres_par_enseignant;
        }

        return response()->json($response, 200);
    }
}
