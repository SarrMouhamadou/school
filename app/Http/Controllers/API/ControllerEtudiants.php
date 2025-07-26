<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Classe;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ControllerEtudiants extends Controller
{
    public function inscrire(Request $request)
    {
        \Log::info('Début de l\'inscription : ' . json_encode($request->all()));
        try {
            // Validation des données
            $request->validate([
                'prenom' => 'required|string|max:255',
                'nom' => 'required|string|max:255',
                'date_de_naissance' => 'required|date|before:today',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'classe' => 'required|string|exists:classes,nom',
            ]);
            \Log::info('Validation réussie');

            // Génération du matricule
            $matricule = 'S' . date('Y') . str_pad(Etudiant::count() + 1, 3, '0', STR_PAD_LEFT);
            \Log::info('Matricule généré : ' . $matricule);

            // Recherche de la classe
            $classe = Classe::where('nom', $request->classe)->firstOrFail();
            \Log::info('Classe trouvée : ' . $classe->id);

            // Création de l'étudiant
            $etudiant = Etudiant::create([
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                'date_de_naissance' => $request->date_de_naissance,
                'matricule' => $matricule,
                'classe_id' => $classe->id,
            ]);
            \Log::info('Étudiant créé : ' . $etudiant->id);

            // Création de l'utilisateur
            $user = User::create([
                'name' => $request->prenom . ' ' . $request->nom,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'etudiant_id' => $etudiant->id,
            ]);
            \Log::info('Utilisateur créé : ' . $user->id);

            // Association du rôle
            $role = Role::where('name', 'eleve')->first();
            if ($role) {
                $user->role()->associate($role);
                $user->save();
                \Log::info('Rôle associé : ' . $role->id);
            } else {
                \Log::error('Rôle "eleve" non trouvé');
            }

            // Génération du token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Réponse JSON
            return response()->json([
                'message' => 'Étudiant inscrit avec succès.',
                'etudiant' => $etudiant,
                'user' => $user,
                'token' => $token,
                'status' => true
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur de validation : ' . json_encode($e->errors()));
            return response()->json(['message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Erreur inscription étudiant : ' . $e->getMessage());
            return response()->json(['message' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

    public function listerEtudiants()
    {
        $etudiants = Etudiant::with('classe')->get();
        return response()->json([
            'message' => 'Etudiants récupérés avec succès.',
            'etudiants' => $etudiants->map(function ($etudiant) {
                return [
                    'id' => $etudiant->id,
                    'prenom' => $etudiant->prenom,
                    'nom' => $etudiant->nom,
                    'matricule' => $etudiant->matricule,
                    'classe' => $etudiant->classe ? $etudiant->classe->nom : null,
                ];
            }),
            'status' => true
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $etudiant = Etudiant::find($id);

        if (!$etudiant) {
            return response()->json([
                'message' => 'Étudiant non trouvé.',
                'status' => false
            ], 404);
        }

        $request->validate([
            'prenom' => 'sometimes|required|string|max:255',
            'nom' => 'sometimes|required|string|max:255',
            'date_de_naissance' => 'sometimes|required|date|before:today',
            'classe' => 'nullable|string|max:255',
        ]);

        $classeNomEntree = $request->input('classe');
        $classe = $classeNomEntree ? Classe::whereRaw('LOWER(nom) = LOWER(?)', [$classeNomEntree])->first() : null;

        if ($classeNomEntree && !$classe) {
            return response()->json([
                'message' => 'Erreur : la classe ' . $classeNomEntree . ' n\'existe pas.',
                'status' => false
            ], 400);
        }

        $classeId = $classe ? $classe->id : null;

        $etudiant->update([
            'prenom' => $request->input('prenom', $etudiant->prenom),
            'nom' => $request->input('nom', $etudiant->nom),
            'date_de_naissance' => $request->input('date_de_naissance', $etudiant->date_de_naissance),
            'classe_id' => $classeId,
        ]);

        return response()->json([
            'message' => 'Étudiant mis à jour avec succès.',
            'etudiant' => [
                'id' => $etudiant->id,
                'prenom' => $etudiant->prenom,
                'nom' => $etudiant->nom,
                'matricule' => $etudiant->matricule,
                'classe' => $etudiant->classe ? $etudiant->classe->nom : null,
            ],
            'status' => true
        ], 200);
    }

    public function destroy($id)
    {
        try {
            // Trouver l'étudiant
            $etudiant = Etudiant::findOrFail($id);

            // Vérifier si l'utilisateur connecté est un admin (facultatif, car géré par le middleware)
            if (auth()->user()->role->name !== 'admin') {
                return response()->json([
                    'message' => 'Unauthorized: Seuls les admins peuvent supprimer un étudiant.',
                    'status' => false
                ], 403);
            }

            // Trouver l'utilisateur associé via la relation
            $user = $etudiant->user;

            // Supprimer l'étudiant
            $etudiant->delete();

            // Supprimer l'utilisateur associé s'il existe
            if ($user) {
                $user->delete();
                Log::info('Utilisateur supprimé : ' . $user->id);
            }

            return response()->json([
                'message' => 'Étudiant et utilisateur associé supprimés avec succès.',
                'status' => true
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'étudiant : ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la suppression de l\'étudiant : ' . $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    public function affecterClasse(Request $request, $id)
    {
        $request->validate([
            'classe' => 'required|string|exists:classes,nom',
        ]);

        $etudiant = Etudiant::findOrFail($id);
        $classe = Classe::whereRaw('LOWER(nom) = LOWER(?)', [$request->classe])->firstOrFail();

        $etudiant->update(['classe_id' => $classe->id]);

        return response()->json([
            'message' => 'Classe affectée avec succès.',
            'etudiant' => [
                'id' => $etudiant->id,
                'prenom' => $etudiant->prenom,
                'nom' => $etudiant->nom,
                'matricule' => $etudiant->matricule,
                'classe' => $etudiant->classe->nom,
            ],
            'status' => true
        ], 200);
    }
}
