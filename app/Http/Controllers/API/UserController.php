<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|exists:roles,name',
        ]);

        if ($request->role === 'eleve') {
            return response()->json([
                'message' => 'La création d\'un élève doit se faire via l\'inscription d\'un étudiant (/etudiant/inscrire).',
                'status' => false
            ], 403);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $user->role()->associate(Role::where('name', $request->role)->first());
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
            ],
            'status' => true
        ], 200);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'message' => 'Successfully logged out',
            'status' => true
        ], 200);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        // Vérifier si l'utilisateur a le rôle 'admin'
        if (!$user || $user->role->name !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized ! Only admins can view all users',
                'status' => false
            ], 403);
        }

        // Récupérer tous les utilisateurs avec leur rôle
        $users = User::with('role')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ? $user->role->name : null,
            ];
        });

        return response()->json([
            'users' => $users,
            'status' => true
        ], 200);
    }

    public function getUser(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
            ],
            'status' => true
        ], 200);
    }
}
