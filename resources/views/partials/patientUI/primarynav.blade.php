<?php
$user = auth()->user();
?>
@push('styles')
    <style>
        .full-width {
            width: 100%;
        }

        .margin-0 {
            margin-right: 0;
            margin-left: 0;
        }

        .top-nav-item-icon {
            height: 19px;
            width: 20px;
            margin-right: 3px;
        }

        .top-nav-item {
            background: none !important;
            padding: 15px;
            line-height: 20px;
            cursor: pointer;
        }

        .text-white {
            color: #fff;
        }

        .search-bar {
            width: 90%;
        }
    </style>
@endpush

<nav class="navbar primary-navbar">
    <div class="container-fluid full-width margin-0">
        <div class="col-lg-6 col-sm-6 col-xs-6">
            <a class="navbar-brand" href="{{ url('/') }}" style="border: none"><img
                        src="{{mix('/img/logos/LogoHorizontal_White.svg')}}"
                        alt="Care Plan Manager"
                        style="position:relative;top:-7px"
                        width="105px"/></a>
        </div>
        <div class="col-lg-6 col-sm-6 col-xs-6">
            @include('partials.user-account-dropdown')
        </div>
    </div>
</nav>
