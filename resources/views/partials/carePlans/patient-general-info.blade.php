<div class="row gutter">
    <div class="col-xs-7">
        <h1 class="patient-summary__title patient-summary__title_9 patient-summary--careplan">
            Care
            Plan</h1>
    </div>

    @include('partials.carePlans.approval-box')
</div>

<br>

<div class="row gutter">
    <div class="col-xs-5 print-row text-bold">{{$patient->getFullName()}}
        (DOB: {{$patient->patientInfo->dob()}})
    </div>
    <div class="col-xs-3 print-row">{{$patient->getPhone()}}</div>
    <div class="col-xs-4 print-row text-right">{{$today}}</div>
</div>
<div class="row gutter">
    @if($billingDoctor)
        <div class="col-xs-5 print-row text-bold">
            {{$billingDoctor->getFullName()}} {!! ($billingDoctor->getSpecialty() == '')? '' :  "<br> {$billingDoctor->getSpecialty()}"!!}
        </div>
        <div class="col-xs-3 print-row">
            {{$billingDoctor->getPhone()}}
        </div>
    @else
        <div class="col-xs-5 print-row text-bold">
            <em>No Billing Dr. Selected</em>
        </div>
        <div class="col-xs-3 print-row">
        </div>
    @endif
    <div class="col-xs-4 print-row text-bold text-right">{{$patient->getPreferredLocationName()}}</div>
</div>


@if($regularDoctor)
    <div class="row gutter">
        <div class="col-xs-5 print-row text-bold">
            {{$regularDoctor->getFullName()}} {!! ($regularDoctor->getSpecialty() == '')? '' :  "<br> {$regularDoctor->getSpecialty()}"!!}
        </div>
        <div class="col-xs-3 print-row">
            {{$regularDoctor->getPhone()}}
        </div>
    </div>
@endif