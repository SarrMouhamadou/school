<!DOCTYPE html>
<html>
<head><title>Bulletin</title></head>
<body>
    <h1>Bulletin de {{ $etudiant->prenom }} {{ $etudiant->nom }}</h1>
    <p>Semestre : {{ $semestre }}</p>
    @foreach($moyennes_matieres as $matiere => $moyenne)
        <p>{{ $matiere }} : {{ $moyenne }}</p>
    @endforeach
    <p>Moyenne générale : {{ $moyenne_generale }}</p>
    <p>Mention : {{ $mention }}</p>
    <p>Rang : {{ $rang }}</p>
    <p>Appréciation : {{ $appreciation }}</p>
</body>
</html>
