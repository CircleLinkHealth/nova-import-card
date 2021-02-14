@php
$phone = '501-833-4001';
$fax = '1-888-213-5007';
@endphp


<div class="flow-text" style="max-height: 590px;">
    <div class="header col">
        <div class="logo">
            @include('selfEnrollment::enrollment-letters.practiceLogo')
        </div>

        <div class="address row" style="max-width: 377px;">
            @if($extraAddressValuesExists)
                <br>
                {{$extraAddressValues[0]['address_line_1']}}
            <br>
            Phone: {{$phone}}
            Fax: {{$fax}}
            @endif
        </div>
    </div>
</div>

<div class="letter-sent">
    {{\Carbon\Carbon::parse($dateLetterSent)->format('m/d/y')}}
</div>

<div class="letter-head">
    Dear {{$userEnrollee->first_name}} {{$userEnrollee->last_name}},
</div>

<style>
    .address{
        font-size: 18px;
    }

    .letter-head{
        padding-bottom: unset;
    }

    .header{
        font-size: 23px;
    }

    .logo{
        float: left;
    }

    @media (max-width: 490px) {
        .header{
            padding-right: 18px;
            font-size: 15px;
        }

        .logo{
            opacity: 90%;
            margin-top: 15px;
            margin-right: unset;
            float: none;
        }

    }
</style>
