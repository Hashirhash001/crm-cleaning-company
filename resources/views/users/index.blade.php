@extends('layouts.app')

@section('title', 'Users Management')

@section('extra-css')
    <link href="{{ asset('assets/libs/simple-datatables/style.css') }}" rel="stylesheet" type="text/css" />

    <style>
        /* Clickable user name styling */
        .user-name-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
        }

        .user-name-link:hover {
            color: #0a58ca;
            text-decoration: underline;
        }

        .user-name-link h6 {
            color: inherit;
            font-weight: 600;
            margin: 0;
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Users Management</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Users</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body" style=" padding: 1.5rem; border-radius: 0.4rem;">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Filter by Role</label>
                        <select class="form-select" id="roleFilter">
                            <option value="">All Roles</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="lead_manager">Lead Manager</option>
                            <option value="field_staff">Field Staff</option>
                            <option value="reporting_user">Reporting User</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Filter by Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" id="searchUser" placeholder="Search by name or email...">
                    </div>

                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary flex-grow-1" id="resetFilters">
                                <i class="fas fa-redo me-2"></i> Reset
                            </button>
                            <button type="button" class="btn btn-primary flex-grow-1" id="addUserBtn">
                                <i class="fas fa-plus me-2"></i> Add User
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Users List (<span id="userCount">{{ $users->total() }}</span> total)</h4>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="usersTable">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile No</th>
                                <th>Role</th>
                                <th>Branch</th>
                                <th>Created On</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            @include('users.partials.table-rows')
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Pagination -->
            <div class="card-footer">
                <div id="paginationContainer">
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="userForm">
                @csrf
                <input type="hidden" id="user_id" name="user_id">
                <input type="hidden" id="form_method" name="_method" value="POST">

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter full name" required>
                            <span class="text-danger error-text name_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                            <span class="text-danger error-text email_error"></span>
                        </div>
                    </div>

                    <div class="row mb-3" id="passwordFields">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
                            <span class="text-danger error-text password_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm password">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone number">
                            <span class="text-danger error-text phone_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="super_admin">Super Admin</option>
                                <option value="lead_manager">Lead Manager</option>
                                <option value="field_staff">Field Staff</option>
                                <option value="reporting_user">Reporting User</option>
                            </select>
                            <span class="text-danger error-text role_error"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                            <select class="form-select" id="branch_id" name="branch_id" required>
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-text branch_id_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="is_active" class="form-label">Status</label>
                            <div class="form-check form-switch form-switch-success mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // CSRF Token Setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // AJAX Load Function
            function loadUsers(url = null) {
                let requestUrl = url || '{{ route("users.index") }}';
                let params = {
                    role: $('#roleFilter').val(),
                    status: $('#statusFilter').val(),
                    search: $('#searchUser').val()
                };

                $.ajax({
                    url: requestUrl,
                    type: 'GET',
                    data: params,
                    success: function(response) {
                        $('#usersTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        $('#userCount').text(response.total);
                    },
                    error: function(xhr) {
                        console.error('Error loading users:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to load users'
                        });
                    }
                });
            }

            // Pagination Click Handler
            $(document).on('click', '#paginationContainer .pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                loadUsers(url);
            });

            // Filters with Debounce
            let filterTimeout;
            $('#roleFilter, #statusFilter, #searchUser').on('change keyup', function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(function() {
                    loadUsers();
                }, 300);
            });

            // Reset Filters
            $('#resetFilters').click(function() {
                $('#roleFilter, #statusFilter, #searchUser').val('');
                window.location.href = '{{ route("users.index") }}';
            });

            // Add User Button Click
            $('#addUserBtn').click(function() {
                $('#userForm')[0].reset();
                $('#user_id').val('');
                $('#form_method').val('POST');
                $('#userModalLabel').text('Add User');
                $('#passwordFields').show();
                $('#password').attr('required', true);
                $('#password_confirmation').attr('required', true);
                $('.error-text').text('');
                $('#userModal').modal('show');
            });

            // Edit Button Click
            $(document).on('click', '.editBtn', function() {
                let userId = $(this).data('id');

                $.ajax({
                    url: '/users/' + userId + '/edit',
                    type: 'GET',
                    success: function(response) {
                        $('#userModalLabel').text('Edit User');
                        $('#user_id').val(response.user.id);
                        $('#form_method').val('PUT');
                        $('#name').val(response.user.name);
                        $('#email').val(response.user.email);
                        $('#phone').val(response.user.phone);
                        $('#role').val(response.user.role);
                        $('#branch_id').val(response.user.branch_id);
                        $('#is_active').prop('checked', response.user.is_active);

                        $('#passwordFields').hide();
                        $('#password').attr('required', false);
                        $('#password_confirmation').attr('required', false);

                        $('.error-text').text('');
                        $('#userModal').modal('show');
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to fetch user data',
                            timer: 2000
                        });
                    }
                });
            });

            // Form Submit
            $('#userForm').submit(function(e) {
                e.preventDefault();

                let userId = $('#user_id').val();
                let url = userId ? '/users/' + userId : '/users';
                let formData = new FormData(this);

                $('.error-text').text('');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#userModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            loadUsers(); // Use AJAX reload instead of location.reload()
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('.' + key + '_error').text(value[0]);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Something went wrong!',
                                timer: 2000
                            });
                        }
                    }
                });
            });

            // Delete Button Click
            $(document).on('click', '.deleteBtn', function() {
                let userId = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/users/' + userId,
                            type: 'DELETE',
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    loadUsers(); // Use AJAX reload instead of location.reload()
                                });
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Failed to delete user',
                                    timer: 2000
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>

@endsection
