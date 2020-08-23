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
                    <patient-list :show-provider-patients-button="@json(auth()->user()->isProvider() && auth()->user()->scope !== App\User::SCOPE_LOCATION)"
                                  :is-admin="@json(auth()->user()->isAdmin())"
                                  :can-approve-careplans="@json(auth()->user()->canApproveCarePlans())"
                                  url-filter="patientsPendingAuthUserApproval"
                                  :hide-download-buttons="@json(true)"
                                  ref="patientList">
                    </patient-list>
            </div>
        </div>
    </div>
</div>