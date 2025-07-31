<?php
namespace App\Http\Controllers;

use App\Models\Classe;
use Illuminate\Http\Request;

class ClassesController extends Controller
{
    public function index()
    {
        $classes = Classe::all();
        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        return view('admin.classes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255|unique:classes,nom',
        ]);

        Classe::create($request->only('nom'));

        return redirect()->route('admin.classes.index')->with('success', 'Classe créée avec succès.');
    }

    public function edit($id)
    {
        $classe = Classe::findOrFail($id);
        return view('admin.classes.edit', compact('classe'));
    }

    public function update(Request $request, $id)
    {
        $classe = Classe::findOrFail($id);
        $request->validate([
            'nom' => 'required|string|max:255|unique:classes,nom,' . $id,
        ]);

        $classe->update($request->only('nom'));

        return redirect()->route('admin.classes.index')->with('success', 'Classe mise à jour avec succès.');
    }

    public function destroy($id)
    {
        $classe = Classe::findOrFail($id);
        $classe->delete();

        return redirect()->route('admin.classes.index')->with('success', 'Classe supprimée avec succès.');
    }
}
