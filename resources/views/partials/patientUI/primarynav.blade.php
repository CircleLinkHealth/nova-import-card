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

        .practice-logo {
            padding-top: 2%;
            font-weight: bolder;
            font-family: "Roboto Slab",Georgia,Times,"Times New Roman",serif;
        }
    </style>
@endpush

<nav class="navbar primary-navbar">
    <div class="container-fluid full-width margin-0">
        <div class="col-lg-6 col-sm-6 col-xs-6">
            <a class="navbar-brand practice-logo" href="{{ url('/') }}" style="border: none"><span>{{$patient->getPrimaryPracticeName()}}</span></a>
        </div>
        <div class="col-lg-6 col-sm-6 col-xs-6">
            <div class="collapse navbar-collapse" id="navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    @include('partials.user-account-dropdown')
                </ul>
            </div>
        </div>
    </div>
</nav>
