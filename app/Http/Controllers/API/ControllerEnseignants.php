<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enseignant;
use App\Models\Matiere;
use App\Models\Classe;

class ControllerEnseignants extends Controller
{
    public function storeEnseignant(Request $request)
    {
        try {
            $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:enseignants,email',
            ]);

            // Générer un matricule unique (ex. : EYYYYNNN, où YYYY est l'année et NNN un numéro séquentiel)
            $year = date('Y');
            $lastMatricule = Enseignant::max('matricule');
            $nextNumber = $lastMatricule ? (int) substr($lastMatricule, -3) + 1 : 1;
            $matricule = 'E' . $year . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $enseignant = Enseignant::create(array_merge(
                $request->only(['nom', 'prenom', 'email']),
                ['matricule' => $matricule]
            ));

            return response()->json([
                'message' => 'Enseignant créé avec succès.',
                'enseignant' => [
                    'id' => $enseignant->id,
                    'nom' => $enseignant->nom,
                    'prenom' => $enseignant->prenom,
                    'email' => $enseignant->email,
                    'matricule' => $enseignant->matricule,
                ],
                'status' => true
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de l\'enseignant : ' . $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    public function affecterMatiere(Request $request)
    {
        $request->validate([
            'matricule' => 'required|string|exists:enseignants,matricule',
            'nom_matiere' => 'required|string|exists:matieres,nom',
            'classe_id' => 'required|exists:classes,id',
        ]);

        $enseignant = Enseignant::where('matricule', $request->matricule)->firstOrFail();
        $matiere = Matiere::where('nom', $request->nom_matiere)->firstOrFail();

        // Vérifier si l'affectation existe déjà
        if ($enseignant->matieres()->where('matiere_id', $matiere->id)->wherePivot('classe_id', $request->classe_id)->exists()) {
            return response()->json([
                'message' => 'Cette affectation existe déjà.',
                'status' => false
            ], 400);
        }

        $enseignant->matieres()->attach($matiere->id, ['classe_id' => $request->classe_id]);

        return response()->json([
            'message' => 'Matière affectée avec succès.',
            'status' => true
        ], 200);
    }

    public function index()
    {
        $enseignants = Enseignant::with([
            'matieres' => function ($query) {
                $query->withPivot('classe_id')->with([
                    'classe' => function ($query) {
                        $query->select('id', 'nom'); // Optimiser en sélectionnant uniquement les colonnes nécessaires
                    }
                ]);
            }
        ])->get();

        return response()->json([
            'message' => 'Enseignants récupérés avec succès.',
            'enseignants' => $enseignants->map(function ($enseignant) {
                return [
                    'id' => $enseignant->id,
                    'matricule' => $enseignant->matricule,
                    'nom' => $enseignant->nom,
                    'prenom' => $enseignant->prenom,
                    'email' => $enseignant->email,
                    'matieres' => $enseignant->matieres->map(function ($matiere) use ($enseignant) {
                        // Récupérer classe_id depuis la table pivot
                        $pivot = $enseignant->matieres()->where('matiere_id', $matiere->id)->first()->pivot;
                        $classe = Classe::find($pivot->classe_id);

                        return [
                            'id' => $matiere->id,
                            'nom' => $matiere->nom,
                            'coefficient' => $matiere->coefficient,
                            'nom_classe' => $classe ? $classe->nom : null,
                        ];
                    }),
                ];
            }),
            'status' => true
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $enseignant = Enseignant::findOrFail($id);

            $request->validate([
                'nom' => 'sometimes|required|string|max:255',
                'prenom' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:enseignants,email,' . $enseignant->id,
                'matricule' => 'sometimes|required|string|unique:enseignants,matricule,' . $enseignant->id,
            ]);

            $enseignant->update($request->only(['nom', 'prenom', 'email', 'matricule']));

            return response()->json([
                'message' => 'Enseignant mis à jour avec succès.',
                'enseignant' => [
                    'id' => $enseignant->id,
                    'matricule' => $enseignant->matricule,
                    'nom' => $enseignant->nom,
                    'prenom' => $enseignant->prenom,
                    'email' => $enseignant->email,
                ],
                'status' => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $enseignant = Enseignant::findOrFail($id);

            if ($enseignant->matieres()->exists()) {
                return response()->json([
                    'message' => 'Impossible de supprimer : cet enseignant est affecté à une matière.',
                    'status' => false
                ], 400);
            }

            $enseignant->delete();

            return response()->json([
                'message' => 'Enseignant supprimé avec succès.',
                'status' => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage(),
                'status' => false
            ], 500);
        }
    }
}
