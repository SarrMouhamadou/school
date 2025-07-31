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
use PDF;

class DashboardController extends Controller
{
    /**
     * Statistiques des notes pour le dashboard admin (sans exposer la liste brute des notes)
     */
    public function noteStats(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role->name !== 'admin') {
            return response()->json(['message' => 'Unauthorized ! Only admins can view the dashboard', 'status' => false], 403);
        }

        $semestres = ['S1', 'S2'];
        $noteCount = Note::count();
        $hasData = $noteCount > 0;
        $message = $hasData ? 'Statistiques notes récupérées avec succès.' : 'Aucune note en base.';

        // Statistiques globales
        $stats = [
            'total_notes' => $noteCount,
            'notes_par_semestre' => [
                'S1' => Note::where('semestre', 'S1')->count(),
                'S2' => Note::where('semestre', 'S2')->count(),
            ],
        ];

        // Moyennes par classe
        $moyennes_classes = Classe::with(['etudiants.notes.matiere'])->get()->map(function ($classe) use ($semestres) {
            $etudiants = $classe->etudiants;
            if ($etudiants->isEmpty()) {
                return ['nom' => $classe->nom, 'moyenne' => null];
            }
            $totalMoyennes = collect();
            foreach ($semestres as $s) {
                $moyennes = $etudiants->map(function ($etudiant) use ($s) {
                    $notes = $etudiant->notes->where('semestre', $s);
                    if ($notes->isEmpty())
                        return null;
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

        // Top 5 élèves toutes classes confondues
        $top_eleves = Etudiant::with(['notes.matiere'])
            ->get()
            ->map(function ($etudiant) use ($semestres) {
                $notes = $etudiant->notes->whereIn('semestre', $semestres);
                if ($notes->isEmpty())
                    return null;
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
            ->take(5)
            ->values();

        // Matières par enseignant
        $matieres_par_enseignant = Enseignant::with('matieres')->get()->map(function ($enseignant) {
            return [
                'id' => $enseignant->id,
                'nom' => $enseignant->nom,
                'prenom' => $enseignant->prenom,
                'matieres' => $enseignant->matieres->pluck('nom')->unique()->values(),
            ];
        });

        return response()->json([
            'message' => $message,
            'stats' => $stats,
            'moyennes_classes' => $moyennes_classes,
            'top_eleves' => $top_eleves,
            'matieres_par_enseignant' => $matieres_par_enseignant,
            'status' => true
        ], 200);
    }

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
                        if ($notes->isEmpty())
                            return null;
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
                    if ($notes->isEmpty())
                        return null;
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

    public function downloadClassBulletins($classeId, $semestre)
    {
        if (auth()->user()->role->name !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $classe = Classe::findOrFail($classeId);
        $etudiants = $classe->etudiants;
        $zip = new \ZipArchive();
        $zipFileName = 'bulletins_classe_' . $classeId . '_S' . $semestre . '.zip';
        $zip->open($zipFileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($etudiants as $etudiant) {
            $bulletin = $this->calculateBulletinForAdmin($etudiant->id, $semestre)->getData();
            if ($bulletin->status) {
                $pdf = PDF::loadView('bulletin.pdf', ['bulletin' => $bulletin]);
                $pdfContent = $pdf->output();
                $zip->addFromString('bulletin_' . $etudiant->id . '_S' . $semestre . '.pdf', $pdfContent);
            }
        }

        $zip->close();
        return response()->download($zipFileName)->deleteFileAfterSend(true);
    }

    private function calculateBulletinForAdmin($etudiantId, $semestre)
    {
        $etudiant = Etudiant::with(['notes.matiere'])->findOrFail($etudiantId);
        $notes = $etudiant->notes->where('semestre', $semestre);

        $moyennesMatieres = [];
        $totalPoints = 0;
        $totalWeighted = 0;

        foreach ($notes->groupBy('matiere_id') as $matiereId => $matiereNotes) {
            $devoir = $matiereNotes->where('type_evaluation', 'devoir')->first();
            $examen = $matiereNotes->where('type_evaluation', 'examen')->first();

            if ($devoir && $examen) {
                $moyenneMatiere = ($devoir->valeur * 0.4) + ($examen->valeur * 0.6);
                $matiere = Matiere::find($matiereId);
                $moyennesMatieres[$matiere->nom] = number_format($moyenneMatiere, 2);

                $totalPoints += $moyenneMatiere * $matiere->coefficient;
                $totalWeighted += $matiere->coefficient;
            }
        }

        if ($totalWeighted == 0) {
            return response()->json([
                'message' => 'Aucune note disponible pour ce semestre.',
                'status' => false
            ], 400);
        }

        $moyenneGenerale = $totalPoints / $totalWeighted;
        $mention = $this->getMention($moyenneGenerale);

        $classeId = $etudiant->classe_id;
        $classMoyennes = Etudiant::where('classe_id', $classeId)
            ->with(['notes.matiere'])
            ->get()
            ->filter(function ($e) use ($semestre) {
                $notes = $e->notes->where('semestre', $semestre);
                $hasCompleteNotes = $notes->groupBy('matiere_id')->every(function ($matiereNotes) {
                    return $matiereNotes->where('type_evaluation', 'devoir')->first() && $matiereNotes->where('type_evaluation', 'examen')->first();
                });
                return $hasCompleteNotes;
            })
            ->map(function ($e) use ($semestre) {
                $notes = $e->notes->where('semestre', $semestre);
                $totalPoints = 0;
                $totalWeighted = 0;
                foreach ($notes->groupBy('matiere_id') as $matiereId => $matiereNotes) {
                    $devoir = $matiereNotes->where('type_evaluation', 'devoir')->first();
                    $examen = $matiereNotes->where('type_evaluation', 'examen')->first();
                    if ($devoir && $examen) {
                        $moyenneMatiere = ($devoir->valeur * 0.4) + ($examen->valeur * 0.6);
                        $matiere = Matiere::find($matiereId);
                        $totalPoints += $moyenneMatiere * $matiere->coefficient;
                        $totalWeighted += $matiere->coefficient;
                    }
                }
                return $totalWeighted > 0 ? $totalPoints / $totalWeighted : 0;
            })
            ->sortByDesc(function ($moyenne) {
                return $moyenne;
            })
            ->values();

        $rang = $classMoyennes->search(function ($moyenne) use ($moyenneGenerale) {
            return abs($moyenne - $moyenneGenerale) < 0.01;
        }) + 1;

        return response()->json([
            'message' => 'Bulletin calculé avec succès.',
            'etudiant' => [
                'id' => $etudiant->id,
                'nom' => $etudiant->nom,
                'prenom' => $etudiant->prenom,
            ],
            'semestre' => $semestre,
            'moyennes_matieres' => $moyennesMatieres,
            'moyenne_generale' => number_format($moyenneGenerale, 2),
            'mention' => $mention,
            'rang' => $rang,
            'appreciation' => $this->getAppreciation($moyenneGenerale),
            'status' => true
        ], 200);
    }

    private function getMention($moyenne)
    {
        if ($moyenne >= 16)
            return 'Excellent';
        if ($moyenne >= 14)
            return 'Très Bien';
        if ($moyenne >= 12)
            return 'Bien';
        if ($moyenne >= 10)
            return 'Assez Bien';
        return 'Passable';
    }

    private function getAppreciation($moyenne)
    {
        if ($moyenne >= 16)
            return 'Excellent effort, continuez ainsi !';
        if ($moyenne >= 14)
            return 'Très bon travail, bravo !';
        if ($moyenne >= 12)
            return 'Bon travail, à perfectionner.';
        if ($moyenne >= 10)
            return 'Satisfaisant, effort à maintenir.';
        return 'À améliorer, travail supplémentaire requis.';
    }
}
