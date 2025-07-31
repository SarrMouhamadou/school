@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Gestion des parents</h1>
        <a href="{{ route('admin.parents.create') }}"
            class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Ajouter un parent</a>

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
                    <th class="border p-2">Email</th>
                    <th class="border p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($parents as $parent)
                    <tr>
                        <td class="border p-2">{{ $parent->id }}</td>
                        <td class="border p-2">{{ $parent->name }}</td>
                        <td class="border p-2">{{ $parent->email }}</td>
                        <td class="border p-2">
                            <a href="{{ route('admin.parents.show', $parent->id) }}"
                                class="bg-green-500 text-white px-2 py-1 rounded">Voir</a>
                            <a href="{{ route('admin.parents.edit', $parent->id) }}"
                                class="bg-yellow-500 text-white px-2 py-1 rounded">Modifier</a>
                            <form action="{{ route('admin.parents.destroy', $parent->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded"
                                    onclick="return confirm('Confirmer la suppression ?')">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="border p-2 text-center">Aucun parent trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
