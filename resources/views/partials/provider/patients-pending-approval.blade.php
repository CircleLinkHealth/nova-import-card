<div class="row" style="margin-top:60px;">
    <div class="main-form-container col-lg-10 col-lg-offset-1">
        <div class="row">
            <div class="main-form-title col-lg-12">
                Pending Care Plans: Click "Approve Now" to Review
            </div>
            <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                @if(auth()->user()->hasRole('provider'))
                    <a class="btn btn-sm btn-info" style="margin-bottom: 20px" aria-label="..."
                       role="button"
                       href="{{route('provider.update-approve-own')}}">@if(optional(auth()->user()->providerInfo)->approve_own_care_plans)
                            Approve all practice patients @else Approve my patients only @endif</a>

                @endif

                @include('partials.webix.patient-list')
            </div>
        </div>
    </div>
</div>