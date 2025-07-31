<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enseignant;
use App\Models\Matiere;
use App\Models\Classe;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;



class ControllerEnseignants extends Controller
{
    public function storeEnseignant(Request $request)
    {
        try {
            \Log::info('Validation des données : ' . json_encode($request->all()));
            $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            $year = date('Y');
            $lastMatricule = Enseignant::max('matricule');
            $nextNumber = $lastMatricule ? (int) substr($lastMatricule, -3) + 1 : 1;
            $matricule = 'E' . $year . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $enseignant = Enseignant::create(array_merge(
                $request->only(['nom', 'prenom', 'email']),
                ['matricule' => $matricule]
            ));
            \Log::info('Enseignant créé : ' . $enseignant->id);

            $role = Role::where('name', 'enseignant')->firstOrFail();
            \Log::info('Rôle trouvé : ' . $role->id);

            $user = User::create([
                'name' => $request->prenom . ' ' . $request->nom,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $role->id,
                'etudiant_id' => null,
            ]);
            \Log::info('Utilisateur créé : ' . $user->id);

            // Lier l'enseignant à l'utilisateur
            $enseignant->update(['user_id' => $user->id]);

            // Générer un token Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Enseignant créé avec succès.',
                'enseignant' => [
                    'id' => $enseignant->id,
                    'nom' => $enseignant->nom,
                    'prenom' => $enseignant->prenom,
                    'email' => $enseignant->email,
                    'matricule' => $enseignant->matricule,
                ],
                'token' => $token,
                'status' => true
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Erreur création enseignant: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la création de l\'enseignant : ' . $e->getMessage(),
                'status' => false
            ], 500);
        }
    }
    public function affecterClasse(Request $request, $enseignant_id)
    {
        $request->validate([
            'classe_id' => 'required|exists:classes,id',
        ]);

        $enseignant = User::findOrFail($enseignant_id);
        $classe = Classe::findOrFail($request->classe_id);

        $enseignant->classes()->attach($classe->id);

        return response()->json([
            'message' => 'Classe affectée à l\'enseignant avec succès.',
            'enseignant' => $enseignant->load('classes'),
            'status' => true
        ], 201);
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
            // Récupère l'enseignant avec son utilisateur associé
            $enseignant = Enseignant::with('user')->findOrFail($id);

            // Validation des données
            $request->validate([
                'nom' => 'sometimes|required|string|max:255',
                'prenom' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . ($enseignant->user ? $enseignant->user->id : 'NULL'),
                'matricule' => 'sometimes|required|string|unique:enseignants,matricule,' . $enseignant->id,
            ]);

            // Mettre à jour les champs de l'enseignant
            $enseignant->update($request->only(['nom', 'prenom', 'email', 'matricule']));

            // Mettre à jour l'utilisateur associé si existant
            if ($enseignant->user) {
                $userData = [];
                if ($request->filled('prenom') || $request->filled('nom')) {
                    $userData['name'] = trim("{$request->input('prenom', $enseignant->prenom)} {$request->input('nom', $enseignant->nom)}");
                }
                if ($request->filled('email')) {
                    $userData['email'] = $request->input('email');
                }

                if (!empty($userData)) {
                    $enseignant->user->update($userData);
                }
            }

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
            // Trouver l'enseignant
            $enseignant = Enseignant::findOrFail($id);

            // Vérifier si l'utilisateur connecté est un admin (facultatif, car géré par le middleware)
            if (auth()->user()->role->name !== 'admin') {
                return response()->json([
                    'message' => 'Unauthorized: Seuls les admins peuvent supprimer un enseignant.',
                    'status' => false
                ], 403);
            }

            // Trouver l'utilisateur associé via la relation
            $user = $enseignant->user;

            // Supprimer l'enseignant
            $enseignant->delete();

            // Supprimer l'utilisateur associé s'il existe
            if ($user) {
                $user->delete();
                Log::info('Utilisateur supprimé : ' . $user->id);
            }

            return response()->json([
                'message' => 'Enseignant et utilisateur associé supprimés avec succès.',
                'status' => true
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'enseignant : ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la suppression de l\'enseignant : ' . $e->getMessage(),
                'status' => false
            ], 500);
        }
    }
}
