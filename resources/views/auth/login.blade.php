<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="dark" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="" name="description" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .error-text {
            font-size: 0.875rem;
            color: #dc3545;
            margin-top: 0.25rem;
            display: block;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .form-control.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .invalid-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #dc3545;
            font-size: 1.2rem;
        }

        .form-group {
            position: relative;
        }

        .btn-loading {
            pointer-events: none;
            opacity: 0.6;
        }
    </style>
</head>

<body>
    <div class="container-xxl">
        <div class="row vh-100 d-flex justify-content-center">
            <div class="col-12 align-self-center">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mx-auto">
                            <div class="card">
                                <div class="card-body p-0 bg-black auth-header-box rounded-top">
                                    <div class="text-center p-3">
                                        <a href="{{ route('login') }}" class="logo logo-admin">
                                            <img src="{{ asset('assets/images/logos/logo.png') }}" height="140" alt="logo" class="auth-logo">
                                        </a>
                                        <p class="text-muted fw-medium mb-0">Sign in to continue to your dashboard.</p>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <!-- Server Error Alert -->
                                    <div id="serverErrorAlert" class="alert alert-danger alert-dismissible fade show d-none mt-3" role="alert">
                                        <span id="serverErrorMessage"></span>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>

                                    <form id="loginForm" class="my-4">
                                        @csrf

                                        <!-- Email Field -->
                                        <div class="form-group mb-3">
                                            <label class="form-label" for="email">Email Address</label>
                                            <input
                                                type="email"
                                                class="form-control"
                                                id="email"
                                                name="email"
                                                placeholder="Enter email"
                                                required
                                            >
                                            <span class="error-text email_error"></span>
                                        </div>

                                        <!-- Password Field -->
                                        <div class="form-group mb-3">
                                            <label class="form-label" for="password">Password</label>
                                            <input
                                                type="password"
                                                class="form-control"
                                                id="password"
                                                name="password"
                                                placeholder="Enter password"
                                                required
                                            >
                                            <span class="error-text password_error"></span>
                                        </div>

                                        <!-- Remember Me -->
                                        <div class="form-group row mb-3">
                                            <div class="col-sm-6">
                                                <div class="form-check form-switch form-switch-success">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        id="remember"
                                                        name="remember"
                                                    >
                                                    <label class="form-check-label" for="remember">Remember me</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="form-group mb-0 row">
                                            <div class="col-12">
                                                <div class="d-grid mt-3">
                                                    <button class="btn btn-primary" type="submit" id="loginBtn">
                                                        <span id="btnText">Log In <i class="fas fa-sign-in-alt ms-1"></i></span>
                                                        <span id="btnSpinner" class="d-none">
                                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                            Logging in...
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Clear errors on input
            $('#email, #password').on('input', function() {
                let fieldName = $(this).attr('name');
                $('.' + fieldName + '_error').text('');
                $(this).removeClass('is-invalid');
            });

            // Form Submit
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                $('.error-text').text('');
                $('#email, #password').removeClass('is-invalid');
                $('#serverErrorAlert').addClass('d-none');

                // Get form data
                let formData = {
                    email: $('#email').val(),
                    password: $('#password').val(),
                    remember: $('#remember').is(':checked') ? 1 : 0,
                    _token: $('input[name="_token"]').val()
                };

                // Show loading state
                $('#loginBtn').addClass('btn-loading').prop('disabled', true);
                $('#btnText').addClass('d-none');
                $('#btnSpinner').removeClass('d-none');

                $.ajax({
                    url: '{{ route("login") }}',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        // Show success toast
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Login Successful!',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = '{{ route("dashboard") }}';
                        });
                    },
                    error: function(xhr) {
                        // Reset button state
                        $('#loginBtn').removeClass('btn-loading').prop('disabled', false);
                        $('#btnText').removeClass('d-none');
                        $('#btnSpinner').addClass('d-none');

                        if (xhr.status === 422) {
                            // Validation errors
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#' + key).addClass('is-invalid');
                                $('.' + key + '_error').text(value[0]);
                            });
                        } else if (xhr.status === 401) {
                            // Authentication failed
                            $('#serverErrorAlert').removeClass('d-none');
                            $('#serverErrorMessage').text('The provided credentials do not match our records.');
                        } else {
                            // General error
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An error occurred. Please try again.',
                                timer: 3000
                            });
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
