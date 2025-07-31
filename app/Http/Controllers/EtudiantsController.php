<?php
namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Classe;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EtudiantsController extends Controller
{
    public function index()
    {
        $etudiants = Etudiant::with('classe')->get();
        return view('admin.etudiants.index', compact('etudiants'));
    }

    public function create()
    {
        $classes = Classe::all();
        return view('admin.etudiants.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date_de_naissance' => 'required|date',
            'email' => 'required|string|email|max:255|unique:users',
            'classe_id' => 'required|exists:classes,id',
        ]);

        $user = User::create([
            'name' => $request->prenom . ' ' . $request->nom,
            'email' => $request->email,
            'password' => Hash::make('password'), // À personnaliser
            'role_id' => Role::where('name', 'eleve')->first()->id,
            'email_verified_at' => now(),
        ]);

        $etudiant = Etudiant::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'date_de_naissance' => $request->date_de_naissance,
            'matricule' => 'S' . date('Y') . rand(1000, 9999),
            'classe_id' => $request->classe_id,
            'user_id' => $user->id,
        ]);

        $user->update(['etudiant_id' => $etudiant->id]);

        return redirect()->route('admin.etudiants.index')->with('success', 'Étudiant créé avec succès.');
    }

    public function show($id)
    {
        $etudiant = Etudiant::with('classe')->findOrFail($id);
        return view('admin.etudiants.show', compact('etudiant'));
    }

    public function edit($id)
    {
        $etudiant = Etudiant::findOrFail($id);
        $classes = Classe::all();
        return view('admin.etudiants.edit', compact('etudiant', 'classes'));
    }

    public function update(Request $request, $id)
    {
        $etudiant = Etudiant::findOrFail($id);
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date_de_naissance' => 'required|date',
            'email' => 'required|string|email|max:255|unique:users,email,' . $etudiant->user_id,
            'classe_id' => 'required|exists:classes,id',
        ]);

        $etudiant->update([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'date_de_naissance' => $request->date_de_naissance,
            'classe_id' => $request->classe_id,
        ]);

        $etudiant->user->update([
            'name' => $request->prenom . ' ' . $request->nom,
            'email' => $request->email,
        ]);

        return redirect()->route('admin.etudiants.index')->with('success', 'Étudiant mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $etudiant = Etudiant::findOrFail($id);
        $etudiant->user->delete();
        $etudiant->delete();
        return redirect()->route('admin.etudiants.index')->with('success', 'Étudiant supprimé avec succès.');
    }
}
