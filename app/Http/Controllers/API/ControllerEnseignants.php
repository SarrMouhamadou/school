<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ControllerEnseignants extends Controller
{
    public function storeEnseignant(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:enseignants,email',
        ]);

        $enseignant = Enseignant::create($request->only(['nom', 'prenom', 'email']));

        return response()->json([
            'message' => 'Enseignant créé avec succès.',
            'enseignant' => [
                'id' => $enseignant->id,
                'nom' => $enseignant->nom,
                'prenom' => $enseignant->prenom,
                'email' => $enseignant->email,
            ],
            'status' => true
        ], 201);
    }

    public function affecterMatiere(Request $request, $enseignantId)
    {
        $request->validate([
            'matiere_id' => 'required|exists:matieres,id',
            'classe_id' => 'required|exists:classes,id',
        ]);

        $enseignant = Enseignant::findOrFail($enseignantId);
        $enseignant->matieres()->attach($request->matiere_id, ['classe_id' => $request->classe_id]);

        return response()->json([
            'message' => 'Matière affectée avec succès.',
            'status' => true
        ], 200);
    }
}
