<nav class="navbar navbar-expand navbar-light navbar-bg">
    <a class="sidebar-toggle js-sidebar-toggle">
        <i class="hamburger align-self-center"></i>
    </a>

    <div class="navbar-collapse collapse">
        <ul class="navbar-nav navbar-align">
            {{-- full screen --}}
            <li class="nav-item">
                <a class="nav-icon js-fullscreen d-none d-lg-block" href="#">
                    <div class="position-relative">
                        <i class="align-middle" data-feather="maximize"></i>
                    </div>
                </a>
            </li>

            {{-- logout --}}
            <li class="nav-item">
                <a class="nav-icon d-none d-lg-block" href="{{route('logout')}}">
                    <div class="position-relative">
                        <i class="align-middle" data-feather="log-out"></i>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</nav>