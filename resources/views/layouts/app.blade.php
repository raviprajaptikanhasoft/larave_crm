<!DOCTYPE html>
<html>

<head>
    <title>CRM Contacts</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: #fff !important;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-3">
                <h5 class="text-primary">CRM Menu</h5>
                <div class="nav flex-column">
                    <a href="{{ route('contacts.index') }}"
                        class="nav-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}">Contacts</a>
                    <a href="{{ route('custom-fields.index') }}"
                        class="nav-link {{ request()->routeIs('custom-fields.*') ? 'active' : '' }}">Custom Fields</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 py-4">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('scripts')
</body>

</html>
