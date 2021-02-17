<div class="flow-text" style="max-height: 590px;">
    <div class="header" style="display: inline-flex; margin-bottom: 60px">
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

            <div class="logo">
                @include('selfEnrollment::enrollment-letters.practiceLogo')
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
        margin-left: 500px;
        margin-top: 15px;
    }

    @media (max-width: 490px) {
        .logo{
            margin-left: 50px;
        }
    }

    @media (max-width: 490px) {
        .header{
            padding-right: 18px;
            font-size: 15px;
        }

    }
</style>