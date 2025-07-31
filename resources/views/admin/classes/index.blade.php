@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Gestion des classes</h1>
        <a href="{{ route('admin.classes.create') }}"
            class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Ajouter une classe</a>

        @if (session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <table class="w-full border-collapse border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Nom</th>
                    <th class="border p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($classes as $classe)
                    <tr>
                        <td class="border p-2">{{ $classe->id }}</td>
                        <td class="border p-2">{{ $classe->nom }}</td>
                        <td class="border p-2">
                            <a href="{{ route('admin.classes.edit', $classe->id) }}"
                                class="bg-yellow-500 text-white px-2 py-1 rounded">Modifier</a>
                            <form action="{{ route('admin.classes.destroy', $classe->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded"
                                    onclick="return confirm('Confirmer la suppression ?')">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="border p-2 text-center">Aucune classe trouvée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
