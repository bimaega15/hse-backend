@extends('auth.layout')

@section('content')
    <div class="auth-bg d-flex min-vh-100 justify-content-center align-items-center">
        <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card overflow-hidden text-center rounded-4 p-xxl-4 p-3 mb-0">
                    <a href="{{ route('admin.login') }}" class="auth-brand mb-4">
                        <img src="{{ asset('assets/logo/logo-app.jpg') }}" alt="dark logo" height="26" class="logo-dark">
                        <img src="{{ asset('assets/logo/logo-app.jpg') }}" alt="logo light" height="26"
                            class="logo-light">
                    </a>

                    <h4 class="fw-semibold mb-2 fs-18">Log in to your account</h4>

                    <p class="text-muted mb-4">Enter your email address and password to access admin panel.</p>

                    <!-- Alert untuk menampilkan pesan -->
                    <div id="alert-container"></div>

                    <form id="loginForm" class="text-start mb-3">
                        @csrf
                        <div class="mb-2">
                            <input type="email" id="email" name="email" class="form-control"
                                placeholder="Enter your email" required>
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>

                        <div class="mb-3 position-relative">
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="Enter your password" required minlength="6">
                            <div class="invalid-feedback" id="password-error"></div>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>

                            <a href="#" class="text-muted border-bottom border-dashed">Forget Password</a>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-primary fw-semibold" type="submit" id="loginBtn">
                                <span class="btn-text">Login</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                    aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>

                    <p class="text-muted fs-14 mb-0">Don't have an account?
                        <a href="#" class="fw-semibold text-danger ms-1">Contact Admin</a>
                    </p>
                </div>

                <p class="mt-4 text-center mb-0">
                    <script>
                        document.write(new Date().getFullYear())
                    </script> © BAIK Management System - By <span
                        class="fw-bold text-decoration-underline text-uppercase text-reset fs-12">BAIK TECHNOLOGY</span>
                </p>
            </div>
        </div>
    </div>

    @push('jsSection')
        <!-- jQuery dan AJAX Script -->
        <script>
            $(document).ready(function() {
                // Handle form submission
                $('#loginForm').on('submit', function(e) {
                    e.preventDefault();

                    // Reset previous errors
                    clearErrors();

                    // Show loading state
                    showLoading(true);

                    // Get form data
                    const formData = {
                        _token: $('input[name="_token"]').val(),
                        email: $('#email').val(),
                        password: $('#password').val(),
                        remember: $('#remember').is(':checked') ? 1 : 0
                    };

                    // AJAX request
                    $.ajax({
                        url: '{{ route('admin.login.post') }}',
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            showLoading(false);

                            if (response.success) {
                                showAlert('success', response.message);

                                // Redirect after success
                                setTimeout(function() {
                                    window.location.href = response.redirect_url;
                                }, 1500);
                            } else {
                                showAlert('danger', response.message);
                            }
                        },
                        error: function(xhr) {
                            showLoading(false);

                            if (xhr.status === 422) {
                                // Validation errors
                                const errors = xhr.responseJSON.errors;
                                showValidationErrors(errors);
                                showAlert('danger', xhr.responseJSON.message);
                            } else if (xhr.status === 401) {
                                // Authentication error
                                showAlert('danger', xhr.responseJSON.message);
                            } else {
                                // Other errors
                                showAlert('danger', 'Terjadi kesalahan, silahkan coba lagi');
                            }
                        }
                    });
                });

                // Clear errors when typing
                $('#email, #password').on('input', function() {
                    const fieldName = $(this).attr('name');
                    clearFieldError(fieldName);
                });
            });

            function showLoading(show) {
                const btn = $('#loginBtn');
                const btnText = btn.find('.btn-text');
                const spinner = btn.find('.spinner-border');

                if (show) {
                    btn.prop('disabled', true);
                    btnText.text('Loading...');
                    spinner.removeClass('d-none');
                } else {
                    btn.prop('disabled', false);
                    btnText.text('Login');
                    spinner.addClass('d-none');
                }
            }

            function showAlert(type, message) {
                const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

                $('#alert-container').html(alertHtml);

                // Auto dismiss after 5 seconds
                if (type === 'success') {
                    setTimeout(function() {
                        $('.alert').alert('close');
                    }, 5000);
                }
            }

            function showValidationErrors(errors) {
                $.each(errors, function(field, messages) {
                    const fieldInput = $('#' + field);
                    const errorContainer = $('#' + field + '-error');

                    fieldInput.addClass('is-invalid');
                    errorContainer.text(messages[0]);
                });
            }

            function clearErrors() {
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                $('#alert-container').empty();
            }

            function clearFieldError(fieldName) {
                $('#' + fieldName).removeClass('is-invalid');
                $('#' + fieldName + '-error').text('');
            }
        </script>
    @endpush
@endsection
