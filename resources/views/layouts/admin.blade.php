<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link href="https://cdn.datatables.net/v/bs5/dt-2.3.2/b-3.2.4/r-3.0.5/sc-2.4.3/sb-1.8.3/datatables.min.css" rel="stylesheet" integrity="sha384-mQEEjSQ3XypQ/tKmE0YCCWqvMMFqyKLeSosK1md3wuP2nESWnQsea3HNkR2a9W5l" crossorigin="anonymous">



    <style>
        :root {
            --primary-red: #9f1239;
            --primary-red-soft: #be123c;
            --primary-red-light: #fff1f2;
            --dark-text: #1f2937;
            --muted-text: #6b7280;
            --border-soft: #e5e7eb;
            --bg-soft: #f8fafc;
            --sidebar-width: 270px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(159, 18, 57, 0.08), transparent 32%),
                linear-gradient(135deg, #fff 0%, #f8fafc 45%, #fff1f2 100%);
            color: var(--dark-text);
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: rgba(255, 255, 255, 0.94);
            border-right: 1px solid rgba(229, 231, 235, 0.85);
            box-shadow: 12px 0 35px rgba(15, 23, 42, 0.04);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1030;
            transition: all 0.25s ease;
        }

        .sidebar-brand {
            height: 74px;
            padding: 1.2rem 1.4rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            border-bottom: 1px solid var(--border-soft);
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-red-soft));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 26px rgba(159, 18, 57, 0.25);
        }

        .brand-icon i {
            font-size: 1.45rem;
        }

        .brand-text h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--dark-text);
        }

        .brand-text span {
            font-size: 0.78rem;
            color: var(--muted-text);
        }

        .sidebar-menu {
            padding: 1rem;
        }

        .menu-label {
            margin: 1rem 0 0.55rem;
            padding: 0 0.75rem;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #9ca3af;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.78rem 0.9rem;
            margin-bottom: 0.35rem;
            border-radius: 14px;
            color: #4b5563;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.92rem;
            transition: all 0.2s ease;
        }

        .menu-item i {
            font-size: 1.25rem;
        }

        .menu-item:hover {
            background: var(--primary-red-light);
            color: var(--primary-red);
            transform: translateX(3px);
        }

        .menu-item.active {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-red-soft));
            color: #fff;
            box-shadow: 0 12px 25px rgba(159, 18, 57, 0.22);
        }

        .admin-main {
            width: 100%;
            min-height: 100vh;
            margin-left: var(--sidebar-width);
            transition: all 0.25s ease;
        }

        .admin-topbar {
            height: 74px;
            padding: 0 1.6rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.78);
            border-bottom: 1px solid rgba(229, 231, 235, 0.75);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .sidebar-toggle {
            width: 42px;
            height: 42px;
            border: 1px solid var(--border-soft);
            border-radius: 13px;
            background: #fff;
            color: var(--dark-text);
            display: none;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .sidebar-toggle:hover {
            background: var(--primary-red-light);
            color: var(--primary-red);
        }

        .page-title h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--dark-text);
        }

        .page-title span {
            font-size: 0.82rem;
            color: var(--muted-text);
        }

        .user-dropdown {
            border: 1px solid var(--border-soft);
            background: #fff;
            border-radius: 999px;
            padding: 0.42rem 0.7rem 0.42rem 0.42rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            color: var(--dark-text);
            text-decoration: none;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-red-light);
            color: var(--primary-red);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
        }

        .user-info {
            line-height: 1.15;
        }

        .user-info strong {
            display: block;
            font-size: 0.86rem;
        }

        .user-info small {
            color: var(--muted-text);
            font-size: 0.75rem;
        }

        .admin-content {
            padding: 1.6rem;
        }

        .admin-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(229, 231, 235, 0.8);
            border-radius: 22px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .sidebar-backdrop {
            display: none;
        }

        @media (max-width: 991.98px) {
            .admin-sidebar {
                left: calc(var(--sidebar-width) * -1);
            }

            .admin-sidebar.show {
                left: 0;
            }

            .admin-main {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: flex;
            }

            .sidebar-backdrop {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.45);
                z-index: 1025;
            }

            .sidebar-backdrop.show {
                display: block;
            }
        }

        @media (max-width: 575.98px) {
            .admin-topbar {
                padding: 0 1rem;
            }

            .admin-content {
                padding: 1rem;
            }

            .user-info {
                display: none;
            }

            .page-title span {
                display: none;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-brand">
                <div class="brand-icon">
                    <i class="bx bx-package"></i>
                </div>
                <div class="brand-text">
                    <h5>SIS - ABM</h5>
                    <span>Admin Panel</span>
                </div>
            </div>

            <div class="sidebar-menu">
                <div class="menu-label">Menu Utama</div>

                <a href="{{ route('admin.dashboard') }}"
                    class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bx bx-home-alt"></i>
                    <span>Dashboard</span>
                </a>

                <div class="menu-label">Master Data</div>

                <a href="{{ route('admin.categories.index') }}"
                    class="menu-item {{ request()->is('admin/categories*') ? 'active' : '' }}">
                    <i class="bx bx-category"></i>
                    <span>Kategori</span>
                </a>

                <a href="{{ route('admin.units.index') }}"
                    class="menu-item {{ request()->is('admin/units*') ? 'active' : '' }}">
                    <i class="bx bx-ruler"></i>
                    <span>Satuan</span>
                </a>

                <a href="{{ route('admin.suppliers.index') }}"
                    class="menu-item {{ request()->is('admin/suppliers*') ? 'active' : '' }}">
                    <i class="bx bx-store"></i>
                    <span>Supplier</span>
                </a>

                <a href="{{ route('admin.warehouses.index', 'warehouses') }}"
                    class="menu-item {{ request()->is('admin/warehouses*') ? 'active' : '' }}">
                    <i class="bx bx-building-house"></i>
                    <span>Gudang</span>
                </a>

                <div class="menu-label">Data Barang</div>

                <a href="{{ route('admin.items.index') }}"
                    class="menu-item {{ request()->routeIs('admin.items.*') ? 'active' : '' }}">
                    <i class="bx bx-box"></i>
                    <span>Data Barang</span>
                </a>

                <a href="{{ route('admin.stock-in-requests.index') }}"
                    class="menu-item {{ request()->routeIs('admin.stock-in-requests.*') ? 'active' : '' }}">
                    <i class="bx bx-log-in-circle"></i>
                    <span>Barang Masuk</span>
                </a>

                <a href="{{ route('admin.stock-out-requests.index') }}"
                    class="menu-item {{ request()->routeIs('admin.stock-out-requests.*') ? 'active' : '' }}">
                    <i class="bx bx-log-in-circle"></i>
                    <span>Barang Keluar</span>
                </a>

                <a href="{{ route('admin.stocks.index') }}"
                    class="menu-item {{ request()->routeIs('admin.stocks.*') ? 'active' : '' }}">
                    <i class="bx bx-package"></i>
                    <span>Stok Barang</span>
                </a>

                <a href="{{ route('admin.stock-mutations.index') }}"
                    class="menu-item {{ request()->routeIs('admin.stock-mutations.*') ? 'active' : '' }}">
                    <i class="bx bx-transfer-alt"></i>
                    <span>Mutasi Stok</span>
                </a>

                <div class="menu-label">Laporan</div>

                <a href="{{ route('admin.reports.stocks.export') }}" class="menu-item">
                    <i class="bx bx-package"></i>
                    <span>Laporan Stok Barang</span>
                </a>

                <a href="{{ route('admin.reports.stock-mutations.export') }}" class="menu-item">
                    <i class="bx bx-transfer-alt"></i>
                    <span>Laporan Mutasi Stok</span>
                </a>
            </div>
        </aside>

        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <main class="admin-main">
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button type="button" class="sidebar-toggle" id="sidebarToggle">
                        <i class="bx bx-menu"></i>
                    </button>

                    <div class="page-title">
                        <h4>@yield('page-title', 'Dashboard')</h4>
                        <span>@yield('page-subtitle', 'Ringkasan informasi aplikasi')</span>
                    </div>
                </div>

                <div class="dropdown">
                    <a href="#" class="user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="user-info">
                            <strong>{{ Auth::user()->name ?? 'Admin' }}</strong>
                            <small>Administrator</small>
                        </div>
                        <i class="bx bx-chevron-down"></i>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-4 mt-2">
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bx bx-log-out me-2"></i>
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </header>

            <section class="admin-content">
                @yield('content')
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        const sidebar = document.getElementById('adminSidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                sidebarBackdrop.classList.toggle('show');
            });
        }

        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarBackdrop.classList.remove('show');
            });
        }
    </script>

    @stack('scripts')
</body>

</html>
