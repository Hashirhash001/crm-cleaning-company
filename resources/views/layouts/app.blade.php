<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="dark" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>@yield('title') | CTree</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="" name="description" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/images/logos/ctree-logo-small.png') }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

    @yield('extra-css')
    <style>
        .avatar-circle {
            width: 35px; height: 35px; border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(102,126,234,.4);
            transition: transform .2s;
        }
        .avatar-circle:hover { transform: scale(1.05); }
        .avatar-initials { color: #fff; font-weight: 600; font-size: 14px; letter-spacing: .5px; }

        .avatar-circle-large {
            width: 50px; height: 50px; border-radius: 50%;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            border: 2px solid rgba(255,255,255,.3);
        }
        .avatar-initials-large { color: #fff; font-weight: 700; font-size: 18px; letter-spacing: 1px; }

        .dropdown-menu { border: none; box-shadow: 0 10px 30px rgba(0,0,0,.15); }
        .dropdown-item { transition: all .2s; font-weight: 500; }
        .dropdown-item:hover { background: #f8f9fa; padding-left: 1.25rem; }

        .menu-label span {
            font-size: 0.68rem; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase; color: #6c7a99; padding: 0 18px;
        }
        .menu-label { margin-top: 18px; margin-bottom: 4px; }

        .sidebar-divider {
            height: 1px; background: rgba(255,255,255,.07); margin: 10px 16px;
        }

        .profile-sidebar-card {
            background: rgba(102,126,234,.1); border-radius: 10px;
            padding: 12px; margin: 10px; border-left: 3px solid #667eea;
            transition: all .3s; cursor: pointer; text-decoration: none; display: block;
        }
        .profile-sidebar-card:hover { background: rgba(102,126,234,.18); transform: translateX(2px); }

        .profile-sidebar-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; font-weight: 700; color: #fff;
            letter-spacing: 1px; flex-shrink: 0;
        }
        .profile-sidebar-info { flex-grow: 1; min-width: 0; }
        .profile-sidebar-name {
            font-size: 13px; font-weight: 600; color: #fff;
            margin-bottom: 2px; white-space: nowrap;
            overflow: hidden; text-overflow: ellipsis;
        }
        .profile-sidebar-role { font-size: 11px; color: #667eea; font-weight: 500; }

        .account-label {
            text-transform: uppercase; font-size: 11px;
            font-weight: 600; color: #a0aec0;
            padding: 0 20px; margin-top: auto; margin-bottom: 8px;
        }
    </style>
</head>

<body>

<!-- ═══════════════════════════════════════
     TOP BAR
═══════════════════════════════════════ -->
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
                <!-- Notifications Bell Dropdown -->
                <li class="dropdown topbar-item me-2">
                    <a class="nav-link dropdown-toggle arrow-none nav-icon position-relative"
                       data-bs-toggle="dropdown" href="#" role="button" id="notificationBellBtn"
                       aria-haspopup="false" aria-expanded="false" data-bs-offset="0,19">
                        <i class="iconoir-bell fs-22" style="color: #4a5568;"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="notificationBadge">
                            0
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end py-0 shadow-lg" style="width:340px; max-width: 90vw;">
                        <div class="d-flex align-items-center justify-content-between px-3 py-3 bg-primary text-white rounded-top">
                            <h6 class="m-0 text-white fw-bold"><i class="las la-bell me-1"></i> Notifications</h6>
                            <button type="button" class="btn btn-sm btn-light text-primary fw-semibold rounded-pill py-1 px-2.5 shadow-sm" id="markAllReadBtn" style="font-size: 0.75rem;">
                                <i class="las la-check-double me-1"></i> Clear All
                            </button>
                        </div>
                        <div class="notification-list-container" id="notificationDropdownList" style="max-height: 320px; overflow-y: auto;">
                            <div class="text-center py-4 text-muted" id="emptyNotificationMsg">
                                <i class="las la-bell-slash fs-28 d-block mb-1"></i>
                                <small>No new notifications</small>
                            </div>
                        </div>
                        <div class="p-2 text-center border-top bg-light">
                            <a href="{{ route('leads.index') }}" class="text-primary fw-semibold fs-12 text-decoration-none">
                                View All Leads <i class="las la-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </li>

                <!-- User Profile Dropdown -->
                <li class="dropdown topbar-item">
                    <a class="nav-link dropdown-toggle arrow-none nav-icon"
                       data-bs-toggle="dropdown" href="#" role="button"
                       aria-haspopup="false" aria-expanded="false" data-bs-offset="0,19">
                        <div class="avatar-circle">
                            <span class="avatar-initials">
                                <i class="iconoir-user" style="color:#fff;"></i>
                            </span>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end py-0 shadow-lg" style="min-width:250px;">
                        <div class="d-flex align-items-center dropdown-item py-3 bg-primary bg-gradient">
                            <div class="flex-shrink-0">
                                <div class="avatar-circle-large">
                                    <span class="avatar-initials-large">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3 text-truncate">
                                <h6 class="my-0 fw-semibold text-white">{{ auth()->user()->name }}</h6>
                                <small class="text-white-50">
                                    {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
                                </small>
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
                            <i class="las la-user-circle fs-18 me-2 align-text-bottom text-primary"></i>My Profile
                        </a>
                        <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                            <i class="las la-user-edit fs-18 me-2 align-text-bottom text-info"></i>Edit Profile
                        </a>
                        <div class="dropdown-divider my-0"></div>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger py-2 w-100 text-start">
                                <i class="las la-power-off fs-18 me-2 align-text-bottom"></i>Logout
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>
    </div>
</div>


<!-- ═══════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════ -->
<div class="startbar d-print-none">

    {{-- Brand --}}
    <div class="brand">
        <a href="{{ route('dashboard') }}" class="logo">
            <span>
                <img src="{{ asset('assets/images/logos/ctree-logo-small.png') }}"
                     alt="logo-small" class="logo-sm">
            </span>
            <span>
                <img src="{{ asset('assets/images/logos/ctree-logo-3.png') }}"
                     alt="logo-large" class="logo-lg logo-light">
                <img src="{{ asset('assets/images/logos/ctree-logo-3.png') }}"
                     alt="logo-large" class="logo-lg logo-dark">
            </span>
        </a>
    </div>

    <div class="startbar-menu">
        <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
            <div class="d-flex align-items-start flex-column w-100">
                <ul class="navbar-nav mb-auto w-100">

                    @php $user = auth()->user(); @endphp

                    {{-- ── DASHBOARD (all roles) ── --}}
                    <li class="menu-label mt-0"><span>Main</span></li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                           href="{{ route('dashboard') }}">
                            <i class="iconoir-home menu-icon"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>


                    {{-- ════════════════════════════════════════
                         SUPER ADMIN
                    ════════════════════════════════════════ --}}
                    @if($user->role === 'super_admin')

                        {{-- SECTION 1: Sales --}}
                        <li class="menu-label"><span>Sales</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}"
                               href="{{ route('leads.index') }}">
                                <i class="iconoir-send menu-icon"></i>
                                <span>Leads
                                    @php $pendingLeads = \App\Models\Lead::where('status','confirmed')->count(); @endphp
                                    @if($pendingLeads > 0)
                                        <span class="badge bg-danger ms-1">{{ $pendingLeads }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('followups.*') ? 'active' : '' }}"
                               href="{{ route('followups.index') }}">
                                <i class="iconoir-calendar menu-icon"></i>
                                <span>Followups</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('jobs.index') || request()->routeIs('jobs.show') || request()->routeIs('jobs.create') ? 'active' : '' }}"
                               href="{{ route('jobs.index') }}">
                                <i class="iconoir-task-list menu-icon"></i>
                                <span>Work Orders
                                    @php $pendingJobs = \App\Models\Job::where('status','confirmed')->count(); @endphp
                                    @if($pendingJobs > 0)
                                        <span class="badge bg-danger ms-1">{{ $pendingJobs }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('jobs.completed*') ? 'active' : '' }}"
                               href="{{ route('jobs.completed') }}">
                                <i class="iconoir-check-circle menu-icon"></i>
                                <span>Completed Orders
                                    @php $staffPending = \App\Models\Job::where('status','staff_pending_approval')->count(); @endphp
                                    @if($staffPending > 0)
                                        <span class="badge bg-warning ms-1">{{ $staffPending }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}"
                               href="{{ route('services.index') }}">
                                <i class="iconoir-list menu-icon"></i>
                                <span>Services</span>
                            </a>
                        </li>

                        {{-- SECTION 2: Recruitment --}}
                        <li class="menu-label"><span>Recruitment</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.candidates.index') || request()->routeIs('recruitment.candidates.show') ? 'active' : '' }}"
                               href="{{ route('recruitment.candidates.index') }}">
                                <i class="iconoir-group menu-icon"></i>
                                <span>Candidates</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.candidates.create') ? 'active' : '' }}"
                               href="{{ route('recruitment.candidates.create') }}">
                                <i class="iconoir-user-plus menu-icon"></i>
                                <span>Add Candidate</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.departments.*') ? 'active' : '' }}"
                               href="{{ route('recruitment.departments.index') }}">
                                <i class="iconoir-building menu-icon"></i>
                                <span>Departments</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.positions.*') ? 'active' : '' }}"
                               href="{{ route('recruitment.positions.index') }}">
                                <i class="iconoir-suitcase menu-icon"></i>
                                <span>Positions</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.reports*') ? 'active' : '' }}"
                               href="{{ route('recruitment.reports') }}">
                                <i class="iconoir-graph-up menu-icon"></i>
                                <span>Reports</span>
                            </a>
                        </li>

                        {{-- SECTION 3: Users & Analytics --}}
                        <li class="menu-label"><span>Users & Analytics</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.*') && !request()->routeIs('users.performance*') ? 'active' : '' }}"
                               href="{{ route('users.index') }}">
                                <i class="iconoir-group menu-icon"></i>
                                <span>Users & Staff</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.performance*') ? 'active' : '' }}"
                               href="{{ route('users.performance') }}">
                                <i class="iconoir-stats-up-square menu-icon"></i>
                                <span>Performance</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"
                               href="{{ route('customers.index') }}">
                                <i class="iconoir-user menu-icon"></i>
                                <span>Customers</span>
                            </a>
                        </li>

                        {{-- SECTION 4: Settings --}}
                        <li class="menu-label"><span>Settings</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"
                               href="{{ route('settings.index') }}">
                                <i class="iconoir-settings menu-icon"></i>
                                <span>Settings</span>
                            </a>
                        </li>


                    {{-- ════════════════════════════════════════
                         LEAD MANAGER
                    ════════════════════════════════════════ --}}
                    @elseif($user->role === 'lead_manager')

                        {{-- SECTION 1: Sales --}}
                        <li class="menu-label"><span>Sales</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}"
                               href="{{ route('leads.index') }}">
                                <i class="iconoir-send menu-icon"></i>
                                <span>My Leads
                                    @php $myPending = \App\Models\Lead::where('created_by',$user->id)->where('status','pending')->count(); @endphp
                                    @if($myPending > 0)
                                        <span class="badge bg-warning ms-1">{{ $myPending }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('followups.*') ? 'active' : '' }}"
                               href="{{ route('followups.index') }}">
                                <i class="iconoir-calendar menu-icon"></i>
                                <span>Followups</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('jobs.index') || request()->routeIs('jobs.show') ? 'active' : '' }}"
                               href="{{ route('jobs.index') }}">
                                <i class="iconoir-task-list menu-icon"></i>
                                <span>Work Orders</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('jobs.completed*') ? 'active' : '' }}"
                               href="{{ route('jobs.completed') }}">
                                <i class="iconoir-check-circle menu-icon"></i>
                                <span>Completed Orders</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}"
                               href="{{ route('services.index') }}">
                                <i class="iconoir-list menu-icon"></i>
                                <span>Services</span>
                            </a>
                        </li>

                        {{-- SECTION 2: Recruitment --}}
                        <li class="menu-label"><span>Recruitment</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.candidates.index') || request()->routeIs('recruitment.candidates.show') ? 'active' : '' }}"
                               href="{{ route('recruitment.candidates.index') }}">
                                <i class="iconoir-user menu-icon"></i>
                                <span>Candidates</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.candidates.create') ? 'active' : '' }}"
                               href="{{ route('recruitment.candidates.create') }}">
                                <i class="iconoir-add-user menu-icon"></i>
                                <span>Add Candidate</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.departments.*') ? 'active' : '' }}"
                               href="{{ route('recruitment.departments.index') }}">
                                <i class="iconoir-building menu-icon"></i>
                                <span>Departments</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.positions.*') ? 'active' : '' }}"
                               href="{{ route('recruitment.positions.index') }}">
                                <i class="iconoir-suitcase menu-icon"></i>
                                <span>Positions</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.reports*') ? 'active' : '' }}"
                               href="{{ route('recruitment.reports') }}">
                                <i class="iconoir-reports menu-icon"></i>
                                <span>Reports</span>
                            </a>
                        </li>

                        {{-- SECTION 3: Users & Analytics --}}
                        <li class="menu-label"><span>Users & Analytics</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.*') && !request()->routeIs('users.performance*') ? 'active' : '' }}"
                               href="{{ route('users.index') }}">
                                <i class="iconoir-group menu-icon"></i>
                                <span>Users & Staff</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.performance*') ? 'active' : '' }}"
                               href="{{ route('users.performance') }}">
                                <i class="iconoir-stats-up-square menu-icon"></i>
                                <span>Performance</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"
                               href="{{ route('customers.index') }}">
                                <i class="iconoir-user menu-icon"></i>
                                <span>Customers</span>
                            </a>
                        </li>


                    {{-- ════════════════════════════════════════
                         TELECALLER
                    ════════════════════════════════════════ --}}
                    @elseif($user->role === 'telecallers')

                        {{-- SECTION 1: Sales --}}
                        <li class="menu-label"><span>Sales</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('leads.index') ? 'active' : '' }}"
                               href="{{ route('leads.index') }}">
                                <i class="iconoir-send menu-icon"></i>
                                <span>Leads
                                    @php $myLeads = \App\Models\Lead::where('assigned_to',$user->id)->whereNotIn('status',['approved','confirmed','rejected'])->count(); @endphp
                                    @if($myLeads > 0)
                                        <span class="badge bg-info ms-1">{{ $myLeads }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('leads.whatsapp') ? 'active' : '' }}"
                               href="{{ route('leads.whatsapp') }}">
                                <i class="lab la-whatsapp menu-icon"></i>
                                <span>WhatsApp
                                    @php
                                        $waSource = \App\Models\LeadSource::where('name','WhatsApp')->first();
                                        $waCount  = $waSource ? \App\Models\Lead::where('assigned_to',$user->id)->where('lead_source_id',$waSource->id)->whereNotIn('status',['approved','confirmed','rejected'])->count() : 0;
                                    @endphp
                                    @if($waCount > 0)
                                        <span class="badge bg-success ms-1">{{ $waCount }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('leads.google-ads') ? 'active' : '' }}"
                               href="{{ route('leads.google-ads') }}">
                                <i class="lab la-google menu-icon"></i>
                                <span>Google Ads
                                    @php
                                        $gaSource = \App\Models\LeadSource::where('name','Google Ads')->first();
                                        $gaCount  = $gaSource ? \App\Models\Lead::where('assigned_to',$user->id)->where('lead_source_id',$gaSource->id)->whereNotIn('status',['approved','confirmed','rejected'])->count() : 0;
                                    @endphp
                                    @if($gaCount > 0)
                                        <span class="badge bg-warning ms-1">{{ $gaCount }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('followups.*') ? 'active' : '' }}"
                               href="{{ route('followups.index') }}">
                                <i class="iconoir-calendar menu-icon"></i>
                                <span>Followups</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}"
                               href="{{ route('jobs.index') }}">
                                <i class="iconoir-task-list menu-icon"></i>
                                <span>Work Orders
                                    @php $myPendingJobs = \App\Models\Job::where('assigned_to',$user->id)->where('status','pending')->count(); @endphp
                                    @if($myPendingJobs > 0)
                                        <span class="badge bg-primary ms-1">{{ $myPendingJobs }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('jobs.completed*') ? 'active' : '' }}"
                               href="{{ route('jobs.completed') }}">
                                <i class="iconoir-check-circle menu-icon"></i>
                                <span>Completed Orders</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}"
                               href="{{ route('services.index') }}">
                                <i class="iconoir-list menu-icon"></i>
                                <span>Services</span>
                            </a>
                        </li>

                        {{-- SECTION 2: Recruitment --}}
                        <li class="menu-label"><span>Recruitment</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.candidates.index') || request()->routeIs('recruitment.candidates.show') ? 'active' : '' }}"
                               href="{{ route('recruitment.candidates.index') }}">
                                <i class="iconoir-user menu-icon"></i>
                                <span>Candidates</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.candidates.create') ? 'active' : '' }}"
                               href="{{ route('recruitment.candidates.create') }}">
                                <i class="iconoir-add-user menu-icon"></i>
                                <span>Add Candidate</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.departments.*') ? 'active' : '' }}"
                               href="{{ route('recruitment.departments.index') }}">
                                <i class="iconoir-building menu-icon"></i>
                                <span>Departments</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.positions.*') ? 'active' : '' }}"
                               href="{{ route('recruitment.positions.index') }}">
                                <i class="iconoir-suitcase menu-icon"></i>
                                <span>Positions</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recruitment.reports*') ? 'active' : '' }}"
                               href="{{ route('recruitment.reports') }}">
                                <i class="iconoir-reports menu-icon"></i>
                                <span>Reports</span>
                            </a>
                        </li>

                        {{-- SECTION 3: Users & Analytics --}}
                        <li class="menu-label"><span>Users & Analytics</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"
                               href="{{ route('customers.index') }}">
                                <i class="iconoir-user menu-icon"></i>
                                <span>Customers</span>
                            </a>
                        </li>

                    {{-- ════════════════════════════════════════
                         FIELD STAFF
                    ════════════════════════════════════════ --}}
                    @elseif($user->role === 'field_staff')

                        {{-- SECTION 2: Sales (only section relevant for field staff) --}}
                        <li class="menu-label"><span>Sales</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}"
                               href="{{ route('jobs.index') }}">
                                <i class="iconoir-task-list menu-icon"></i>
                                <span>My Work Orders
                                    @php $fsJobs = \App\Models\Job::where('assigned_to',$user->id)->where('status','assigned')->count(); @endphp
                                    @if($fsJobs > 0)
                                        <span class="badge bg-primary ms-1">{{ $fsJobs }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"
                               href="{{ route('customers.index') }}">
                                <i class="iconoir-user menu-icon"></i>
                                <span>Customers</span>
                            </a>
                        </li>


                    {{-- ════════════════════════════════════════
                         SUPERVISOR / WORKER
                    ════════════════════════════════════════ --}}
                    @elseif(in_array($user->role, ['supervisor', 'worker']))

                        {{-- SECTION 2: Sales --}}
                        <li class="menu-label"><span>Sales</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}"
                               href="{{ route('jobs.index') }}">
                                <i class="iconoir-task-list menu-icon"></i>
                                <span>My Work Orders</span>
                            </a>
                        </li>

                    @endif


                    {{-- ── Profile card (all roles) ── --}}
                    <div class="sidebar-divider"></div>
                    <div class="account-label pt-2">Account</div>
                    <a href="{{ route('profile.show') }}" class="profile-sidebar-card">
                        <div class="d-flex align-items-center">
                            <div class="profile-sidebar-avatar">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="profile-sidebar-info ms-3">
                                <div class="profile-sidebar-name">{{ $user->name }}</div>
                                <div class="profile-sidebar-role">
                                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                </div>
                            </div>
                        </div>
                    </a>

                </ul>
            </div>
        </div>
    </div>

