<div class="flow-text" style="max-height: 590px; overflow-y: scroll;">
    <div>
        <div class="header" style="display: inline-flex; margin-bottom: 60px">
            <div class="address">
                @if($extraAddressValuesExists)
                <br>
                    {{$extraAddressValues[0]['address_line_1']}}
                    <br>
                    {{$extraAddressValues[0]['city']}}
                    {{$extraAddressValues[0]['state']}}
                    {{$extraAddressValues[0]['postal_code']}}
                @endif
            </div>

            <div class="logo" style="text-align: right; opacity: 90%; margin-left: 377px;">
                    @include('enrollment-letters.practiceLogo')
                </div>
        </div>
    </div>

    <div class="letter-sent">
        {{$dateLetterSent}}
    </div>

    <div class="letter-head">
        Dear {{$userEnrollee->first_name}} {{$userEnrollee->last_name}},
    </div>
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

    @media (max-width: 490px) {
        .header{
            padding-right: 18px;
            font-size: 15px;
        }

    }
</style>