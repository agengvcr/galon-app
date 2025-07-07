<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Laravel App')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Add Animate.css library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>    <!-- Add Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #2196F3;      /* Main blue */
            --primary-dark: #1976D2;       /* Darker blue for hover states */
            --primary-light: #BBDEFB;      /* Light blue for backgrounds */
            --secondary-color: #64B5F6;    /* Secondary blue */
            --background-color: #F5F9FF;   /* Very light blue background */
            --text-color: #1A237E;         /* Dark blue text */
            --text-muted: #5C6BC0;         /* Muted blue text */
            --border-color: #E3F2FD;       /* Light blue border */
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: var(--text-color);
            background-color: var(--background-color);
        }

        main {
            flex: 1 0 auto;
        }

        /* Flat Navbar Styles */
        .navbar {
            background-color: white !important;
            box-shadow: 0 2px 4px rgba(33, 150, 243, 0.1);
            border-bottom: 1px solid var(--border-color);
        }

        .navbar-light {
            background-color: white !important;
        }

        .nav-link {
            color: var(--text-color) !important;
            transition: all 0.2s ease;
            padding: 0.5rem 1rem;
            border-radius: 4px;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
            background-color: var(--primary-light);
        }

        /* Flat Button Styles */
        .btn {
            border-radius: 4px;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* Modal Styles */
        .modal-content {
            border: none;
            box-shadow: 0 3px 6px rgba(33, 150, 243, 0.16);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            background-color: white;
        }

        .modal-footer {
            border-top: 1px solid var(--border-color);
            background-color: white;
        }

        /* Card Styles */
        .card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(33, 150, 243, 0.1);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--border-color);
            color: var(--primary-color);
        }

        /* Footer Styles */
        .footer {
            background-color: white !important;
            border-top: 1px solid var(--border-color);
            padding: 1rem 0;
        }

        .footer .text-muted {
            color: var(--text-muted) !important;
        }

        /* Custom Animation Classes */
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(33, 150, 243, 0.2);
        }

        /* DataTables Customization */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border: none !important;
            background: none !important;
            color: var(--text-color) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: var(--primary-light) !important;
            border: none !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: var(--primary-color) !important;
            color: white !important;
        }

        /* Form Controls */
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
        }

        /* Select2 Customization */
        .select2-container--default .select2-selection--single:focus,
        .select2-container--default .select2-selection--multiple:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
        }
    </style>
    @yield('styles')
</head>
<body class="d-flex flex-column h-100">
    <header>
        <nav class="navbar navbar-expand-md navbar-light bg-light">
            <div class="container">
                @auth
                <a class="navbar-brand" href="{{ url('/') }}"><i class="fas fa-home me-2"></i></a>
                @endauth
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('transactions.index') }}">
                                    <i class="fas fa-exchange-alt me-1"></i> Transaksi
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('debts.index') }}">
                                    <i class="fas fa-money-bill me-1"></i> Hutang
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('customers.index') }}">
                                    <i class="fas fa-users me-1"></i> Pelanggan
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarKaryawan" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-tie me-1"></i> Karyawan
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarKaryawan">
                                    <li><a class="dropdown-item" href="{{ route('employees.index') }}">Data Karyawan</a></li>
                                    <li><a class="dropdown-item" href="{{ route('employee-attendance.index') }}">Absen Karyawan</a></li>
                                    <li><a class="dropdown-item" href="{{ route('reports.payroll') }}">Laporan Penggajian</a></li>
                                    <li><a class="dropdown-item" href="{{ route('operational-expenses.index') }}">Biaya Operasional</a></li>
                                    <li><a class="dropdown-item" href="{{ route('employee-loans.index') }}">Pinjaman Karyawan</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn nav-link border-0 bg-transparent">
                                        <i class="fas fa-sign-out-alt me-1"></i> Keluar
                                    </button>
                                </form>
                            </li>
                        @else
                          
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="flex-shrink-0">
        <div class="container py-4">
            @yield('content')
        </div>
    </main>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">© {{ date('Y') }} Ageng Developer</span>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (required for DataTables and Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
    <!-- Add Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @yield('scripts')
</body>
</html>
