<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SmartStock') | SmartStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ url('/') }}">SmartStock</a>

        @auth
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">Inventory</a></li>

                    @if (auth()->user()->hasRole(\App\Models\Role::MANAGER, \App\Models\Role::WAREHOUSE))
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('stock_entry.*') ? 'active' : '' }}" href="{{ route('stock_entry.index') }}">Stock Entry</a></li>
                    @endif

                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('stock_movement.*') ? 'active' : '' }}" href="{{ route('stock_movement.index') }}">Movements</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('alerts.*') ? 'active' : '' }}" href="{{ route('alerts.index') }}">Alerts</a></li>

                    @if (auth()->user()->hasRole(\App\Models\Role::MANAGER, \App\Models\Role::WAREHOUSE))
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('reports') ? 'active' : '' }}" href="{{ route('reports') }}">Reports</a></li>
                    @endif

                    @if (auth()->user()->hasRole(\App\Models\Role::MANAGER))
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Users</a></li>
                    @endif
                </ul>

                <span class="navbar-text me-3 text-white-50">
                    {{ auth()->user()->name }} ({{ auth()->user()->role->role_name }})
                </span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        @endauth
    </div>
</nav>

<main class="container my-4">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="text-center text-muted small py-4">SmartStock &middot; BITU 3923 Group K27</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
