<script src="{{ asset('assets/js/jquery-3.5.1.min.js') }}"></script>
<!-- Bootstrap js-->
<script src="{{ asset('assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
<!-- feather icon js-->
<script src="{{ asset('assets/js/icons/feather-icon/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/icons/feather-icon/feather-icon.js') }}"></script>
<!-- scrollbar js-->
<script src="{{ asset('assets/js/scrollbar/simplebar.js') }}"></script>
<script src="{{ asset('assets/js/scrollbar/custom.js') }}"></script>
<!-- Sidebar jquery-->
<script src="{{ asset('assets/js/config.js') }}"></script>
<!-- Plugins JS start-->
<script src="{{ asset('assets/js/sidebar-menu.js') }}"></script>
<script src="{{ asset('assets/js/tooltip-init.js') }}"></script>
<!-- Plugins JS Ends-->
<!-- Theme js-->
<script src="{{ asset('assets/js/script.js') }}"></script>
<script src="{{ asset('assets/js/theme-customizer/customizer.js') }}"></script>
<!-- SweetAlert2 -->
<script>
  window.alertTranslations = {
    areYouSure: @json(__('Are you sure?')),
    cannotUndo: @json(__('This action cannot be undone!')),
    yesDelete: @json(__('Yes, delete it!')),
    yesUpdate: @json(__('Yes, update!')),
    yesConfirm: @json(__('Yes, confirm!')),
    cancel: @json(__('Cancel'))
  };
</script>

@if(session('success') || session('error') || session('warning') || session('info'))
<script>
  @if(session('success'))
  window.laravelFlash = {
    type: 'success',
    message: "{{ session('success') }}"
  };
  @elseif(session('error'))
  window.laravelFlash = {
    type: 'error',
    message: "{{ session('error') }}"
  };
  @elseif(session('warning'))
  window.laravelFlash = {
    type: 'warning',
    message: "{{ session('warning') }}"
  };
  @elseif(session('info'))
  window.laravelFlash = {
    type: 'info',
    message: "{{ session('info') }}"
  };
  @endif
</script>
@endif