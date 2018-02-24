@extends('partials.noUser')

@section('title', 'Care Enrollment')
@section('activity', 'Patient Care Enrollment')

@section('content')
    @push('styles')
        <style type="text/css">
            .container {
                width: 620px !important;
            }

            .color-red {
                color: red;
            }

            .color-orange {
                color: #fa0;
            }

            .color-green {
                color: #5cb85c;
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

            .points ul {
                list-style: disc !important;
            }
        </style>
    @endpush

    <div class="main-form-block main-form-horizontal col-md-12">
        <h3 class="main-form-primary-horizontal text-center">
            Enrollment to Personalized Care (CCM)
        </h3>
        <h4 class="color-orange text-center">
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
                    <ul>
                        <li>Program is a way for me / MD to follow-up between office visits</li>
                    </ul>
                </li>
                <li>
                    <h4>
                        Personalized care manager for Qs:
                    </h4>
                    <ul>
                        <li>A personalized care manager (registered nurse) will answer questions and keep us connected</li>
                    </ul>
                </li>
                <li>
                    <h4>
                        You're covered!
                    </h4>
                    <ul>
                        <li>Medicare covers the program and if you have supplemental insurance or Medicaid, it should cover the co-pay (~$8/mo.)</li>
                    </ul>
                </li>
                <li>
                    <h4>
                        You can quit anytime, just call
                    </h4>
                </li>
                <li>
                    <h4>
                        You can only be on the program with 1 Dr. at a time
                    </h4>
                </li>
            </ul>
        </div>

        <div class="footer row top-30">
            <div class="col-sm-6">
                <form method="post" action="{{ url('care/enroll/' . $enrollUserId) }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="status" value="rejected" />
                    <button class="btn btn-warning">DID NOT CONSENT</button>
                </form>
            </div>
            <div class="col-sm-6 text-right">
                <a class="btn btn-success" href="{{ url('manage-patients/' . $enrollUserId . '/view-careplan/assessment') }}">Patient Consented</a>
            </div>
        </div>
    </div>
@endsection