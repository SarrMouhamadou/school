@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Détails du parent</h1>
        <div class="bg-white p-4 rounded shadow">
            <p><strong>ID:</strong> {{ $parent->id }}</p>
            <p><strong>Nom:</strong> {{ $parent->name }}</p>
            <p><strong>Email:</strong> {{ $parent->email }}</p>
            <p><strong>Enfants:</strong>
                @forelse ($parent->students as $etudiant)
                    {{ $etudiant->prenom }} {{ $etudiant->nom }} (Classe: {{ $etudiant->classe->nom ?? 'N/A' }})
                    @if (!$loop->last)
                        ,
                    @endif
                @empty
                    Aucun enfant assigné.
                @endforelse
            </p>
        </div>
        <div class="mt-4">
            <a href="{{ route('admin.parents.edit', $parent->id) }}"
                class="bg-yellow-500 text-white px-4 py-2 rounded">Modifier</a>
            <a href="{{ route('admin.parents.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Retour</a>
        </div>
    </div>
@endsection
