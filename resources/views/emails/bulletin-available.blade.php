@component('mail::message')
# Nouveau Bulletin Disponible

Bonjour,

Le bulletin de {{ $etudiant->prenom }} {{ $etudiant->nom }} pour le semestre {{ $semestre }} est disponible.
Moyenne générale : {{ $moyenneGenerale }}.

Vous pouvez le consulter ou le télécharger depuis votre portail.

@component('mail::button', ['url' => url('/dashboard')])
Consulter le bulletin
@endcomponent

Merci,
{{ config('app.name') }}
@endcomponent
