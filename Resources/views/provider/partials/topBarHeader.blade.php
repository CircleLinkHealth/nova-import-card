<nav>
    <div class="nav-wrapper primary-color">
        <a href="#" class="center" style="margin-left: 15px;">@yield('title')</a>

        <ul class="right hide-on-med-and-down">
            <li>
                <a href="#!" style="margin-right: 15px;">
                    {{ auth()->user()->display_name }}
                </a>
            </li>
        </ul>
    </div>
</nav>

@push('scripts')

@endpush