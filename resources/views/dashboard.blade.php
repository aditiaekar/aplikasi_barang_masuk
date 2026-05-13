<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aplikasi Barang Masuk</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main style="padding: 32px;">
        <h1>Dashboard</h1>

        <p>
            Selamat datang, {{ auth()->user()->name }}.
        </p>

        <p>
            Role:
            <strong>{{ auth()->user()->role->display_name ?? '-' }}</strong>
        </p>

        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button
                type="submit"
                style="padding: 10px 16px; border: none; border-radius: 8px; background: #dc2626; color: #ffffff; cursor: pointer;"
            >
                Logout
            </button>
        </form>
    </main>
</body>
</html>