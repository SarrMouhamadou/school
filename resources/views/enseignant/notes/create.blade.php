@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Ajouter une note</h1>
        <form action="{{ route('enseignant.notes.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="etudiant_id" class="block text-sm font-medium">Étudiant</label>
                <select name="etudiant_id" id="etudiant_id"
                    class="mt-1 block w-full border rounded p-2 @error('etudiant_id') border-red-500 @enderror">
                    <option value="">Sélectionner un étudiant</option>
                    @foreach ($etudiants as $etudiant)
                        <option value="{{ $etudiant->id }}" {{ old('etudiant_id') == $etudiant->id ? 'selected' : '' }}>
                            {{ $etudiant->prenom }} {{ $etudiant->nom }} ({{ $etudiant->classe->nom }})</option>
                    @endforeach
                </select>
                @error('etudiant_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="matiere_id" class="block text-sm font-medium">Matière</label>
                <select name="matiere_id" id="matiere_id"
                    class="mt-1 block w-full border rounded p-2 @error('matiere_id') border-red-500 @enderror">
                    <option value="">Sélectionner une matière</option>
                    @foreach ($matieres as $matiere)
                        <option value="{{ $matiere->id }}" {{ old('matiere_id') == $matiere->id ? 'selected' : '' }}>
                            {{ $matiere->nom }} (Classe: {{ $matiere->pivot->classe->nom }})</option>
                    @endforeach
                </select>
                @error('matiere_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="classe_id" class="block text-sm font-medium">Classe</label>
                <select name="classe_id" id="classe_id"
                    class="mt-1 block w-full border rounded p-2 @error('classe_id') border-red-500 @enderror">
                    <option value="">Sélectionner une classe</option>
                    @foreach ($classes as $classe)
                        <option value="{{ $classe->id }}" {{ old('classe_id') == $classe->id ? 'selected' : '' }}>
                            {{ $classe->nom }}</option>
                    @endforeach
                </select>
                @error('classe_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="valeur" class="block text-sm font-medium">Note (0-20)</label>
                <input type="number" step="0.1" name="valeur" id="valeur"
                    class="mt-1 block w-full border rounded p-2 @error('valeur') border-red-500 @enderror"
                    value="{{ old('valeur') }}">
                @error('valeur')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="semestre" class="block text-sm font-medium">Semestre</label>
                <select name="semestre" id="semestre"
                    class="mt-1 block w-full border rounded p-2 @error('semestre') border-red-500 @enderror">
                    <option value="">Sélectionner un semestre</option>
                    <option value="1" {{ old('semestre') == '1' ? 'selected' : '' }}>Semestre 1</option>
                    <option value="2" {{ old('semestre') == '2' ? 'selected' : '' }}>Semestre 2</option>
                </select>
                @error('semestre')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="type_evaluation" class="block text-sm font-medium">Type d'évaluation</label>
                <select name="type_evaluation" id="type_evaluation"
                    class="mt-1 block w-full border rounded p-2 @error('type_evaluation') border-red-500 @enderror">
                    <option value="">Sélectionner un type</option>
                    <option value="devoir" {{ old('type_evaluation') == 'devoir' ? 'selected' : '' }}>Devoir</option>
                    <option value="examen" {{ old('type_evaluation') == 'examen' ? 'selected' : '' }}>Examen</option>
                </select>
                @error('type_evaluation')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Créer</button>
            <a href="{{ route('enseignant.dashboard') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Annuler</a>
        </form>
    </div>
@endsection
