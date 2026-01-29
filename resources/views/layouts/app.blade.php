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

    @yield('extra-css')
    <style>
        /* Avatar Circle Styles */
        .avatar-circle {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
            transition: transform 0.2s;
        }

        .avatar-circle:hover {
            transform: scale(1.05);
        }

        .avatar-initials {
            color: white;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .avatar-circle-large {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .avatar-initials-large {
            color: white;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: 1px;
        }

        /* Dropdown improvements */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .dropdown-item {
            transition: all 0.2s;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            padding-left: 1.25rem;
        }

        /* Profile card at bottom of sidebar */
        .profile-sidebar-card {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 10px;
            padding: 12px;
            margin: 10px;
            border-left: 3px solid #667eea;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            display: block;
        }

        .profile-sidebar-card:hover {
            background: rgba(102, 126, 234, 0.15);
            transform: translateX(2px);
        }

        .profile-sidebar-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
            color: white;
            letter-spacing: 1px;
            flex-shrink: 0;
        }

        .profile-sidebar-info {
            flex-grow: 1;
            min-width: 0;
        }

        .profile-sidebar-name {
            font-size: 14px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .profile-sidebar-role {
            font-size: 12px;
            color: #667eea;
            font-weight: 500;
        }

        /* Account label above profile */
        .account-label {
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 600;
            color: #a0aec0;
            padding: 0 20px;
            margin-top: auto;
            margin-bottom: 8px;
        }

    </style>

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
                            <div class="avatar-circle">
                                <span class="avatar-initials"><i class="iconoir-user" style="color: #fff;"></i></span>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end py-0 shadow-lg" style="min-width: 250px;">
                            <div class="d-flex align-items-center dropdown-item py-3 bg-primary bg-gradient">
                                <div class="flex-shrink-0">
                                    <div class="avatar-circle-large">
                                        <span class="avatar-initials-large">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3 text-truncate">
                                    <h6 class="my-0 fw-semibold text-white">{{ auth()->user()->name }}</h6>
                                    <small class="text-white-50">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</small>
                                    @if(auth()->user()->branch)
                                        <small class="d-block text-white-50">
                                            <i class="las la-building"></i> {{ auth()->user()->branch->name }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="dropdown-divider my-0"></div>
                            <small class="text-muted px-3 py-2 d-block fw-semibold">Account</small>
                            <a class="dropdown-item py-2" href="{{ route('profile.show') }}">
                                <i class="las la-user-circle fs-18 me-2 align-text-bottom text-primary"></i> My Profile
                            </a>
                            <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                <i class="las la-user-edit fs-18 me-2 align-text-bottom text-info"></i> Edit Profile
                            </a>
                            <div class="dropdown-divider my-0"></div>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2 w-100 text-start">
                                    <i class="las la-power-off fs-18 me-2 align-text-bottom"></i> Logout
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
                    <img src="{{ asset('assets/images/logos/ctree-logo-small.png') }}" alt="logo-small" class="logo-sm">
                </span>
                <span class="">
                    <img src="{{ asset('assets/images/logos/ctree-logo-3.png') }}" alt="logo-large" class="logo-lg logo-light">
                    <img src="{{ asset('assets/images/logos/ctree-logo-3.png') }}" alt="logo-large" class="logo-lg logo-dark">
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
                                <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}">
                                    <i class="iconoir-list menu-icon"></i>
                                    <span>Services</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}" href="{{ route('leads.index') }}">
                                    <i class="iconoir-send menu-icon"></i>
                                    <span>Leads
                                        @php
                                            $pendingApproval = \App\Models\Lead::where('status', 'confirmed')->count();
                                        @endphp
                                        @if($pendingApproval > 0)
                                            <span class="badge bg-danger ms-2">{{ $pendingApproval }}</span>
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
                                    <span>Work Orders
                                        @php
                                            $pendingConfirmation = \App\Models\Job::where('status', 'confirmed')->count();
                                        @endphp
                                        @if($pendingConfirmation > 0)
                                            <span class="badge bg-danger ms-2">{{ $pendingConfirmation }}</span>
                                        @endif
                                    </span>
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
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                    <i class="iconoir-group menu-icon"></i>
                                    <span>Users</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}">
                                    <i class="iconoir-list menu-icon"></i>
                                    <span>Services</span>
                                </a>
                            </li>
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
                                    <span>Work Orders</span>
                                </a>
                            </li>

                            @elseif(auth()->user()->role === 'telecallers')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}">
                                    <i class="iconoir-list menu-icon"></i>
                                    <span>Services</span>
                                </a>
                            </li>
                            <!-- Telecaller Menu -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('leads.*') && !request()->routeIs('leads.whatsapp') && !request()->routeIs('leads.google-ads') ? 'active' : '' }}" href="{{ route('leads.index') }}">
                                    <i class="iconoir-send menu-icon"></i>
                                    <span>Leads
                                        @php
                                            $todayFollowups = \App\Models\Lead::where('assigned_to', auth()->id())
                                                ->whereNotIn('status', ['approved', 'confirmed', 'rejected'])
                                                ->count();
                                        @endphp
                                        @if($todayFollowups > 0)
                                            <span class="badge bg-info ms-2">
                                                {{ $todayFollowups }}
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
                                                    ->whereNotIn('status', ['approved', 'confirmed', 'rejected'])
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
                                                    ->whereNotIn('status', ['approved', 'confirmed', 'rejected'])
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
                                            // Count the number of pending jobs
                                            $myJobsCount = \App\Models\Job::where('assigned_to', auth()->id())->whereIn('status', ['pending'])->count();
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

                        <!-- Account Label -->
                        <div class="account-label mt-auto pt-3">
                            ACCOUNT
                        </div>

                        <!-- Combined Profile Card (clickable to go to profile) -->
                        <a href="{{ route('profile.show') }}" class="profile-sidebar-card text-decoration-none">
                            <div class="d-flex align-items-center">
                                <div class="profile-sidebar-avatar">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <div class="profile-sidebar-info ms-3">
                                    <div class="profile-sidebar-name">
                                        {{ auth()->user()->name }}
                                    </div>
                                    <div class="profile-sidebar-role">
                                        {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
                                    </div>
                                </div>
                            </div>
                        </a>

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
