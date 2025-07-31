<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Gestion Ecole') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">
    <nav class="bg-white shadow">
        <div class="container mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <a href="{{ route('home') }}"
                        class="flex items-center text-xl font-bold">{{ config('app.name') }}</a>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        @if (auth()->user()->role->name === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-blue-500">Tableau de
                                bord</a>
                            <a href="{{ route('admin.classes.index') }}"
                                class="text-gray-700 hover:text-blue-500">Classes</a>
                            <a href="{{ route('admin.etudiants.index') }}"
                                class="text-gray-700 hover:text-blue-500">Étudiants</a>
                            <a href="{{ route('admin.enseignants.index') }}"
                                class="text-gray-700 hover:text-blue-500">Enseignants</a>
                            <a href="{{ route('admin.matieres.index') }}"
                                class="text-gray-700 hover:text-blue-500">Matières</a>
                            <a href="{{ route('admin.parents.index') }}"
                                class="text-gray-700 hover:text-blue-500">Parents</a>
                        @elseif (auth()->user()->role->name === 'enseignant')
                            <a href="{{ route('enseignant.dashboard') }}"
                                class="text-gray-700 hover:text-blue-500">Notes</a>
                        @elseif (auth()->user()->role->name === 'eleve')
                            <a href="{{ route('eleve.dashboard') }}" class="text-gray-700 hover:text-blue-500">Mes
                                notes</a>
                        @elseif (auth()->user()->role->name === 'parent')
                            <a href="{{ route('parent.dashboard') }}" class="text-gray-700 hover:text-blue-500">Mes
                                enfants</a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="text-gray-700 hover:text-blue-500">Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-blue-500">Déconnexion</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-500">Connexion</a>
                        <a href="{{ route('register') }}" class="text-gray-700 hover:text-blue-500">Inscription</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    <main class="py-4">
        @yield('content')
    </main>
</body>

</html>
