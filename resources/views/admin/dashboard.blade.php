@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Tableau de bord - Administrateur</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-lg font-semibold">Nombre d'élèves</h2>
                <p class="text-2xl">{{ $stats['nombre_eleves'] }}</p>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-lg font-semibold">Nombre d'enseignants</h2>
                <p class="text-2xl">{{ $stats['nombre_enseignants'] }}</p>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-lg font-semibold">Nombre de classes</h2>
                <p class="text-2xl">{{ $stats['nombre_classes'] }}</p>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-lg font-semibold">Nombre de parents</h2>
                <p class="text-2xl">{{ $stats['nombre_parents'] }}</p>
            </div>
        </div>
        <div class="mt-6">
            <a href="{{ route('admin.classes.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">Gérer les
                classes</a>
            <a href="{{ route('admin.etudiants.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">Gérer les
                étudiants</a>
            <a href="{{ route('admin.enseignants.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">Gérer les
                enseignants</a>
            <a href="{{ route('admin.matieres.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">Gérer les
                matières</a>
            <a href="{{ route('admin.parents.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Gérer les
                parents</a>
        </div>
    </div>
@endsection
