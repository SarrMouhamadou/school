@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Détails de l'enseignant</h1>
        <div class="bg-white p-4 rounded shadow">
            <p><strong>ID:</strong> {{ $enseignant->id }}</p>
            <p><strong>Nom:</strong> {{ $enseignant->nom }}</p>
            <p><strong>Prénom:</strong> {{ $enseignant->prenom }}</p>
            <p><strong>Email:</strong> {{ $enseignant->user->email }}</p>
            <p><strong>Matricule:</strong> {{ $enseignant->matricule }}</p>
            <p><strong>Matières enseignées:</strong>
                @forelse ($enseignant->matieres as $matiere)
                    {{ $matiere->nom }} (Classe: {{ $matiere->pivot->classe->nom ?? 'N/A' }})
                    @if (!$loop->last)
                        ,
                    @endif
                @empty
                    Aucune matière assignée.
                @endforelse
            </p>
        </div>
        <div class="mt-4">
            <a href="{{ route('admin.enseignants.edit', $enseignant->id) }}"
                class="bg-yellow-500 text-white px-4 py-2 rounded">Modifier</a>
            <a href="{{ route('admin.enseignants.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Retour</a>
        </div>
    </div>
@endsection
