<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ControllerNotes extends Controller
{
    public function index(Request $request)
    {
        $query = Note::with(['etudiant', 'matiere']);

        if ($request->has('semestre') && in_array($request->semestre, ['S1', 'S2'])) {
            $query->where('semestre', $request->semestre);
        }

        if ($request->has('etudiant_id') && $request->etudiant_id) {
            $query->where('etudiant_id', $request->etudiant_id);
        }

        $notes = $query->get();

        return response()->json([
            'message' => 'Notes récupérées avec succès.',
            'notes' => $notes->map(function ($note) {
                return [
                    'id' => $note->id,
                    'etudiant' => [
                        'id' => $note->etudiant->id,
                        'nom' => $note->etudiant->nom,
                        'prenom' => $note->etudiant->prenom,
                    ],
                    'matiere' => [
                        'id' => $note->matiere->id,
                        'nom' => $note->matiere->nom,
                    ],
                    'valeur' => $note->valeur,
                    'semestre' => $note->semestre,
                    'type_evaluation' => $note->type_evaluation,
                ];
            }),
            'status' => true
        ], 200);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role->name !== 'enseignant') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $request->validate([
                'etudiant_id' => 'required|exists:students,id',
                'matiere_id' => 'required|exists:matieres,id',
                'valeur' => 'required|numeric|min:0|max:20',
                'semestre' => 'required|in:S1,S2',
                'type_evaluation' => 'required|in:devoir,examen',
            ]);

            // Vérifier si l'enseignant est autorisé pour cette matière
            $enseignant = Enseignant::where('user_id', $user->id)->first();
            if (!$enseignant || !$enseignant->matieres()->where('matiere_id', $request->matiere_id)->exists()) {
                return response()->json(['message' => 'Vous n\'êtes pas autorisé à saisir des notes pour cette matière.'], 403);
            }

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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Les données fournies sont invalides.',
                'errors' => $e->errors(),
                'status' => false
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || $user->role->name !== 'enseignant') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $note = Note::findOrFail($id);

        try {
            $request->validate([
                'etudiant_id' => 'sometimes|exists:students,id',
                'matiere_id' => 'sometimes|exists:matieres,id',
                'valeur' => 'sometimes|required|numeric|min:0|max:20',
                'semestre' => 'sometimes|in:S1,S2',
                'type_evaluation' => 'sometimes|in:devoir,examen',
            ]);

            // Vérifier si l'enseignant est autorisé pour cette matière
            $enseignant = Enseignant::where('user_id', $user->id)->first();
            $matiereId = $request->matiere_id ?? $note->matiere_id;
            if (!$enseignant || !$enseignant->matieres()->where('matiere_id', $matiereId)->exists()) {
                return response()->json(['message' => 'Vous n\'êtes pas autorisé à modifier des notes pour cette matière.'], 403);
            }

            $existingNote = Note::where([
                'etudiant_id' => $request->etudiant_id ?? $note->etudiant_id,
                'matiere_id' => $matiereId,
                'semestre' => $request->semestre ?? $note->semestre,
                'type_evaluation' => $request->type_evaluation ?? $note->type_evaluation,
            ])->where('id', '!=', $id)->first();

            if ($existingNote) {
                return response()->json([
                    'message' => 'Une note existe déjà pour cette évaluation.',
                    'status' => false
                ], 400);
            }

            $note->update($request->only(['etudiant_id', 'matiere_id', 'valeur', 'semestre', 'type_evaluation']));

            return response()->json([
                'message' => 'Note mise à jour avec succès.',
                'note' => $note,
                'status' => true
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Les données fournies sont invalides.',
                'errors' => $e->errors(),
                'status' => false
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user || $user->role->name !== 'enseignant') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $note = Note::findOrFail($id);

        try {
            // Vérifier si l'enseignant est autorisé pour cette matière
            $enseignant = Enseignant::where('user_id', $user->id)->first();
            if (!$enseignant || !$enseignant->matieres()->where('matiere_id', $note->matiere_id)->exists()) {
                return response()->json(['message' => 'Vous n\'êtes pas autorisé à supprimer des notes pour cette matière.'], 403);
            }

            $semestre = $note->semestre;
            $etudiantId = $note->etudiant_id;
            $note->delete();

            $remainingNotes = Note::where('etudiant_id', $etudiantId)->where('semestre', $semestre)->get();
            $hasCompleteNotes = $remainingNotes->groupBy('matiere_id')->every(function ($matiereNotes) {
                return $matiereNotes->where('type_evaluation', 'devoir')->first() && $matiereNotes->where('type_evaluation', 'examen')->first();
            });
            if (!$hasCompleteNotes) {
                return response()->json([
                    'message' => 'Note supprimée, mais le semestre est maintenant incomplet.',
                    'status' => true
                ], 200);
            }

            return response()->json([
                'message' => 'Note supprimée avec succès.',
                'status' => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    public function getStudentNotes($etudiantId, $semestre)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role->name, ['eleve', 'parent'])) {
            return response()->json(['message' => 'Accès non autorisé.', 'status' => false], 403);
        }

        $allowedEtudiantId = $user->role->name === 'eleve' ? $user->etudiant_id : ($user->etudiant_id ?? $this->getParentStudentId($user->id));
        if ($etudiantId != $allowedEtudiantId) {
            return response()->json(['message' => 'Accès non autorisé à cet étudiant.', 'status' => false], 403);
        }

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
        $user = auth()->user();
        if (!$user || !in_array($user->role->name, ['eleve', 'parent'])) {
            return response()->json(['message' => 'Accès non autorisé.', 'status' => false], 403);
        }

        $allowedEtudiantId = $user->role->name === 'eleve' ? $user->etudiant_id : ($user->etudiant_id ?? $this->getParentStudentId($user->id));
        if ($etudiantId != $allowedEtudiantId) {
            return response()->json(['message' => 'Accès non autorisé à cet étudiant.', 'status' => false], 403);
        }

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

    public function getBulletins($userId)
    {
        $user = auth()->user();
        if (!$user || $user->id != $userId || !in_array($user->role->name, ['eleve', 'parent'])) {
            return response()->json(['message' => 'Accès non autorisé.', 'status' => false], 403);
        }

        $etudiantId = $user->role->name === 'eleve' ? $user->etudiant_id : ($user->etudiant_id ?? $this->getParentStudentId($user->id));
        if (!$etudiantId) {
            return response()->json(['message' => 'Aucun étudiant associé.', 'status' => false], 404);
        }

        $bulletins = [];
        foreach (['S1', 'S2'] as $semestre) {
            $bulletin = $this->calculateBulletin($etudiantId, $semestre)->getData();
            if ($bulletin->status) {
                $bulletins[$semestre] = $bulletin;
            }
        }

        return response()->json([
            'message' => 'Bulletins récupérés avec succès.',
            'bulletins' => $bulletins,
            'status' => true
        ], 200);
    }

    public function downloadBulletin($userId, $semestre)
    {
        $user = auth()->user();
        if (!$user || $user->id != $userId || !in_array($user->role->name, ['eleve', 'parent'])) {
            return response()->json(['message' => 'Accès non autorisé.', 'status' => false], 403);
        }

        $etudiantId = $user->role->name === 'eleve' ? $user->etudiant_id : ($user->etudiant_id ?? $this->getParentStudentId($user->id));
        if (!$etudiantId) {
            return response()->json(['message' => 'Aucun étudiant associé.', 'status' => false], 404);
        }

        $bulletin = $this->calculateBulletin($etudiantId, $semestre)->getData();
        if (!$bulletin->status) {
            return response()->json($bulletin, 400);
        }

        $pdf = \PDF::loadView('bulletin.pdf', ['bulletin' => $bulletin]);
        return $pdf->download('bulletin_' . $etudiantId . '_S' . $semestre . '.pdf');

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

    private function getParentStudentId($userId)
    {
        return DB::table('parent_student')->where('user_id', $userId)->value('student_id');
    }
}
