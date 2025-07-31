@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Ajouter une classe</h1>
        <form action="{{ route('admin.classes.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="nom" class="block text-sm font-medium">Nom de la classe</label>
                <input type="text" name="nom" id="nom"
                    class="mt-1 block w-full border rounded p-2 @error('nom') border-red-500 @enderror"
                    value="{{ old('nom') }}">
                @error('nom')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Créer</button>
            <a href="{{ route('admin.classes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Annuler</a>
        </form>
    </div>
@endsection
