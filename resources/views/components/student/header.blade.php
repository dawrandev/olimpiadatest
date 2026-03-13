@props(['hideLogout' => false])

<div class="sticky-header">
    <header>
        <nav class="navbar navbar-b navbar-trans navbar-expand-xl fixed-top nav-padding" id="sidebar-menu">
            <a class="navbar-brand p-0" href="{{ route('student.home') }}">
                <img class="img-fluid" src="{{ asset('storage/logo.png') }}" alt="Logo" style="max-height: 60px;">
            </a>
            <button class="navbar-toggler navabr_btn-set custom_nav" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarDefault" aria-controls="navbarDefault" aria-expanded="false"
                aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>
            <div class="navbar-collapse justify-content-end collapse hidenav" id="navbarDefault">
                <ul class="navbar-nav navbar_nav_modify" id="scroll-spy">
                    @if(!$hideLogout)
                    <li class="nav-item d-flex justify-content-center">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline m-0 p-0">
                            @csrf
                            <button type="submit"
                                class="nav-link btn btn-link d-flex align-items-center text-danger p-0">
                                <i class="icofont icofont-logout me-2"></i> {{ __('Logout') }}
                            </button>
                        </form>
                    </li>
                    @endif
                </ul>
            </div>
        </nav>
    </header>
</div>