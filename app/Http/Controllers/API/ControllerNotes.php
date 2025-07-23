<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Matiere;
use App\Models\Note;
use Illuminate\Http\Request;

class ControllerNotes extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'etudiant_id' => 'required|exists:students,id',
            'matiere_id' => 'required|exists:matieres,id',
            'valeur' => 'required|numeric|min:0|max:20',
            'semestre' => 'required|in:S1,S2',
            'type_evaluation' => 'required|in:devoir,examen',
        ]);

        // Vérifier si une note existe déjà pour cette combinaison
        $existingNote = Note::where([
            'etudiant_id' => $request->etudiant_id,
            'matiere_id' => $request->matiere_id,
            'semestre' => $request->semestre,
            'type_evaluation' => $request->type_evaluation,
        ])->first();

        if ($existingNote) {
            return response()->json([
                'message' => 'Une note existe déjà pour cette évaluation.',
                'status' => false
            ], 400);
        }

        $note = Note::create($request->only(['etudiant_id', 'matiere_id', 'valeur', 'semestre', 'type_evaluation']));

        return response()->json([
            'message' => 'Note saisie avec succès.',
            'note' => $note,
            'status' => true
        ], 201);
    }

    public function getStudentNotes($etudiantId, $semestre)
    {
        $etudiant = Etudiant::with(['notes.matiere'])->findOrFail($etudiantId);
        $notes = $etudiant->notes->where('semestre', $semestre);

        $notesByMatiere = [];
        foreach ($notes as $note) {
            $matiereNom = $note->matiere->nom;
            if (!isset($notesByMatiere[$matiereNom])) {
                $notesByMatiere[$matiereNom] = [];
            }
            $notesByMatiere[$matiereNom][$note->type_evaluation] = $note->valeur;
        }

        return response()->json([
            'message' => 'Notes récupérées avec succès.',
            'etudiant' => [
                'id' => $etudiant->id,
                'nom' => $etudiant->nom,
                'prenom' => $etudiant->prenom,
            ],
            'semestre' => $semestre,
            'notes' => $notesByMatiere,
            'status' => true
        ], 200);
    }

    public function calculateBulletin($etudiantId, $semestre)
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

        // Calcul du rang
        $classeId = $etudiant->classe_id;
        $classMoyennes = Etudiant::where('classe_id', $classeId)
            ->with(['notes.matiere'])
            ->get()
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
            return abs($moyenne - $moyenneGenerale) < 0.01; // Tolérance pour éviter les erreurs d'arrondi
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
        if ($moyenne >= 16) return 'Excellent';
        if ($moyenne >= 14) return 'Très Bien';
        if ($moyenne >= 12) return 'Bien';
        if ($moyenne >= 10) return 'Assez Bien';
        return 'Passable';
    }

    private function getAppreciation($moyenne)
    {
        if ($moyenne >= 16) return 'Excellent effort, continuez ainsi !';
        if ($moyenne >= 14) return 'Très bon travail, bravo !';
        if ($moyenne >= 12) return 'Bon travail, à perfectionner.';
        if ($moyenne >= 10) return 'Satisfaisant, effort à maintenir.';
        return 'À améliorer, travail supplémentaire requis.';
    }
}
