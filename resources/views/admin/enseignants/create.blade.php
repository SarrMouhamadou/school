@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Ajouter un enseignant</h1>
        <form action="{{ route('admin.enseignants.store') }}" method="POST">
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
                <label for="email" class="block text-sm font-medium">Email</label>
                <input type="email" name="email" id="email"
                    class="mt-1 block w-full border rounded p-2 @error('email') border-red-500 @enderror"
                    value="{{ old('email') }}">
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="matieres" class="block text-sm font-medium">Matières</label>
                <select name="matieres[]" id="matieres" multiple
                    class="mt-1 block w-full border rounded p-2 @error('matieres') border-red-500 @enderror">
                    @foreach ($matieres as $matiere)
                        <option value="{{ $matiere->id }}"
                            {{ in_array($matiere->id, old('matieres', [])) ? 'selected' : '' }}>{{ $matiere->nom }}</option>
                    @endforeach
                </select>
                @error('matieres')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="classes" class="block text-sm font-medium">Classes</label>
                <select name="classes[]" id="classes" multiple
                    class="mt-1 block w-full border rounded p-2 @error('classes') border-red-500 @enderror">
                    @foreach ($classes as $classe)
                        <option value="{{ $classe->id }}"
                            {{ in_array($classe->id, old('classes', [])) ? 'selected' : '' }}>{{ $classe->nom }}</option>
                    @endforeach
                </select>
                @error('classes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Créer</button>
            <a href="{{ route('admin.enseignants.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Annuler</a>
        </form>
    </div>
@endsection
