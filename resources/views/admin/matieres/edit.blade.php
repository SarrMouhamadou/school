@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Modifier une matière</h1>
        <form action="{{ route('admin.matieres.update', $matiere->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="nom" class="block text-sm font-medium">Nom</label>
                <input type="text" name="nom" id="nom"
                    class="mt-1 block w-full border rounded p-2 @error('nom') border-red-500 @enderror"
                    value="{{ old('nom', $matiere->nom) }}">
                @error('nom')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="coefficient" class="block text-sm font-medium">Coefficient</label>
                <input type="number" step="0.1" name="coefficient" id="coefficient"
                    class="mt-1 block w-full border rounded p-2 @error('coefficient') border-red-500 @enderror"
                    value="{{ old('coefficient', $matiere->coefficient) }}">
                @error('coefficient')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Mettre à jour</button>
            <a href="{{ route('admin.matieres.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Annuler</a>
        </form>
    </div>
@endsection