</div>
<div class="startbar-overlay d-print-none"></div>


<!-- ═══════════════════════════════════════
     PAGE WRAPPER
═══════════════════════════════════════ -->
<div class="page-wrapper">
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

            @yield('content')

        </div>
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
<script>
    if (document.getElementById('year')) {
        document.getElementById('year').textContent = new Date().getFullYear();
    }

    // Real-Time Lead Notification System with Web Audio Sound Alert & Bell Dropdown
    document.addEventListener("DOMContentLoaded", function () {
        let lastCheckedTime = null;
        const checkUrl = "{{ route('notifications.check') }}";

        let notificationsList = JSON.parse(localStorage.getItem('crm_notifications_list') || '[]');
        let unreadCount = parseInt(localStorage.getItem('crm_notifications_unread_count') || '0', 10);

        const badgeElem = document.getElementById('notificationBadge');
        const listContainer = document.getElementById('notificationDropdownList');
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        const bellBtn = document.getElementById('notificationBellBtn');

        function saveNotificationsState() {
            localStorage.setItem('crm_notifications_list', JSON.stringify(notificationsList.slice(0, 30)));
            localStorage.setItem('crm_notifications_unread_count', unreadCount.toString());
        }

        function updateBadgeUI() {
            if (unreadCount > 0) {
                badgeElem.textContent = unreadCount > 99 ? '99+' : unreadCount;
                badgeElem.classList.remove('d-none');
            } else {
                badgeElem.classList.add('d-none');
            }
        }

        function renderDropdownListUI() {
            if (!notificationsList || notificationsList.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center py-4 text-muted" id="emptyNotificationMsg">
                        <i class="las la-bell-slash fs-28 d-block mb-1"></i>
                        <small>No new notifications</small>
                    </div>`;
                return;
            }

            let html = '';
            notificationsList.forEach(function (item) {
                let iconClass = item.type === 'new_lead' ? 'las la-user-plus text-success' : 'las la-comment-alt text-warning';
                let bgUnread = item.unread ? 'bg-light font-weight-bold' : '';
                let titleText = item.type === 'new_lead' ? 'New Lead' : 'Website Enquiry';
                let descText = item.name + ' (' + (item.phone || 'No phone') + ')';
                let timeStr = item.time_ago || 'Recently';

                html += `
                    <a href="${item.show_url}" class="dropdown-item py-2 px-3 border-bottom text-wrap ${bgUnread}">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-2 mt-1">
                                <i class="${iconClass} fs-20"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold fs-13 text-dark">${titleText}</span>
                                    <span class="text-muted fs-11">${timeStr}</span>
                                </div>
                                <div class="fs-12 text-secondary">${descText}</div>
                                <span class="badge bg-soft-primary text-primary mt-1" style="font-size:10px;">${item.branch}</span>
                            </div>
                        </div>
                    </a>`;
            });

            listContainer.innerHTML = html;
        }

        // Initialize UI
        updateBadgeUI();
        renderDropdownListUI();

        // Web Audio API chime sound synth (no external MP3 asset dependency needed)
        function playChimeSound() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();

                const now = ctx.currentTime;
                // Play a pleasant 2-tone notification chime (E5 -> A5)
                const osc1 = ctx.createOscillator();
                const osc2 = ctx.createOscillator();
                const gain = ctx.createGain();

                osc1.type = 'sine';
                osc2.type = 'sine';

                osc1.frequency.setValueAtTime(659.25, now); // E5
                osc2.frequency.setValueAtTime(880.00, now + 0.15); // A5

                gain.gain.setValueAtTime(0, now);
                gain.gain.linearRampToValueAtTime(0.3, now + 0.05);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.6);

                osc1.connect(gain);
                osc2.connect(gain);
                gain.connect(ctx.destination);

                osc1.start(now);
                osc1.stop(now + 0.15);
                osc2.start(now + 0.15);
                osc2.stop(now + 0.6);
            } catch (e) {
                console.log('Audio chime error:', e);
            }
        }

        function handleIncomingNotification(event, popupEnabled = true) {
            playChimeSound();

            event.unread = true;
            // Add to top of dropdown list
            notificationsList.unshift(event);
            unreadCount++;
            notificationsCleared = false;

            saveNotificationsState();
            updateBadgeUI();
            renderDropdownListUI();

            // If popup notifications are disabled in settings, skip showing toast popup
            if (!popupEnabled) {
                return;
            }

            // Toast Alert
            let title = event.type === 'new_lead' ? '🔔 New Lead Received!' : '🔔 Returning Customer Enquiry!';
            let badgeBg = event.type === 'new_lead' ? 'bg-success' : 'bg-warning text-dark';
            let message = `<strong>${event.name}</strong> (${event.phone}) - <span class="badge ${badgeBg}">${event.branch}</span>`;
            if (event.note) {
                message += `<br><small class="text-muted">${event.note}</small>`;
            }

            let toastHtml = `
                <div class="toast show shadow-lg border-0" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 320px; background: #ffffff; margin-top: 10px;">
                    <div class="toast-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <strong class="me-auto"><i class="las la-bell me-1 fs-16"></i> ${title}</strong>
                        <small class="text-white-50">Just now</small>
                        <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        <div class="mb-2">${message}</div>
                        <div class="d-flex gap-2">
                            <a href="${event.show_url}" class="btn btn-sm btn-primary">View Details</a>
                            <a href="${event.edit_url}" class="btn btn-sm btn-outline-secondary">Edit Lead</a>
                        </div>
                    </div>
                </div>
            `;

            let container = document.getElementById('lead-notification-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'lead-notification-toast-container';
                container.className = 'position-fixed bottom-0 end-0 p-3 d-flex flex-column align-items-end';
                container.style.zIndex = '99999';
                container.style.maxHeight = '80vh';
                container.style.overflowY = 'auto';
                document.body.appendChild(container);
            }

            // Append popups sequentially at the bottom right corner (one after another, no overlap)
            container.insertAdjacentHTML('beforeend', toastHtml);

            // Auto-refresh leads table if on leads list page
            if (typeof fetchLeadsData === 'function') {
                fetchLeadsData();
            }
        }

        let notificationsCleared = localStorage.getItem('crm_notifications_cleared') === 'true';

        function saveNotificationsState() {
            localStorage.setItem('crm_notifications_list', JSON.stringify(notificationsList.slice(0, 30)));
            localStorage.setItem('crm_notifications_unread_count', unreadCount.toString());
            localStorage.setItem('crm_notifications_cleared', notificationsCleared ? 'true' : 'false');
        }

        // Fetch initial list when opening bell dropdown (only if never cleared or has items)
        if (bellBtn) {
            bellBtn.addEventListener('click', function () {
                if (notificationsList.length === 0 && !notificationsCleared) {
                    $.ajax({
                        url: checkUrl,
                        data: { fetch_list: 1 },
                        success: function (res) {
                            if (res.success && res.recent_notifications && res.recent_notifications.length > 0) {
                                notificationsList = res.recent_notifications.map(n => { n.unread = false; return n; });
                                renderDropdownListUI();
                                saveNotificationsState();
                            }
                        }
                    });
                }
            });
        }

        // Mark All as Read / Clear All button
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                unreadCount = 0;
                notificationsList = [];
                notificationsCleared = true;
                saveNotificationsState();
                updateBadgeUI();
                renderDropdownListUI();

                // Clear any visible popups on screen as well
                const toastContainer = document.getElementById('lead-notification-toast-container');
                if (toastContainer) {
                    toastContainer.innerHTML = '';
                }
            });
        }

        function pollNewLeads() {
            let reqData = {};
            if (lastCheckedTime) {
                reqData.since = lastCheckedTime;
            }

            $.ajax({
                url: checkUrl,
                data: reqData,
                success: function (res) {
                    if (res.success) {
                        let isInitialCall = (!lastCheckedTime);
                        lastCheckedTime = res.server_time;

                        // Only display popups for real live incoming leads (NOT during initial page boot)
                        if (!isInitialCall && res.events && res.events.length > 0) {
                            res.events.forEach(function (evt) {
                                handleIncomingNotification(evt, res.popup_enabled !== false);
                            });
                        }
                    }
                },
                error: function () {}
            });
        }

        // Run initial call immediately to sync server timestamp without triggering popups
        pollNewLeads();

        // Poll every 8 seconds
        setInterval(pollNewLeads, 8000);
    });
</script>
@yield('extra-scripts')
</body>
</html>
