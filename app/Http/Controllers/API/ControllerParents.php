<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Etudiant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ControllerParents extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'student_ids' => 'required|array', // Liste des IDs des étudiants
            'student_ids.*' => 'exists:students,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $role = Role::where('name', 'parent')->firstOrFail();
        $user->role()->associate($role);
        $user->save();

        // Lier le parent aux étudiants
        foreach ($request->student_ids as $studentId) {
            $user->students()->attach($studentId);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Parent inscrit avec succès.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
            ],
            'status' => true
        ], 201);
    }

    public function linkStudent(Request $request, $parentId)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $parent = User::findOrFail($parentId);
        if ($parent->role->name !== 'parent') {
            return response()->json(['message' => 'Utilisateur non autorisé.', 'status' => false], 403);
        }

        if ($parent->students()->where('student_id', $request->student_id)->exists()) {
            return response()->json(['message' => 'Cet étudiant est déjà lié.', 'status' => false], 400);
        }

        $parent->students()->attach($request->student_id);

        return response()->json([
            'message' => 'Étudiant lié avec succès.',
            'parent' => [
                'id' => $parent->id,
                'name' => $parent->name,
                'students' => $parent->students->pluck('id'),
            ],
            'status' => true
        ], 200);
    }

    public function getStudents($parentId)
    {
        $parent = User::findOrFail($parentId);
        if ($parent->role->name !== 'parent') {
            return response()->json(['message' => 'Utilisateur non autorisé.', 'status' => false], 403);
        }

        return response()->json([
            'message' => 'Étudiants récupérés avec succès.',
            'students' => $parent->students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'nom' => $student->nom,
                    'prenom' => $student->prenom,
                    'matricule' => $student->matricule,
                    'classe' => $student->classe ? $student->classe->nom : null, // Ajout de la classe
                ];
            }),
            'status' => true
        ], 200);
    }

    public function index()
    {
        $user = auth()->user();
        if (!$user || $user->role->name !== 'admin') {
            return response()->json(['message' => 'Unauthorized ! Only admins can view all parents', 'status' => false], 403);
        }

        $parents = User::where('role_id', function ($query) {
            $query->select('id')->from('roles')->where('name', 'parent');
        })->withCount('students')->get();

        return response()->json([
            'message' => 'Parents récupérés avec succès.',
            'parents' => $parents->map(function ($parent) {
                return [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'email' => $parent->email, // Ajout de l'email si nécessaire
                    'nombres_enfants' => $parent->students_count,
                ];
            }),
            'status' => true
        ], 200);
    }
}
