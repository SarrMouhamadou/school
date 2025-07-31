@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Détails de la matière</h1>
        <div class="bg-white p-4 rounded shadow">
            <p><strong>ID:</strong> {{ $matiere->id }}</p>
            <p><strong>Nom:</strong> {{ $matiere->nom }}</p>
            <p><strong>Coefficient:</strong> {{ $matiere->coefficient }}</p>
        </div>
        <div class="mt-4">
            <a href="{{ route('admin.matieres.edit', $matiere->id) }}"
                class="bg-yellow-500 text-white px-4 py-2 rounded">Modifier</a>
            <a href="{{ route('admin.matieres.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Retour</a>
        </div>
    </div>
@endsection
