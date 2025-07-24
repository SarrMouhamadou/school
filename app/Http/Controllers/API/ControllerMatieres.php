<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Matiere;
use Illuminate\Http\Request;

class ControllerMatieres extends Controller
{
    public function index()
    {
        $matieres = Matiere::all();
        return response()->json([
            'message' => 'Matières récupérées avec succès.',
            'matieres' => $matieres->map(function ($matiere) {
                return [
                    'id' => $matiere->id,
                    'nom' => $matiere->nom,
                    'coefficient' => $matiere->coefficient,
                ];
            }),
            'status' => true
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255|unique:matieres,nom',
            'coefficient' => 'required|numeric|min:0.01|max:10.00',
        ]);

        $matiere = Matiere::create($request->only(['nom', 'coefficient']));

        return response()->json([
            'message' => 'Matière créée avec succès.',
            'matiere' => [
                'id' => $matiere->id,
                'nom' => $matiere->nom,
                'coefficient' => $matiere->coefficient,
            ],
            'status' => true
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $matiere = Matiere::findOrFail($id);

        $request->validate([
            'nom' => 'sometimes|required|string|max:255|unique:matieres,nom,' . $id,
            'coefficient' => 'sometimes|required|numeric|min:0.01|max:10.00',
        ]);

        $matiere->update($request->only(['nom', 'coefficient']));

        return response()->json([
            'message' => 'Matière mise à jour avec succès.',
            'matiere' => [
                'id' => $matiere->id,
                'nom' => $matiere->nom,
                'coefficient' => $matiere->coefficient,
            ],
            'status' => true
        ], 200);
    }

    public function destroy($id)
    {
        $matiere = Matiere::findOrFail($id);

        if ($matiere->enseignants()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer : cette matière est affectée à un enseignant.',
                'status' => false
            ], 400);
        }

        $matiere->delete();

        return response()->json([
            'message' => 'Matière supprimée avec succès.',
            'status' => true
        ], 200);
    }
}
