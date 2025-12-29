<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="dark" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>@yield('title') | CTree</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="" name="description" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/images/logos/logo.png') }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

    @yield('extra-css')
</head>

<body>
    <!-- Top Bar Start -->
    <div class="topbar d-print-none">
        <div class="container-fluid">
            <nav class="topbar-custom d-flex justify-content-between" id="topbar-custom">
                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                    <li>
                        <button class="nav-link mobile-menu-btn nav-icon" id="togglemenu">
                            <i class="iconoir-menu"></i>
                        </button>
                    </li>
                </ul>

                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                    <li class="dropdown topbar-item">
                        <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" data-bs-offset="0,19">
                            <img src="{{ asset('assets/images/logos/logo.png') }}" alt="" class="thumb-md rounded-circle">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end py-0">
                            <div class="d-flex align-items-center dropdown-item py-2 bg-secondary-subtle">
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('assets/images/logos/logo.png') }}" alt="" class="thumb-md rounded-circle">
                                </div>
                                <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                                    <h6 class="my-0 fw-medium text-dark fs-13">{{ auth()->user()->name }}</h6>
                                    <small class="text-muted mb-0">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</small>
                                </div>
                            </div>
                            <div class="dropdown-divider mt-0"></div>
                            <small class="text-muted px-2 pb-1 d-block">Account</small>
                            <a class="dropdown-item" href="#"><i class="las la-user fs-18 me-1 align-text-bottom"></i> Profile</a>
                            <div class="dropdown-divider mb-0"></div>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger w-100 text-start">
                                    <i class="las la-power-off fs-18 me-1 align-text-bottom"></i> Logout
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    <!-- Top Bar End -->

    <!-- Sidebar Start -->
    <div class="startbar d-print-none">
        <!--start brand-->
        <div class="brand">
            <a href="{{ route('dashboard') }}" class="logo">
                <span>
                    <img src="{{ asset('assets/images/logos/logo.png') }}" alt="logo-small" class="logo-sm">
                </span>
                <span class="">
                    <img src="{{ asset('assets/images/logos/logo.png') }}" alt="logo-large" class="logo-lg logo-light">
                    <img src="{{ asset('assets/images/logos/logo.png') }}" alt="logo-large" class="logo-lg logo-dark">
                </span>
            </a>
        </div>
        <!--end brand-->

        <!--start startbar-menu-->
        <div class="startbar-menu">
            <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
                <div class="d-flex align-items-start flex-column w-100">
                    <!-- Navigation -->
                    <ul class="navbar-nav mb-auto w-100">
                        <li class="menu-label mt-2">
                            <span>Main</span>
                        </li>

                        <!-- Dashboard - All Users -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="iconoir-home menu-icon"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        @if(auth()->user()->role === 'super_admin')
                            <!-- Super Admin Menu -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                    <i class="iconoir-group menu-icon"></i>
                                    <span>Users</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}" href="{{ route('leads.index') }}">
                                    <i class="iconoir-send menu-icon"></i>
                                    <span>Leads
                                        @php
                                            $pendingCount = \App\Models\Lead::where('status', 'pending')->count();
                                        @endphp
                                        @if($pendingCount > 0)
                                            <span class="badge bg-danger ms-2">{{ $pendingCount }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                                    <i class="iconoir-user menu-icon"></i>
                                    <span>Customers</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}" href="{{ route('jobs.index') }}">
                                    <i class="iconoir-task-list menu-icon"></i>
                                    <span>Work Orders</span>
                                </a>
                            </li>

                            <li class="menu-label mt-3">
                                <span>Settings</span>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                                    <i class="iconoir-settings menu-icon"></i>
                                    <span>Settings</span>
                                </a>
                            </li>

                        @elseif(auth()->user()->role === 'lead_manager')
                            <!-- Lead Manager Menu -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}" href="{{ route('leads.index') }}">
                                    <i class="iconoir-send menu-icon"></i>
                                    <span>My Leads
                                        @php
                                            $myLeadsCount = \App\Models\Lead::where('created_by', auth()->id())
                                                ->where('status', 'pending')
                                                ->count();
                                        @endphp
                                        @if($myLeadsCount > 0)
                                            <span class="badge bg-warning ms-2">{{ $myLeadsCount }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                                    <i class="iconoir-user menu-icon"></i>
                                    <span>Customers</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}" href="{{ route('jobs.index') }}">
                                    <i class="iconoir-task-list menu-icon"></i>
                                    <span>Jobs</span>
                                </a>
                            </li>

                            @elseif(auth()->user()->role === 'telecallers')
                            <!-- Telecaller Menu -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('leads.*') && !request()->routeIs('leads.whatsapp') && !request()->routeIs('leads.google-ads') ? 'active' : '' }}" href="{{ route('leads.index') }}">
                                    <i class="iconoir-send menu-icon"></i>
                                    <span>My Leads
                                        @php
                                            $assignedLeadsCount = \App\Models\Lead::where('assigned_to', auth()->id())
                                                ->where('status', 'pending')
                                                ->count();
                                            $todayFollowups = \App\Models\LeadFollowup::where('assigned_to', auth()->id())
                                                ->where('status', 'pending')
                                                ->whereDate('followup_date', today())
                                                ->count();
                                        @endphp
                                        @if($assignedLeadsCount > 0 || $todayFollowups > 0)
                                            <span class="badge bg-info ms-2">
                                                {{ $assignedLeadsCount }}
                                                @if($todayFollowups > 0)
                                                    <i class="las la-bell" title="{{ $todayFollowups }} followups today"></i>
                                                @endif
                                            </span>
                                        @endif
                                    </span>
                                </a>
                            </li>

                            <!-- WhatsApp Leads -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('leads.whatsapp') ? 'active' : '' }}" href="{{ route('leads.whatsapp') }}">
                                    <i class="lab la-whatsapp menu-icon"></i>
                                    <span>WhatsApp Leads
                                        @php
                                            $whatsappSource = \App\Models\LeadSource::where('name', 'WhatsApp')->first();
                                            $whatsappCount = $whatsappSource ?
                                                \App\Models\Lead::where('assigned_to', auth()->id())
                                                    ->where('lead_source_id', $whatsappSource->id)
                                                    ->where('status', 'pending')
                                                    ->count() : 0;
                                        @endphp
                                        @if($whatsappCount > 0)
                                            <span class="badge bg-success ms-2">{{ $whatsappCount }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>

                            <!-- Google Ads Leads -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('leads.google-ads') ? 'active' : '' }}" href="{{ route('leads.google-ads') }}">
                                    <i class="lab la-google menu-icon"></i>
                                    <span>Google Ads Leads
                                        @php
                                            $googleAdsSource = \App\Models\LeadSource::where('name', 'Google Ads')->first();
                                            $googleAdsCount = $googleAdsSource ?
                                                \App\Models\Lead::where('assigned_to', auth()->id())
                                                    ->where('lead_source_id', $googleAdsSource->id)
                                                    ->where('status', 'pending')
                                                    ->count() : 0;
                                        @endphp
                                        @if($googleAdsCount > 0)
                                            <span class="badge bg-warning ms-2">{{ $googleAdsCount }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                                    <i class="iconoir-user menu-icon"></i>
                                    <span>Customers</span>
                                </a>
                            </li>

                            <!-- Jobs Menu for Telecallers -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}" href="{{ route('jobs.index') }}">
                                    <i class="iconoir-task-list menu-icon"></i>
                                    <span>Work Orders
                                        @php
                                            // Count jobs from leads created by this telecaller
                                            $myJobsCount = \App\Models\Job::whereHas('lead', function($query) {
                                                $query->where('assigned_to', auth()->id());
                                            })->whereIn('status', ['pending', 'confirmed'])->count();
                                        @endphp
                                        @if($myJobsCount > 0)
                                            <span class="badge bg-primary ms-2">{{ $myJobsCount }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>


                        @elseif(auth()->user()->role === 'field_staff')
                            <!-- Field Staff Menu -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}" href="{{ route('jobs.index') }}">
                                    <i class="iconoir-task-list menu-icon"></i>
                                    <span>My Work Orders
                                        @php
                                            $myJobsCount = \App\Models\Job::where('assigned_to', auth()->id())
                                                ->where('status', 'pending')
                                                ->count();
                                        @endphp
                                        @if($myJobsCount > 0)
                                            <span class="badge bg-primary ms-2">{{ $myJobsCount }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                                    <i class="iconoir-user menu-icon"></i>
                                    <span>Customers</span>
                                </a>
                            </li>
                        @endif

                    </ul>

                </div>
            </div><!--end startbar-collapse-->
        </div><!--end startbar-menu-->

    </div><!--end startbar-->
    <div class="startbar-overlay d-print-none"></div>
    <!-- Sidebar End -->

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content-->
        <div class="page-content">
            <div class="container-fluid">
                @if($errors->any())
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- @if(session('success'))
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                    </div>
                @endif --}}

                @yield('content')
            </div><!-- container -->
        </div><!-- page-content -->

    </div>
    <!-- end page-wrapper -->


    <!-- Javascript -->
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

    <script>
        // Set year safely
        if (document.getElementById('year')) {
            document.getElementById('year').textContent = new Date().getFullYear();
        }
    </script>

    @yield('extra-scripts')
</body>
</html>
