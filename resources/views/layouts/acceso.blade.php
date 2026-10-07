<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Iniciar sesión') · Togas CPC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background: linear-gradient(160deg, #4f46e5, #5b21b6);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1f2937;
        }

        .acceso-card {
            width: 100%;
            max-width: @yield('ancho', '420px');
            background: #fff;
            border-radius: 22px;
            padding: 32px 28px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
        }

        .acceso-marca {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
        }

        .acceso-logo {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            background: #4f46e5;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .acceso-marca h1 {
            font-size: 1.25rem;
            margin: 0;
            font-weight: 700;
        }

        .acceso-marca small {
            color: #6b7280;
        }

        .btn-acceso {
            background: #4f46e5;
            border-color: #4f46e5;
        }

        .btn-acceso:hover {
            background: #4338ca;
            border-color: #4338ca;
        }
    </style>
</head>
<body>
    <main class="acceso-card">
        <div class="acceso-marca">
            <div class="acceso-logo">CPC</div>
            <div>
                <h1>Togas CPC</h1>
                <small>Centro Profesional de Cómputo</small>
            </div>
        </div>

        @yield('content')
    </main>
</body>
</html>
