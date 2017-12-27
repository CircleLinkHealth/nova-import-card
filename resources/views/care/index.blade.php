@extends('partials.noUser')

@section('title', 'Care Enrollment')
@section('activity', 'Patient Care Enrollment')

@section('content')
    @push('styles')
        <style type="text/css">
            .color-red {
                color: red;
            }

            .top-20 {
                margin-top: 20px
            }

            .top-30 {
                margin-top: 30px
            }

            .points li {
                line-height: 30px;
                list-style: square;
            }

            .main-form-block {
                border: 1px solid lightgray;
                box-shadow: 4px 4px #eee;
                font-size: 16px;
            }

            .main-form-block p {
                text-indent: 20px;
                margin-bottom: 30px;
            }
        </style>
    @endpush

    <div class="main-form-block main-form-horizontal col-md-12">
        <h3 class="main-form-primary-horizontal text-center">
            Enrollment and Advanced Care Planning for Personalized Care (CCM)
        </h3>
        <h4 class="color-red text-center">
            Reimbursement code G0506
        </h4>
        <h4 class="color-green top-20 text-center">
            TALKING POINTS FOR PATIENT
        </h4>

        <div class="points top-30">
            <ul>
                <li>
                    <h4>
                        Check-ins and care between visits:
                    </h4>
                    <p>
                        Program is a way for me / MD to follow-up between office visits
                    </p>
                </li>
                <li>
                    <h4>
                        Personalized care manager for Qs:
                    </h4>
                    <p>
                        A personalized care manager (registered nurse) will answer questions and keep us connected
                    </p>
                </li>
                <li>
                    <h4>
                        You're covered!
                    </h4>
                    <p>
                        Medicare covers the program and if you have supplemental insurance or Medicaid, it should cover the co-pay (~$8/mo.)
                    </p>
                </li>
                <li>
                    <h4>
                        You can quit anytime, just call
                    </h4>
                    <p>
                        
                    </p>
                </li>
            </ul>
        </div>

        <div class="footer top-30 text-right">
            <a class="btn btn-success" href="{{ url('manage-patients/' . $enrollUserId . '/view-careplan/assessment') }}">Patient Consented</a>
            <a class="btn btn-warning" href="{{ url('home') }}">DID NOT CONSENT</a>
        </div>
    </div>
@endsection