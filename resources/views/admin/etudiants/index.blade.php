@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Gestion des étudiants</h1>
        <a href="{{ route('admin.etudiants.create') }}"
            class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Ajouter un étudiant</a>

        @if (session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <table class="w-full border-collapse border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Nom</th>
                    <th class="border p-2">Prénom</th>
                    <th class="border p-2">Classe</th>
                    <th class="border p-2">Email</th>
                    <th class="border p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($etudiants as $etudiant)
                    <tr>
                        <td class="border p-2">{{ $etudiant->id }}</td>
                        <td class="border p-2">{{ $etudiant->nom }}</td>
                        <td class="border p-2">{{ $etudiant->prenom }}</td>
                        <td class="border p-2">{{ $etudiant->classe->nom }}</td>
                        <td class="border p-2">{{ $etudiant->user->email }}</td>
                        <td class="border p-2">
                            <a href="{{ route('admin.etudiants.show', $etudiant->id) }}"
                                class="bg-green-500 text-white px-2 py-1 rounded">Voir</a>
                            <a href="{{ route('admin.etudiants.edit', $etudiant->id) }}"
                                class="bg-yellow-500 text-white px-2 py-1 rounded">Modifier</a>
                            <form action="{{ route('admin.etudiants.destroy', $etudiant->id) }}" method="POST"
                                class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded"
                                    onclick="return confirm('Confirmer la suppression ?')">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="border p-2 text-center">Aucun étudiant trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
