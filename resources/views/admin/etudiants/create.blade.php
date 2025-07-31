@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Ajouter un étudiant</h1>
        <form action="{{ route('admin.etudiants.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="nom" class="block text-sm font-medium">Nom</label>
                <input type="text" name="nom" id="nom"
                    class="mt-1 block w-full border rounded p-2 @error('nom') border-red-500 @enderror"
                    value="{{ old('nom') }}">
                @error('nom')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="prenom" class="block text-sm font-medium">Prénom</label>
                <input type="text" name="prenom" id="prenom"
                    class="mt-1 block w-full border rounded p-2 @error('prenom') border-red-500 @enderror"
                    value="{{ old('prenom') }}">
                @error('prenom')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="date_de_naissance" class="block text-sm font-medium">Date de naissance</label>
                <input type="date" name="date_de_naissance" id="date_de_naissance"
                    class="mt-1 block w-full border rounded p-2 @error('date_de_naissance') border-red-500 @enderror"
                    value="{{ old('date_de_naissance') }}">
                @error('date_de_naissance')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium">Email</label>
                <input type="email" name="email" id="email"
                    class="mt-1 block w-full border rounded p-2 @error('email') border-red-500 @enderror"
                    value="{{ old('email') }}">
                @error('email')
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
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Créer</button>
            <a href="{{ route('admin.etudiants.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Annuler</a>
        </form>
    </div>
@endsection
