<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.admin.head')

    @stack('css')

    @vite(['resources/js/app.js'])

    @vite(['resources/css/admin/header.css'])
</head>

<body>
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        @include('components.admin.header')
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            @include('components.admin.sidebar')
            <div class="page-body">
                <!-- Container-fluid starts-->
                @yield('content')
                <!-- Container-fluid Ends-->
            </div>
            @include('components.admin.footer')
        </div>
    </div>
    @include('partials.admin.script')
    @vite(['resources/js/alert.js'])
    @stack('scripts')

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
</body>

</html>