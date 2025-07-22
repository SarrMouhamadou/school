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
}
