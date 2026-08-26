<!-- BEGIN: Vendor JS-->
<script src="{{ asset(mix('assets/vendor/libs/jquery/jquery.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/popper/popper.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/js/bootstrap.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/node-waves/node-waves.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/hammer/hammer.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/typeahead-js/typeahead.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/toastr/toastr.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/sweetalert2/sweetalert2.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/flatpickr/flatpickr.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/js/menu.js')) }}"></script>
@yield('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
<script src="{{ asset(mix('assets/js/main.js')) }}"></script>

<!-- END: Theme JS-->
<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->

<!-- Global Notification & Axios Error Interceptor -->
<script>
  (function () {
    // Configure Toastr defaults
    if (typeof toastr !== 'undefined') {
      toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 5000,
        extendedTimeOut: 2000
      };
    }

    // Global helper to show notification toasts
    window.showToast = function (message, type = 'info', title = '') {
      if (typeof toastr !== 'undefined') {
        toastr[type] ? toastr[type](message, title) : toastr.info(message, title);
      } else if (typeof Swal !== 'undefined') {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: type === 'danger' ? 'error' : type,
          title: message,
          showConfirmButton: false,
          timer: 4000
        });
      } else {
        alert((title ? title + ': ' : '') + message);
      }
    };

    // Show Session flash messages if present
    @if(session('status') || session('success'))
      window.showToast("{{ session('status') ?? session('success') }}", 'success', 'Success');
    @endif
    @if(session('error'))
      window.showToast("{{ session('error') }}", 'error', 'Error');
    @endif
    @if(session('warning'))
      window.showToast("{{ session('warning') }}", 'warning', 'Warning');
    @endif
    @if(session('info'))
      window.showToast("{{ session('info') }}", 'info', 'Info');
    @endif

    // Setup global Axios error interceptor if axios is loaded
    if (typeof axios !== 'undefined') {
      axios.interceptors.response.use(
        function (response) {
          return response;
        },
        function (error) {
          let message = 'An unexpected error occurred. Please try again.';
          let title = 'Error';

          if (error.response) {
            if (error.response.status === 403) {
              message = 'Access Denied: You do not have permission to perform this action.';
              title = '403 Forbidden';
            } else if (error.response.status === 404) {
              message = 'Requested resource was not found.';
              title = '404 Not Found';
            } else if (error.response.status === 422) {
              title = 'Validation Error';
              if (error.response.data && error.response.data.errors) {
                message = Object.values(error.response.data.errors).flat().join('<br>');
              } else if (error.response.data && error.response.data.message) {
                message = error.response.data.message;
              }
            } else if (error.response.status >= 500) {
              message = 'Server error occurred. Please contact the administrator.';
              title = '500 Server Error';
            } else if (error.response.data && error.response.data.message) {
              message = error.response.data.message;
            }
          } else if (error.request) {
            message = 'Unable to reach the server. Please check your network connection.';
            title = 'Network Error';
          }

          window.showToast(message, 'error', title);
          return Promise.reject(error);
        }
      );
    }
  })();
</script>

<!-- BEGIN: Page JS-->
@yield('page-script')
@stack('page-script')
<!-- END: Page JS-->
