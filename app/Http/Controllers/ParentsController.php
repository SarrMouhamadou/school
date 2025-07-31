<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Etudiant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ParentsController extends Controller
{
    public function index()
    {
        $parents = User::where('role_id', Role::where('name', 'parent')->first()->id)->get();
        return view('admin.parents.index', compact('parents'));
    }

    public function create()
    {
        $etudiants = Etudiant::all();
        return view('admin.parents.create', compact('etudiants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'etudiants' => 'required|array',
            'etudiants.*' => 'exists:etudiants,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => Role::where('name', 'parent')->first()->id,
            'email_verified_at' => now(),
        ]);

        $user->students()->attach($request->etudiants);

        return redirect()->route('admin.parents.index')->with('success', 'Parent créé avec succès.');
    }

    public function show($id)
    {
        $parent = User::with('students')->findOrFail($id);
        return view('admin.parents.show', compact('parent'));
    }

    public function edit($id)
    {
        $parent = User::findOrFail($id);
        $etudiants = Etudiant::all();
        return view('admin.parents.edit', compact('parent', 'etudiants'));
    }

    public function update(Request $request, $id)
    {
        $parent = User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $parent->id,
            'password' => 'nullable|string|min:8|confirmed',
            'etudiants' => 'required|array',
            'etudiants.*' => 'exists:etudiants,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $parent->update($data);
        $parent->students()->sync($request->etudiants);

        return redirect()->route('admin.parents.index')->with('success', 'Parent mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $parent = User::findOrFail($id);
        $parent->students()->detach();
        $parent->delete();
        return redirect()->route('admin.parents.index')->with('success', 'Parent supprimé avec succès.');
    }

    public function getStudentsWeb()
    {
        $user = auth()->user();
        $etudiants = $user->students()->with('classe')->get();
        return view('parent.dashboard', compact('etudiants'));
    }
}
