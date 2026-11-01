<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>NEP · Ecosistema Empresarial Petit</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900"
      style="background-image: linear-gradient(to bottom, #eef2ff, #ffffff 55%, #f9fafb);">
    <div class="flex min-h-screen flex-col">
        <main class="flex flex-1 flex-col items-center justify-center px-6 text-center">
            <x-application-logo class="h-16 w-16" />

            <h1 class="mt-6 text-4xl font-semibold tracking-tight sm:text-5xl">NEP</h1>
            <p class="mt-2 text-lg text-gray-600">Ecosistema Empresarial Petit</p>

            <p class="mt-6 max-w-md text-sm leading-relaxed text-gray-500">
                Plataforma de coordinación empresarial para empresas y especialistas.
                El acceso es solo para usuarios autorizados.
            </p>

            <a href="{{ route('login') }}"
               class="mt-8 inline-flex items-center rounded-md bg-indigo-600 px-6 py-3 text-sm font-medium text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Iniciar sesión
            </a>

            <p class="mt-4 text-xs text-gray-400">
                ¿Necesitas acceso? Solicítalo al administrador de tu organización.
            </p>
        </main>

        <footer class="py-6 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} Ecosistema Empresarial Petit
        </footer>
    </div>
</body>
</html>