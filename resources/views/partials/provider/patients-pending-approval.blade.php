<div class="row" style="margin-top:60px;">
    <div class="main-form-container col-lg-10 col-lg-offset-1">
        <div class="row">
            <div class="main-form-title col-lg-12">
                @if(auth()->user()->isAdmin())
                    Care Plans Pending CLH Approval: Click "CLH Approve" to Review
                @else
                    Pending Care Plans: Click "Approve Now" to Review
                @endif
            </div>
            <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                @if(auth()->user()->isProvider() && auth()->user()->providerInfo)
                    <form action="{{route('provider.update-approve-own')}}"
                          method="POST">
                        {{csrf_field()}}
                        <input class="btn btn-sm btn-default" aria-label="..."
                               type="submit"
                               value="@if(auth()->user()->providerInfo->approve_own_care_plans)View all Practice Care Plans @else View Assigned Care Plans Only @endif">
                    </form>
                @endif

                @include('partials.webix.patient-list')
            </div>
        </div>
    </div>
</div>