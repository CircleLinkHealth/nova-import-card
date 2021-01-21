<div class="flow-text" style="max-height: 590px;">
    <div class="header" style="margin-bottom: 20px">

        <div class="logo">
            @include('selfEnrollment::enrollment-letters.practiceLogo')
        </div>

        <div class="address" style="max-width: 377px;">
            @if($extraAddressValuesExists)
                <br>
                {{$extraAddressValues[0]['address_line_1']}}
                <br>
                {{$extraAddressValues[0]['city']}}
                {{$extraAddressValues[0]['state']}}
                <br>
                {{$extraAddressValues[0]['postal_code']}}
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

    .letter-sent{
        margin-top: -27px;
    }

    .header{
        font-size: 23px;
    }

    .logo{
        opacity: 90%;
        margin-top: 15px;
        text-align: left;
    }

    @media (max-width: 490px) {
        .header{
            padding-right: 18px;
            font-size: 15px;
        }

    }
</style>