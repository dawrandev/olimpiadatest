<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.student.head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="landing-page">
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->

    <div class="page-wrapper landing-page">
        @if(request()->routeIs('student.test*'))
        @include('components.student.header', ['hideLogout' => true])
        @else
        @include('components.student.header')
        @endif

        @yield('content')
    </div>

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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.laravelFlash) {
                Swal.fire({
                    icon: window.laravelFlash.type,
                    title: window.laravelFlash.message,
                });
            }
        });
    </script>

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

    @include('partials.student.script')
    @stack('scripts')
</body>

</html>