@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Ajouter un parent</h1>
        <form action="{{ route('admin.parents.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium">Nom</label>
                <input type="text" name="name" id="name"
                    class="mt-1 block w-full border rounded p-2 @error('name') border-red-500 @enderror"
                    value="{{ old('name') }}">
                @error('name')
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
                <label for="password" class="block text-sm font-medium">Mot de passe</label>
                <input type="password" name="password" id="password"
                    class="mt-1 block w-full border rounded p-2 @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="mt-1 block w-full border rounded p-2">
            </div>
            <div class="mb-4">
                <label for="etudiants" class="block text-sm font-medium">Enfants</label>
                <select name="etudiants[]" id="etudiants" multiple
                    class="mt-1 block w-full border rounded p-2 @error('etudiants') border-red-500 @enderror">
                    @foreach ($etudiants as $etudiant)
                        <option value="{{ $etudiant->id }}"
                            {{ in_array($etudiant->id, old('etudiants', [])) ? 'selected' : '' }}>{{ $etudiant->prenom }}
                            {{ $etudiant->nom }}</option>
                    @endforeach
                </select>
                @error('etudiants')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Créer</button>
            <a href="{{ route('admin.parents.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Annuler</a>
        </form>
    </div>
@endsection
