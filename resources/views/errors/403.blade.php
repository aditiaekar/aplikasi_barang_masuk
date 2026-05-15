<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f8fafc; padding: 24px;">
        <div style="max-width: 480px; width: 100%; background: #ffffff; padding: 32px; border-radius: 18px; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08); text-align: center;">
            <h1 style="font-size: 48px; margin: 0; color: #991b1b;">
                403
            </h1>

            <h2 style="margin: 12px 0 8px; color: #111827;">
                Akses Ditolak
            </h2>

            <p style="color: #6b7280; margin-bottom: 24px;">
                Anda tidak memiliki izin untuk mengakses halaman ini.
            </p>

            <a
                href="{{ route('dashboard') }}"
                style="display: inline-block; padding: 10px 18px; border-radius: 10px; background: #991b1b; color: #ffffff; text-decoration: none; font-weight: 600;"
            >
                Kembali ke Dashboard
            </a>
        </div>
    </main>
</body>
</html>