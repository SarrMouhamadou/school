@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Détails de l'étudiant</h1>
        <div class="bg-white p-4 rounded shadow">
            <p><strong>ID:</strong> {{ $etudiant->id }}</p>
            <p><strong>Nom:</strong> {{ $etudiant->nom }}</p>
            <p><strong>Prénom:</strong> {{ $etudiant->prenom }}</p>
            <p><strong>Date de naissance:</strong> {{ $etudiant->date_de_naissance }}</p>
            <p><strong>Classe:</strong> {{ $etudiant->classe->nom }}</p>
            <p><strong>Email:</strong> {{ $etudiant->user->email }}</p>
            <p><strong>Matricule:</strong> {{ $etudiant->matricule }}</p>
        </div>
        <div class="mt-4">
            <a href="{{ route('admin.etudiants.edit', $etudiant->id) }}"
                class="bg-yellow-500 text-white px-4 py-2 rounded">Modifier</a>
            <a href="{{ route('admin.etudiants.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Retour</a>
        </div>
    </div>
@endsection
