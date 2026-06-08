<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema de control - togas')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            margin: 0;
            background: #f4f6fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1f2937;
        }

        .app-shell {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            position: sticky;
            top: 0;
            background: linear-gradient(180deg, #4f46e5, #5b21b6);
            color: white;
            padding: 24px 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 4px 0 20px rgba(0,0,0,0.08);
            overflow-y: auto;
            flex-shrink: 0;
        }

        .brand-box {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            background: white;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .brand-text h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }

        .brand-text small {
            opacity: 0.85;
            font-size: 12px;
        }

        .sidebar-user {
            background: rgba(255,255,255,0.12);
            border-radius: 16px;
            padding: 14px;
            margin-bottom: 20px;
        }

        .sidebar-user .name {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .sidebar-user .role {
            font-size: 13px;
            opacity: 0.9;
        }

        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.92);
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,0.14);
            color: white;
            transform: translateX(2px);
        }

        .sidebar-nav .nav-link.active {
            background: rgba(255,255,255,0.18);
            color: white;
            font-weight: 700;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .topbar {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .topbar h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }

        .topbar p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }

        .content-area {
            padding: 28px;
            flex: 1;
            overflow-y: auto;
        }

        .page-card {
            background: white;
            border-radius: 22px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            border: 1px solid #eef2f7;
        }

        .quick-grid,
        .stats-grid {
            display: grid;
            gap: 18px;
        }

        .quick-grid {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .quick-card,
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            border: 1px solid #edf2f7;
            box-shadow: 0 8px 22px rgba(0,0,0,0.04);
        }

        .quick-card {
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }

        .quick-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 28px rgba(0,0,0,0.08);
        }

        .quick-icon,
        .stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 14px;
        }

        .quick-icon {
            background: #eef2ff;
        }

        .stat-icon {
            background: #f3f4f6;
        }

        .card-title-mini {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .card-desc-mini {
            color: #6b7280;
            font-size: 13px;
            margin: 0;
        }

        .stat-label {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 30px;
            font-weight: 800;
            color: #111827;
            line-height: 1.1;
        }

        .stat-sub {
            color: #6b7280;
            font-size: 13px;
            margin-top: 6px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 18px;
            color: #111827;
        }

        .table-modern thead th {
            background: #f8fafc;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 1px solid #e5e7eb;
        }

        .table-modern td,
        .table-modern th {
            vertical-align: middle;
            padding: 14px 12px;
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            height: 32px;
            border-radius: 999px;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
        }

        .badge-entrada {
            background: #dcfce7;
            color: #166534;
        }

        .badge-ajuste {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-alquiler {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-devolucion {
            background: #cffafe;
            color: #155e75;
        }

        .badge-danger-soft {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-toga {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-birrete {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-collarin {
            background: #cffafe;
            color: #155e75;
        }

        .amount-positive {
            color: #16a34a;
            font-weight: 700;
        }

        .amount-negative {
            color: #dc2626;
            font-weight: 700;
        }

        .action-main-btn {
            min-width: 130px;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border-radius: 14px;
            border: 1px solid #dbe3ef;
            padding: 10px 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .btn {
            font-weight: 600;
        }

        .toga-item {
            transition: all 0.2s ease-in-out;
        }

        .toga-item:has(.producto-check:checked) {
            border-color: #0d6efd !important;
            background: #f8fbff;
        }

        .panel-configuracion {
            background: #ffffff;
        }

        @media (max-width: 992px) {
            .app-shell {
                flex-direction: column;
                height: auto;
                overflow: auto;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                overflow-y: visible;
            }

            .main-content {
                height: auto;
                overflow: visible;
            }

            .topbar {
                padding: 18px;
                align-items: flex-start;
                gap: 12px;
                flex-direction: column;
            }

            .content-area {
                padding: 18px;
                overflow-y: visible;
            }
        }
    </style>
</head>

<body>
    <div class="app-shell">

        <aside class="sidebar">
            <div>
                <div class="brand-box">
                    <div class="brand-logo">CPC</div>
                    <div class="brand-text">
                        <h4>Togas CPC</h4>
                        <small>Sistema de control</small>
                    </div>
                </div>

                <div class="sidebar-user">
                    <div class="name">👤 Administración</div>
                    <div class="role">Centro Profesional de Cómputo CPC</div>
                </div>

                <nav class="nav flex-column sidebar-nav">
                    <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}">
                        📊 Dashboard
                    </a>

                    <a class="nav-link {{ request()->is('inventario/movimientos') ? 'active' : '' }}" href="{{ url('/inventario/movimientos') }}">
                        📦 Movimientos
                    </a>

                    <a class="nav-link {{ request()->is('productos-web*') ? 'active' : '' }}" href="{{ url('/productos-web') }}">
                        👗 Productos
                    </a>

                    <a class="nav-link {{ request()->is('clientes-web*') ? 'active' : '' }}" href="{{ url('/clientes-web') }}">
                        👥 Clientes
                    </a>

                    <a class="nav-link {{ request()->is('alquileres-web*') ? 'active' : '' }}" href="{{ url('/alquileres-web') }}">
                        🧾 Alquileres
                    </a>
                </nav>
            </div>
        </aside>

        <main class="main-content">
            <div class="topbar">
                <div>
                    <h1>@yield('page_title', 'Sistema de control - togas')</h1>
                    <p>@yield('page_subtitle', 'Administra alquileres, pagos e inventario de forma ordenada')</p>
                </div>

                <div class="text-end">
                    <span class="badge text-bg-light rounded-pill px-3 py-2">CPC</span>
                </div>
            </div>

            <div class="content-area">
                @yield('content')
            </div>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const confirmForms = document.querySelectorAll('.confirm-action-form');

            confirmForms.forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    const title = form.dataset.title || '¿Estás seguro?';
                    const text = form.dataset.text || 'Esta acción realizará cambios en el sistema.';
                    const icon = form.dataset.icon || 'warning';
                    const confirmButtonText = form.dataset.confirm || 'Sí, continuar';
                    const cancelButtonText = form.dataset.cancel || 'Cancelar';

                    Swal.fire({
                        title: title,
                        text: text,
                        icon: icon,
                        showCancelButton: true,
                        confirmButtonText: confirmButtonText,
                        cancelButtonText: cancelButtonText,
                        confirmButtonColor: '#4f46e5',
                        cancelButtonColor: '#6b7280',
                        reverseButtons: true,
                        customClass: {
                            popup: 'rounded-4',
                            confirmButton: 'rounded-pill px-4',
                            cancelButton: 'rounded-pill px-4'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    @if(session('success'))
        <script>
            Swal.fire({
                title: '¡Listo!',
                text: @json(session('success')),
                icon: 'success',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#4f46e5',
                customClass: {
                    popup: 'rounded-4',
                    confirmButton: 'rounded-pill px-4'
                }
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                title: 'Ocurrió un problema',
                text: @json(session('error')),
                icon: 'error',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#dc2626',
                customClass: {
                    popup: 'rounded-4',
                    confirmButton: 'rounded-pill px-4'
                }
            });
        </script>
    @endif
</body>
</html>