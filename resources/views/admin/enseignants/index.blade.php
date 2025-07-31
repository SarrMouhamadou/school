@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Gestion des enseignants</h1>
        <a href="{{ route('admin.enseignants.create') }}"
            class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Ajouter un enseignant</a>

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
                    <th class="border p-2">Email</th>
                    <th class="border p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enseignants as $enseignant)
                    <tr>
                        <td class="border p-2">{{ $enseignant->id }}</td>
                        <td class="border p-2">{{ $enseignant->nom }}</td>
                        <td class="border p-2">{{ $enseignant->prenom }}</td>
                        <td class="border p-2">{{ $enseignant->user->email }}</td>
                        <td class="border p-2">
                            <a href="{{ route('admin.enseignants.show', $enseignant->id) }}"
                                class="bg-green-500 text-white px-2 py-1 rounded">Voir</a>
                            <a href="{{ route('admin.enseignants.edit', $enseignant->id) }}"
                                class="bg-yellow-500 text-white px-2 py-1 rounded">Modifier</a>
                            <form action="{{ route('admin.enseignants.destroy', $enseignant->id) }}" method="POST"
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
                        <td colspan="5" class="border p-2 text-center">Aucun enseignant trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
