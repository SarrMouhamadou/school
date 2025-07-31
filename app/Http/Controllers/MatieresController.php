<?php
namespace App\Http\Controllers;

use App\Models\Matiere;
use Illuminate\Http\Request;

class MatieresController extends Controller
{
    public function index()
    {
        $matieres = Matiere::all();
        return view('admin.matieres.index', compact('matieres'));
    }

    public function create()
    {
        return view('admin.matieres.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'coefficient' => 'required|numeric|min:0',
        ]);

        Matiere::create([
            'nom' => $request->nom,
            'coefficient' => $request->coefficient,
        ]);

        return redirect()->route('admin.matieres.index')->with('success', 'Matière créée avec succès.');
    }

    public function show($id)
    {
        $matiere = Matiere::findOrFail($id);
        return view('admin.matieres.show', compact('matiere'));
    }

    public function edit($id)
    {
        $matiere = Matiere::findOrFail($id);
        return view('admin.matieres.edit', compact('matiere'));
    }

    public function update(Request $request, $id)
    {
        $matiere = Matiere::findOrFail($id);
        $request->validate([
            'nom' => 'required|string|max:255',
            'coefficient' => 'required|numeric|min:0',
        ]);

        $matiere->update([
            'nom' => $request->nom,
            'coefficient' => $request->coefficient,
        ]);

        return redirect()->route('admin.matieres.index')->with('success', 'Matière mise à jour avec succès.');
    }

    public function destroy($id)
    {
        $matiere = Matiere::findOrFail($id);
        $matiere->delete();
        return redirect()->route('admin.matieres.index')->with('success', 'Matière supprimée avec succès.');
    }
}
