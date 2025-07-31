@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Bulletin - {{ $etudiant->prenom }} {{ $etudiant->nom }} ({{ $semestre }})
        </h1>
        @if ($moyenneGenerale)
            <table class="w-full border-collapse border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border p-2">Matière</th>
                        <th class="border p-2">Moyenne</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($moyennesMatieres as $matiere => $moyenne)
                        <tr>
                            <td class="border p-2">{{ $matiere }}</td>
                            <td class="border p-2">{{ $moyenne }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="mt-4">Moyenne générale : {{ $moyenneGenerale }}</p>
            <a href="{{ route('eleve.bulletin.download', $semestre) }}"
                class="bg-blue-500 text-white px-4 py-2 rounded mt-4 inline-block">Télécharger PDF</a>
        @else
            <p class="text-red-500">Aucune note disponible pour ce semestre.</p>
        @endif
    </div>
@endsection
