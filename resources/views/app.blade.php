<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab LIMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <style>
        body { background-color: #f5f6fa; }
        .sidebar {
            min-height: 100vh;
            background-color: #2c3e50;
            color: #ecf0f1;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            display: block;
            padding: 0.6rem 1rem;
        }
        .sidebar a:hover, .sidebar .active {
            background-color: #34495e;
        }
        .sidebar-header {
            padding: 1rem;
            font-weight: 700;
            font-size: 1.1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <aside class="col-md-2 sidebar p-0">
            <div class="sidebar-header">
                Lab LIMS
            </div>
            <a href="{{ route('receiving.index') }}" class="{{ request()->routeIs('receiving.*') ? 'active' : '' }}">
                Service – Receiving
            </a>
            {{-- later: other menu items: Technical, Releasing, Billing, AR --}}
        </aside>

        <main class="col-md-10 p-4">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
