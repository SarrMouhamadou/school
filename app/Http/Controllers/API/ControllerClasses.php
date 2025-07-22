<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use Illuminate\Http\Request;

class ControllerClasses extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
        ]);

        $nomEntree = $request->input('nom');
        // Vérifier si une classe existe avec une variante de casse
        $classeExistante = Classe::whereRaw('LOWER(nom) = LOWER(?)', [$nomEntree])->first();

        if ($classeExistante) {
            return response()->json([
                'message' => 'Une classe avec ce nom existe déjà : ' . $classeExistante->nom,
                'status' => false
            ], 409); // 409 Conflict
        }

        $classe = Classe::create([
            'nom' => $nomEntree,
        ]);

        return response()->json([
            'message' => 'Classe créée avec succès.',
            'classe' => [
                'id' => $classe->id,
                'nom' => $classe->nom,
            ],
            'status' => true
        ], 201);
    }

    public function index()
    {
        $classes = Classe::all();
        return response()->json([
            'message' => 'Classes récupérées avec succès.',
            'classes' => $classes->map(function ($classe) {
                return [
                    'id' => $classe->id,
                    'nom' => $classe->nom,
                ];
            }),
            'status' => true
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $classe = Classe::find($id);

        if (!$classe) {
            return response()->json([
                'message' => 'Classe non trouvée.',
                'status' => false
            ], 404);
        }

        $request->validate([
            'nom' => 'required|string|max:255',
        ]);

        $nomEntree = $request->input('nom');
        $classeExistante = Classe::where('id', '!=', $id)->whereRaw('LOWER(nom) = LOWER(?)', [$nomEntree])->first();

        if ($classeExistante) {
            return response()->json([
                'message' => 'Une classe avec ce nom (insensible à la casse) existe déjà : ' . $classeExistante->nom,
                'status' => false
            ], 409);
        }

        $classe->update(['nom' => $nomEntree]);

        return response()->json([
            'message' => 'Classe mise à jour avec succès.',
            'classe' => [
                'id' => $classe->id,
                'nom' => $classe->nom,
            ],
            'status' => true
        ], 200);
    }

    public function destroy($id)
    {
        $classe = Classe::find($id);

        if (!$classe) {
            return response()->json([
                'message' => 'Classe non trouvée.',
                'status' => false
            ], 404);
        }

        $classe->delete();

        return response()->json([
            'message' => 'Classe supprimée avec succès.',
            'status' => true
        ], 200);
    }

}
