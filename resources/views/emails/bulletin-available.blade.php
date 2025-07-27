<!DOCTYPE html>
<html>
<head>
    <title>Nouveau Bulletin Disponible</title>
</head>
<body>
    <h1>Bonjour {{ $etudiant->prenom }} {{ $etudiant->nom }},</h1>
    <p>Votre bulletin pour le semestre {{ $semestre }} est maintenant disponible.</p>
    <p>Moyenne générale : {{ number_format($moyenneGenerale, 2) }}</p>
    <p>Connectez-vous à l'application pour le consulter.</p>
</body>
</html>
