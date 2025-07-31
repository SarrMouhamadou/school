<?php
namespace App\Http\Controllers;

use App\Models\Enseignant;
use App\Models\User;
use App\Models\Role;
use App\Models\Matiere;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EnseignantsController extends Controller
{
    public function index()
    {
        $enseignants = Enseignant::with('user')->get();
        return view('admin.enseignants.index', compact('enseignants'));
    }

    public function create()
    {
        $matieres = Matiere::all();
        $classes = Classe::all();
        return view('admin.enseignants.create', compact('matieres', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'matieres' => 'required|array',
            'matieres.*' => 'exists:matieres,id',
            'classes' => 'required|array',
            'classes.*' => 'exists:classes,id',
        ]);

        $user = User::create([
            'name' => $request->prenom . ' ' . $request->nom,
            'email' => $request->email,
            'password' => Hash::make('password'), // À personnaliser
            'role_id' => Role::where('name', 'enseignant')->first()->id,
            'email_verified_at' => now(),
        ]);

        $enseignant = Enseignant::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'matricule' => 'E' . date('Y') . rand(1000, 9999),
            'user_id' => $user->id,
        ]);

        foreach ($request->matieres as $matiereId) {
            foreach ($request->classes as $classeId) {
                $enseignant->matieres()->attach($matiereId, ['classe_id' => $classeId]);
            }
        }

        return redirect()->route('admin.enseignants.index')->with('success', 'Enseignant créé avec succès.');
    }

    public function show($id)
    {
        $enseignant = Enseignant::with(['user', 'matieres', 'classes'])->findOrFail($id);
        return view('admin.enseignants.show', compact('enseignant'));
    }

    public function edit($id)
    {
        $enseignant = Enseignant::findOrFail($id);
        $matieres = Matiere::all();
        $classes = Classe::all();
        return view('admin.enseignants.edit', compact('enseignant', 'matieres', 'classes'));
    }

    public function update(Request $request, $id)
    {
        $enseignant = Enseignant::findOrFail($id);
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $enseignant->user_id,
            'matieres' => 'required|array',
            'matieres.*' => 'exists:matieres,id',
            'classes' => 'required|array',
            'classes.*' => 'exists:classes,id',
        ]);

        $enseignant->update([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
        ]);

        $enseignant->user->update([
            'name' => $request->prenom . ' ' . $request->nom,
            'email' => $request->email,
        ]);

        $enseignant->matieres()->sync([]);
        foreach ($request->matieres as $matiereId) {
            foreach ($request->classes as $classeId) {
                $enseignant->matieres()->attach($matiereId, ['classe_id' => $classeId]);
            }
        }

        return redirect()->route('admin.enseignants.index')->with('success', 'Enseignant mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $enseignant = Enseignant::findOrFail($id);
        $enseignant->user->delete();
        $enseignant->delete();
        return redirect()->route('admin.enseignants.index')->with('success', 'Enseignant supprimé avec succès.');
    }
}
