<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · {{ config('app.name') }} admin</title>

    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard/vendor/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard/admin.css') }}">
    @stack('styles')
</head>

<body class="admin">
    @php
        $me = Auth::user();
        $pendingOrders = \App\Models\Order::where('status', 'pending')->count();
        $initials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($me->name ?? 'A', 0, 1));
    @endphp
    <div class="app">

        <aside class="sb" id="sidebar">
            <a href="{{ route('dashboard') }}" class="sb-brand">
                <span class="sb-mark">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(config('app.name'), 0, 1)) }}</span>
                <span>
                    <span class="sb-brand-name d-block">{{ config('app.name') }}</span>
                    <span class="sb-brand-sub">Marketplace admin</span>
                </span>
            </a>

            <x-nav />

            <div class="sb-foot">
                <a href="{{ route('home') }}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt me-2"></i>View storefront</a>
            </div>
        </aside>
        <div class="scrim"></div>

        <div class="main">
            <header class="tb">
                <button class="tb-toggle" type="button" data-sb-toggle aria-label="Open menu"><i class="fas fa-bars"></i></button>

                <form class="tb-search" action="{{ route('orders.index') }}" method="get" role="search">
                    <i class="fas fa-search"></i>
                    <input type="search" name="q" placeholder="Find an order by number, name or email" aria-label="Search orders">
                </form>

                <div class="tb-right">
                    <a href="{{ route('orders.index', ['status' => 'pending']) }}" class="tb-btn" title="Pending orders" aria-label="Pending orders: {{ $pendingOrders }}">
                        <i class="far fa-bell"></i>
                        @if ($pendingOrders)
                            <span class="tb-dot">{{ $pendingOrders }}</span>
                        @endif
                    </a>

                    <div class="dropdown">
                        <button class="tb-user" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar">{{ $initials }}</span>
                            <span class="tb-user-text">
                                <span class="tb-user-name d-block">{{ $me->name }}</span>
                                <span class="tb-user-role">{{ ($me->super_admin ?? false) ? 'Super admin' : 'Admin' }}</span>
                            </span>
                            <i class="fas fa-chevron-down small muted"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('dashboard.profile.edit') }}">Your profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="post">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Log out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <main class="page">
                <div class="page-head">
                    <div>
                        <ol class="crumbs">
                            @section('breadcrump')
                                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            @show
                        </ol>
                        <h1>@yield('title')</h1>
                        @hasSection('subtitle')
                            <p class="page-sub">@yield('subtitle')</p>
                        @endif
                    </div>
                    @hasSection('actions')
                        <div class="page-actions">@yield('actions')</div>
                    @endif
                </div>

                @foreach (['success', 'info', 'warning', 'danger'] as $flash)
                    @if (session()->has($flash))
                        <div class="alert alert-{{ $flash }}" role="status">{{ session($flash) }}</div>
                    @endif
                @endforeach

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Shared confirmation dialog: any <form data-confirm="..."> opens it -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px">
                <div class="modal-body p-4">
                    <h2 class="h5" id="confirmTitle">Are you sure?</h2>
                    <p class="muted mb-4" data-confirm-message></p>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" data-confirm-ok>Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/dashboard/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/dashboard/admin.js') }}"></script>
    @stack('script')
</body>

</html>
