<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use Illuminate\Http\Request;

class ControllerEtudiants extends Controller
{
        public function inscrire(Request $request)
        {
            $request->validate([
                'prenom'=> 'required|string|max:255',
                'nom' => 'required|string|max:255',
                'date_de_naissance' => 'required|date',
                'classe_id' => 'nullable|exists:classes,id',
            ]);

            $matricule = 'S' . date('Y') . str_pad(Etudiant::count() + 1, 3, '0', STR_PAD_LEFT);

            $etudiant = Etudiant::create([
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                'date_de_naissance' => $request->date_de_naissance,
                'matricule' => $matricule,
                'classe_id' => $request->classe_id,
            ]);

            return response()->json([
                'message' => 'Etudiant inscrit avec succès.',
                'etudiant' => [
                    'id' => $etudiant->id,
                    'prenom' => $etudiant->prenom,
                    'nom' => $etudiant->nom,
                    'matricule' => $etudiant->matricule,
                    'classe' => $etudiant->classe ? $etudiant->classe->nom : null,
                ],
                'status' => true
            ],201);
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
            ],200);
        }
}
