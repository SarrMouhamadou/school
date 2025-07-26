<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin de {{ $bulletin->etudiant->prenom }} {{ $bulletin->etudiant->nom }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 30px;
            color: #333;
        }

        h1, h2, h3 {
            text-align: center;
        }

        .info {
            margin-bottom: 20px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #444;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
        }

        .footer {
            margin-top: 30px;
            font-style: italic;
            text-align: center;
        }

        .mention {
            font-weight: bold;
            color: #0a7f27;
        }

        .header {
            margin-bottom: 40px;
        }

        .etablissement {
            text-align: center;
        }

        .clear {
            clear: both;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="etablissement">
            <h2>École Supérieure de Technologie</h2>
            <p>Bulletin de notes</p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="info">
        <p><strong>Nom :</strong> {{ $bulletin->etudiant->prenom }} {{ $bulletin->etudiant->nom }}</p>
        <p><strong>Semestre :</strong> {{ $bulletin->semestre }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Matière</th>
                <th>Moyenne</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bulletin->moyennes_matieres as $matiere => $moyenne)
                <tr>
                    <td>{{ $matiere }}</td>
                    <td>{{ $moyenne }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="info">
        <p><strong>Moyenne générale :</strong> {{ $bulletin->moyenne_generale }}</p>
        <p><strong>Mention :</strong> <span class="mention">{{ $bulletin->mention }}</span></p>
        <p><strong>Rang :</strong> {{ $bulletin->rang }}</p>
    </div>

    <div class="footer">
        <p>Appréciation : {{ $bulletin->appreciation }}</p>
        <p>Fait le {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
    </div>

</body>
</html>
