<!DOCTYPE html>
<html>
<head>
    <title>Bulletin {{ $bulletin->etudiant->prenom }} {{ $bulletin->etudiant->nom }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        h1 { text-align: center; }
    </style>
</head>
<body>
    <h1>Bulletin de {{ $bulletin->etudiant->prenom }} {{ $bulletin->etudiant->nom }}</h1>
    <p>Semestre : {{ $bulletin->semestre }}</p>
    <table>
        <thead>
            <tr>
                <th>Matière</th>
                <th>Moyenne</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bulletin->moyennes_matieres as $matiere => $moyenne)
                <tr>
                    <td>{{ $matiere }}</td>
                    <td>{{ $moyenne }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p>Moyenne générale : {{ $bulletin->moyenne_generale }}</p>
    <p>Mention : {{ $bulletin->mention }}</p>
    <p>Rang : {{ $bulletin->rang }}</p>
    <p>Appréciation : {{ $bulletin->appreciation }}</p>
</body>
</html>
