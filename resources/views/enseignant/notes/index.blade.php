@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Tableau de bord - Enseignant</h1>
        <a href="{{ route('enseignant.notes.create') }}"
            class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Ajouter une note</a>

        @if (session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <table class="w-full border-collapse border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Étudiant</th>
                    <th class="border p-2">Matière</th>
                    <th class="border p-2">Classe</th>
                    <th class="border p-2">Note</th>
                    <th class="border p-2">Semestre</th>
                    <th class="border p-2">Type</th>
                    <th class="border p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($notes as $note)
                    <tr>
                        <td class="border p-2">{{ $note->id }}</td>
                        <td class="border p-2">
                            {{ $note->etudiant ? $note->etudiant->prenom . ' ' . $note->etudiant->nom : 'Étudiant non trouvé' }}
                        </td>
                        <td class="border p-2">
                            {{ $note->matiere ? $note->matiere->nom : 'Matière non trouvée' }}
                        </td>
                        <td class="border p-2">
                            {{ $note->etudiant && $note->etudiant->classe ? $note->etudiant->classe->nom : 'Classe non trouvée' }}
                        </td>
                        <td class="border p-2">{{ $note->valeur }}</td>
                        <td class="border p-2">{{ $note->semestre }}</td>
                        <td class="border p-2">{{ ucfirst($note->type_evaluation) }}</td>
                        <td class="border p-2">
                            <a href="{{ route('enseignant.notes.edit', $note->id) }}"
                                class="bg-yellow-500 text-white px-2 py-1 rounded">Modifier</a>
                            <form action="{{ route('enseignant.notes.destroy', $note->id) }}" method="POST"
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
                        <td colspan="8" class="border p-2 text-center">Aucune note trouvée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
